<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class AdminProductAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'week');
        $now = now();

        $year = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);
        $quarter = (int) $request->get('quarter', ceil($now->month / 3));
        $week = (int) $request->get('week', 0);
        $years = range($now->year - 3, $now->year);

        // ==== Chia tháng thành tuần ====
        $weeksList = $this->weeksOfMonth($year, $month);
        $currentWeekIndex = 0;
        foreach ($weeksList as $i => $w) {
            if ($now->between($w['start'], $w['end'])) {
                $currentWeekIndex = $i;
                break;
            }
        }
        $week = $request->get('week', $currentWeekIndex);

        // ==== Xác định khoảng thời gian ====
        switch ($period) {
            case 'week':
                $start = $weeksList[$week]['start'];
                $end = $weeksList[$week]['end'];
                $label = "Top 10 sản phẩm bán chạy tuần " . ($week + 1) .
                         " ({$start->format('d/m')} - {$end->format('d/m/Y')})";
                break;

            case 'month':
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = Carbon::create($year, $month, 1)->endOfMonth();
                $label = "Top 10 sản phẩm bán chạy tháng {$month}/{$year}";
                break;

            case 'quarter':
                $start = Carbon::create($year, 3 * $quarter - 2, 1)->startOfMonth();
                $end = Carbon::create($year, 3 * $quarter, 1)->endOfMonth();
                $label = "Top 10 sản phẩm bán chạy quý {$quarter} năm {$year}";
                break;

            case 'year':
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = Carbon::create($year, 12, 31)->endOfYear();
                $label = "Top 10 sản phẩm bán chạy năm {$year}";
                break;
        }

        // ==== Truy vấn sản phẩm ====
        $data = Product::whereIn('products.status', ['in_stock', 'out_of_stock', 'pending'])
        ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
        ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
        ->select(
            'products.id',
            'products.name',
            'products.status',
            DB::raw('COALESCE(SUM(CASE WHEN orders.status IN ("completed","delivered","success")
                AND orders.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN order_items.quantity ELSE 0 END), 0) AS sold_qty'),
            DB::raw('COALESCE(SUM(CASE WHEN orders.status IN ("completed","delivered","success")
                AND orders.created_at BETWEEN "' . $start . '" AND "' . $end . '"
                THEN order_items.price * order_items.quantity ELSE 0 END), 0) AS revenue')
        )

            ->groupBy('products.id', 'products.name', 'products.status')
            ->orderByDesc('sold_qty')
            ->limit(10) // ✅ chỉ lấy top 10
            ->get();

        // ==== Chuẩn bị dữ liệu biểu đồ ====
        $labels = $data->pluck('name');
        $values = $data->pluck('sold_qty');

        return view('admin.analytics_products', compact(
            'period', 'label', 'data', 'labels', 'values',
            'years', 'year', 'month', 'week', 'weeksList', 'quarter'
        ));
    }

    // ==== Hàm chia tháng thành các tuần ====
    private function weeksOfMonth(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $weeks = [];

        // Tuần 1: từ ngày 1 -> CN đầu tiên
        if ($startOfMonth->dayOfWeek === Carbon::SUNDAY) {
            $firstWeekEnd = $startOfMonth->copy();
        } else {
            $firstWeekEnd = $startOfMonth->copy()->next(Carbon::SUNDAY);
            if ($firstWeekEnd > $endOfMonth) $firstWeekEnd = $endOfMonth;
        }

        $weeks[] = ['start' => $startOfMonth->copy(), 'end' => $firstWeekEnd->copy()];

        // Các tuần tiếp theo: Thứ 2 -> CN
        $cursor = $firstWeekEnd->copy()->addDay();
        while ($cursor <= $endOfMonth) {
            $weekStart = $cursor->copy();
            $weekEnd = $weekStart->copy()->next(Carbon::SUNDAY);
            if ($weekEnd > $endOfMonth) $weekEnd = $endOfMonth;

            $weeks[] = ['start' => $weekStart, 'end' => $weekEnd];
            $cursor = $weekEnd->copy()->addDay();
        }

        return $weeks;
    }
}
