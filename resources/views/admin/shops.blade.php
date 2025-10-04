@extends('admin.admin')

@push('styles')
<style>
  .table th {
    background: #f3f4f6 !important;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: .5px;
  }
  .status-btn {
    font-size: 14px;
    padding: 6px 14px;
    border-radius: 8px;
    min-width: 110px;
    text-align: center;
    border: none;
    cursor: pointer;
    text-transform: capitalize;
  }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Quản lý Shop</h1>

    <!-- Thống kê -->
    <div class="row text-center mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Tổng số shop</h6>
                    <h3>{{ $shopCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card quản lý -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Danh sách shop</h4>

                <!-- Bộ lọc ngày -->
                <form method="GET" action="{{ route('admin.shops.index') }}" class="d-flex gap-2">
                    <input type="date" name="from" class="form-control" 
                           value="{{ request('from', $defaultFrom) }}">
                    <input type="date" name="to" class="form-control" 
                           value="{{ request('to', $defaultTo) }}">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    @if(request()->has('from') || request()->has('to'))
                        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Search -->
            <div class="mb-3">
                <form method="GET" action="{{ route('admin.shops.index') }}">
                    <input type="text" name="search" class="form-control" 
                           placeholder="🔍 Tìm kiếm theo tên shop..." 
                           value="{{ request('search') }}" oninput="this.form.submit()">
                </form>
            </div>

            <!-- Thông báo lỗi -->
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Bảng shop -->
            <table class="table table-striped text-start align-middle">
                <thead class="table-light">
                    <tr>
                        <th>
                            <a href="{{ route('admin.shops.index', array_merge(request()->all(), [
                                'sort_by' => 'name',
                                'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-decoration-none text-dark">
                                Tên shop
                                @if(request('sort_by') === 'name')
                                    <span>{{ request('sort_order') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th>Logo</th>
                        <th>Chi tiết</th>
                        <th>
                            <a href="{{ route('admin.shops.index', array_merge(request()->all(), [
                                'sort_by' => 'created_at',
                                'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-decoration-none text-dark">
                                Ngày tạo
                                @if(request('sort_by') === 'created_at')
                                    <span>{{ request('sort_order') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th>Tình trạng</th>
                        <th>Xem thêm</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        <tr>
                            <td>{{ $shop->name }}</td>
                            <td>
                                <img src="{{ $shop->logo_path ? asset('storage/'.$shop->logo_path) : asset('Picture/default_shop.png') }}" 
                                    alt="Logo" style="height:40px; width:40px; border-radius:6px;">
                            </td>
                            <td>{{ Str::limit($shop->description, 50) }}</td>
                            <td>{{ \Carbon\Carbon::parse($shop->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.shops.toggleStatus', $shop->user_id) }}" method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn đổi tình trạng shop này?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="status-btn {{ $shop->status == 'active' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                        {{ $shop->status }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.shops.show', $shop->user_id) }}" 
                                   class="btn btn-sm btn-info">Xem thêm</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Không tìm thấy kết quả</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
