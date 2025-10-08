<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'week');

        // 🕒 Xác định mặc định theo thời điểm hiện tại
        $now = now();
        $year = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);
        $quarter = (int) $request->get('quarter', ceil($now->month / 3));

        // Danh sách năm 3 năm gần nhất
        $years = range($now->year - 3, $now->year);

        // --- Tính danh sách tuần ---
        $weeksList = $this->weeksOfMonth($year, $month);

        // Tìm tuần hiện tại (nếu không chọn)
        $currentWeekIndex = 0;
        foreach ($weeksList as $i => $w) {
            if ($now->between($w['start'], $w['end'])) {
                $currentWeekIndex = $i;
                break;
            }
        }

        // Nếu người dùng không chọn tuần → mặc định tuần hiện tại
        $week = (int) $request->get('week', $currentWeekIndex);

        // Mặc định nhãn và thời gian
        $label = '';
        $start = $now->startOfWeek();
        $end = $now->endOfWeek();

        // --- Tùy theo kiểu thống kê ---
        switch ($period) {
            case 'week':
                $start = $weeksList[$week]['start'];
                $end = $weeksList[$week]['end'];
                $label = "Doanh thu tuần " . ($week + 1) . " ({$start->format('d/m')} - {$end->format('d/m/Y')})";
                break;

            case 'month':
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = Carbon::create($year, $month, 1)->endOfMonth();
                $label = "Doanh thu tháng {$month}/{$year} (Top 10 shop)";
                break;

            case 'quarter':
                $start = Carbon::create($year, 3 * $quarter - 2, 1)->startOfMonth();
                $end = Carbon::create($year, 3 * $quarter, 1)->endOfMonth();
                $label = "Doanh thu quý {$quarter} năm {$year} (Top 10 shop)";
                break;

            case 'year':
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = Carbon::create($year, 12, 31)->endOfYear();
                $label = "Doanh thu năm {$year} (Top 10 shop)";
                break;
        }

        // 🧾 Truy vấn doanh thu (chỉ lấy shop đang active)
        $data = Shop::where('shops.status', 'active') // ✅ chỉ lấy shop đang hoạt động
    ->leftJoin('users', 'shops.user_id', '=', 'users.id')
    ->leftJoin('order_items', 'order_items.seller_id', '=', 'users.id')
    ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
    ->select(
        'shops.user_id as id',
        'shops.name as shop_name',
        DB::raw('COALESCE(SUM(CASE 
            WHEN orders.status = "completed"
            AND orders.updated_at BETWEEN "' . $start . '" AND "' . $end . '"
            THEN orders.total_price ELSE 0 END), 0) as total')
    )
    ->groupBy('shops.user_id', 'shops.name')
    ->orderByDesc('total')
    ->limit(10)
    ->get();


        // Nếu ít hơn 10 shop, thêm shop active khác vào danh sách (doanh thu = 0)
        if ($data->count() < 10) {
            $missing = 10 - $data->count();
            $extra = Shop::where('status', 'active') // ✅ chỉ thêm shop đang active
                ->whereNotIn('user_id', $data->pluck('id'))
                ->limit($missing)
                ->get(['user_id as id', 'name as shop_name']);

            foreach ($extra as $s) {
                $s->total = 0;
                $data->push($s);
            }
        }

        $labels = $data->pluck('shop_name');
        $values = $data->pluck('total');

        return view('admin.analytics', compact(
            'period', 'label', 'data', 'labels', 'values',
            'years', 'year', 'month', 'week', 'weeksList', 'quarter'
        ));
    }

    // 🧮 Hàm chia tháng thành các tuần
    private function weeksOfMonth(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $weeks = [];

        // Tuần 1: từ ngày 1 đến CN đầu tiên
        if ($startOfMonth->dayOfWeek === Carbon::SUNDAY) {
            $firstWeekEnd = $startOfMonth->copy();
        } else {
            $firstWeekEnd = $startOfMonth->copy()->next(Carbon::SUNDAY);
            if ($firstWeekEnd > $endOfMonth) $firstWeekEnd = $endOfMonth;
        }

        $weeks[] = [
            'start' => $startOfMonth->copy(),
            'end' => $firstWeekEnd->copy(),
        ];

        // Các tuần tiếp theo: Thứ 2 -> CN
        $cursor = $firstWeekEnd->copy()->addDay();
        while ($cursor <= $endOfMonth) {
            $weekStart = $cursor->copy();
            $weekEnd = $weekStart->copy()->next(Carbon::SUNDAY);
            if ($weekEnd > $endOfMonth) $weekEnd = $endOfMonth;

            $weeks[] = [
                'start' => $weekStart->copy(),
                'end' => $weekEnd->copy(),
            ];
            $cursor = $weekEnd->copy()->addDay();
        }

        return $weeks;
    }

    // 🧭 API cập nhật danh sách tuần khi đổi tháng
    public function getWeeks(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $weeks = $this->weeksOfMonth($year, $month);
        $result = [];

        foreach ($weeks as $i => $w) {
            $result[] = [
                'index' => $i,
                'label' => 'Tuần ' . ($i + 1) . ' (' .
                    $w['start']->format('d/m') . ' - ' .
                    $w['end']->format('d/m') . ')'
            ];
        }

        return response()->json($result);
    }
}
