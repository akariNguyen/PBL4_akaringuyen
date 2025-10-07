@extends('admin.admin')

@push('styles')
<style>
:root {
  --border:#e5e7eb;
  --muted:#6b7280;
}

/* CARD THỐNG KÊ */
.stat-card {
  border-radius: 12px;
  padding: 20px 0;
  text-align: center;
  color: #fff;
  transition: .25s;
  width: 100%;
}
.stat-card:hover { transform: translateY(-3px); }
.stat-card h6 { font-size: 15px; margin-bottom: 6px; font-weight: 600; }
.stat-card h3 { font-size: 28px; margin: 0; font-weight: 700; }

.bg-pending { background: #facc15; color: #1f2937; }
.bg-paid { background: #2563eb; }
.bg-shipped { background: #06b6d4; }
.bg-completed { background: #16a34a; }
.bg-cancelled { background: #dc2626; }

.table th {
  background: #f3f4f6 !important;
  text-transform: uppercase;
  font-size: 13px;
  letter-spacing: .4px;
}

.status-badge {
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 600;
  text-transform: capitalize;
  color: #fff;
}
.status-pending { background: #facc15; color:#78350f; }
.status-paid { background: #2563eb; }
.status-shipped { background: #06b6d4; }
.status-completed { background: #16a34a; }
.status-cancelled { background: #dc2626; }

/* CHI TIẾT */
.detail-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 20px;
}
.detail-card h5 { font-weight: 700; margin-bottom: 12px; }
.detail-flex {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 40px;
}
.detail-left, .detail-right { flex: 1; }
.divider { width: 1px; background: #e5e7eb; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">📦 Quản lý đơn hàng</h1>

    <!-- 5 CARD THỐNG KÊ FULL NGANG -->
    <div class="row text-center mb-4 g-3">
        <div class="col-6 col-md-2 flex-fill">
            <div class="stat-card bg-pending">
                <h6>Chờ xử lý</h6>
                <h3>{{ \App\Models\Order::where('status','pending')->count() }}</h3>
            </div>
        </div>
        
        <div class="col-6 col-md-2 flex-fill">
            <div class="stat-card bg-shipped">
                <h6>Đang giao</h6>
                <h3>{{ \App\Models\Order::where('status','shipped')->count() }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-2 flex-fill">
            <div class="stat-card bg-completed">
                <h6>Hoàn tất</h6>
                <h3>{{ \App\Models\Order::where('status','completed')->count() }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-2 flex-fill">
            <div class="stat-card bg-cancelled">
                <h6>Đã hủy</h6>
                <h3>{{ \App\Models\Order::where('status','cancelled')->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Danh sách đơn hàng</h4>

                <!-- Bộ lọc ngày -->
                <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex gap-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from', $defaultFrom) }}">
                    <input type="date" name="to" class="form-control" value="{{ request('to', $defaultTo) }}">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    @if(request()->has('from') || request()->has('to'))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>

            </div>

            <!-- Search -->
            <div class="mb-3">
                <form method="GET" action="{{ route('admin.orders.index') }}">
                    <input type="text" name="search" class="form-control"
                           placeholder="🔍 Tìm kiếm theo tên khách hàng hoặc tên shop..."
                           value="{{ request('search') }}" oninput="this.form.submit()">
                </form>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link {{ $status=='pending'?'active':'' }}" href="{{ route('admin.orders.index',['status'=>'pending']) }}">Chờ xử lý</a></li>
              
                <li class="nav-item"><a class="nav-link {{ $status=='shipped'?'active':'' }}" href="{{ route('admin.orders.index',['status'=>'shipped']) }}">Đang giao</a></li>
                <li class="nav-item"><a class="nav-link {{ $status=='completed'?'active':'' }}" href="{{ route('admin.orders.index',['status'=>'completed']) }}">Hoàn tất</a></li>
                <li class="nav-item"><a class="nav-link {{ $status=='cancelled'?'active':'' }}" href="{{ route('admin.orders.index',['status'=>'cancelled']) }}">Đã hủy</a></li>
            </ul>

            <!-- TABLE -->
            @if($orders->isEmpty())
                <div class="alert alert-info mt-3">Không có đơn hàng nào trong trạng thái này.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mt-3 align-middle w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Shop</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $firstItem = $order->items->first();
                                    $seller = $firstItem?->product?->seller;
                                    $shop = $seller?->shop;
                                @endphp
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->user->name ?? 'Không xác định' }}</td>
                                    <td>{{ $shop->name ?? '—' }}</td>
                                    <td>{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                                    <td><span class="status-badge status-{{ $order->status }}">{{ $order->status }}</span></td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td><button class="btn btn-sm btn-primary" onclick="toggleDetail({{ $order->id }})">Xem thêm</button></td>
                                </tr>

                                <!-- Chi tiết -->
                                <tr id="detail-{{ $order->id }}" style="display:none;">
                                    <td colspan="7">
                                        <div class="detail-card">
                                            <button class="btn-close float-end" onclick="toggleDetail({{ $order->id }})"></button>

                                            <div class="detail-flex">
                                                <div class="detail-left">
                                                    <h5>👤 Thông tin khách hàng</h5>
                                                    <p><strong>Tên:</strong> {{ $order->user->name ?? 'Không xác định' }}</p>
                                                    <p><strong>Email:</strong> {{ $order->user->email ?? 'Không xác định' }}</p>
                                                </div>

                                                <div class="divider"></div>

                                                <div class="detail-right">
                                                    <h5>🏬 Thông tin shop</h5>
                                                    <p><strong>Tên shop:</strong> {{ $shop->name ?? 'Không xác định' }}</p>
                                                    <p><strong>Chủ shop:</strong> {{ $seller->name ?? 'Không xác định' }}</p>
                                                    <p><strong>Email:</strong> {{ $seller->email ?? 'Không xác định' }}</p>
                                                </div>
                                            </div>

                                            <h5 class="mt-4">🛍️ Danh sách sản phẩm</h5>
                                            <table class="table table-bordered mt-2">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tên sản phẩm</th>
                                                        <th>Giá</th>
                                                        <th>Số lượng</th>
                                                        <th>Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                        <tr>
                                                            <td>{{ $item->product_name ?? 'Sản phẩm đã xóa' }}</td>
                                                            <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                                            <td>{{ $item->quantity }}</td>
                                                            <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(id){
  document.querySelectorAll('tr[id^="detail-"]').forEach(tr => {
    if(tr.id !== 'detail-' + id) tr.style.display = 'none';
  });
  const el = document.getElementById('detail-' + id);
  el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'table-row' : 'none';
}
</script>
@endpush
