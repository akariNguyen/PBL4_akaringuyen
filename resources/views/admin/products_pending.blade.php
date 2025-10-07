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
    <h1 class="mb-4">⏳ Duyệt sản phẩm</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($products->isEmpty())
                <div class="alert alert-info text-center">Không có sản phẩm nào chờ duyệt.</div>
            @else
                <table class="table table-striped align-middle text-center">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Người bán</th>
                            <th>Ngày tạo</th>
                            <th>Tình trạng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                            @php
                                $imgs = is_array($p->images) ? $p->images : json_decode($p->images, true);
                                $img = (is_array($imgs) && count($imgs)) 
                                    ? Storage::disk('public')->url($imgs[0])
                                    : '/Picture/products/Aothun.jpg';
                                $sellerName = $p->seller->name ?? '—';
                                $shopName = $p->seller->shop->name ?? '—';
                            @endphp

                            <tr>
                                <td>
                                    <img src="{{ $img }}" alt="{{ $p->name }}" style="height:50px;width:50px;border-radius:6px;object-fit:cover;">
                                </td>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->category->name ?? '—' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $sellerName }}</div>
                                    <div class="text-muted small">{{ $shopName }}</div>
                                </td>
                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $p->status }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.products.approve', $p->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success btn-sm">Duyệt</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.reject', $p->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-danger btn-sm">Từ chối</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
