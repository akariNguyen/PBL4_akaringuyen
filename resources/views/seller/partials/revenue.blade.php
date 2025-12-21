<template id="tpl-revenue-report">
    @if($shop && $shop->status === 'suspended')
    <div class="suspended-alert">
        <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
        <p style="margin:0; font-size:14px;">
            Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết.
            Các chức năng quản lý có thể bị hạn chế.
        </p>
    </div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <h2 style="margin-bottom:16px;">📊 Thống kê doanh thu</h2>

        <!-- Bộ lọc năm -->
        <form id="yearForm" method="GET"
              style="margin-bottom:20px; display:flex; align-items:center; gap:12px;">
            <label for="yearSelect">Năm:</label>
            <select id="yearSelect" name="year"
                style="padding:8px 12px; border-radius:8px; border:1px solid #d1d5db;"
                onchange="this.form.submit()">

                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}"
                        {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor

            </select>
        </form>

        <!-- Thống kê tổng quan -->
        <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:24px;">
            <div class="metric">
                <h3>🧾 Số đơn hoàn tất</h3>
                <div class="val" id="totalOrdersDisplay">0</div>
            </div>

            <div class="metric">
                <h3>📦 Sản phẩm đã bán</h3>
                <div class="val" id="soldCountDisplay">0</div>
            </div>

            <div class="metric">
                <h3>💰 Tổng doanh thu</h3>
                <div class="val" id="totalRevenueDisplay" style="color:#16a34a;">
                    0 ₫
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="card" style="padding:16px; height:380px;">
            <canvas id="chartRevenue" style="width:100%; height:100%;"></canvas>
        </div>
    </div>
</template>


