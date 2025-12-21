<template id="tpl-orders-all">
    <?php
        use App\Models\Order;
        use App\Models\OrderItem;
        $sellerId = auth()->id();
        $orders = Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->with(['items' => function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        }, 'user'])->latest()->get();
        $completedCount = $orders->where('status', 'completed')->count();
        $undeliveredCount = $orders->whereIn('status', ['pending', 'shipped'])->count();
        $cancelledCount = $orders->where('status', 'cancelled')->count();
    ?>
    
    <div class="card" style="margin-bottom:16px;">
        <h2 style="margin:0 0 8px 0;">Thống kê đơn hàng</h2>
        <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="metric">
                <h3>Đã giao</h3>
                <div class="val">{{ $completedCount }}</div>
            </div>
            <div class="metric">
                <h3>Chưa giao</h3>
                <div class="val">{{ $undeliveredCount }}</div>
            </div>
            <div class="metric">
                <h3>Hủy</h3>
                <div class="val">{{ $cancelledCount }}</div>
            </div>
        </div>
    </div>
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <h2 style="margin:0;">Quản lý tất cả đơn hàng</h2>
            <div style="display:flex; gap:12px;">
                <input id="filterFrom" type="date" style="padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                <input id="filterTo" type="date" style="padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                <button id="btnFilter" class="btn primary">Lọc</button>
            </div>
        </div>
        <div class="tabs">
            <button class="tab active" data-tab="pending">Chờ xử lý</button>
            <button class="tab" data-tab="shipped">Đang giao</button>
            <button class="tab" data-tab="completed">Hoàn thành</button>
            <button class="tab" data-tab="cancelled">Đã hủy</button>
        </div>
        <div id="ordersList">
            <!-- Danh sách đơn hàng sẽ được render động bằng JS -->
        </div>
    </div>
    <!-- Dữ liệu orders JSON để JS xử lý -->
    <script>
        // ✅ Chỉ khởi tạo allOrders lần đầu tiên, sau đó giữ nguyên giá trị đã cập nhật
        if (!window.allOrders) {
            window.allOrders = @json($orders);
        }
    </script>
</template>



