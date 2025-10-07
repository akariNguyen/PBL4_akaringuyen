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
    <h1 class="mb-4">⏳ Duyệt tạo shop</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($shops->isEmpty())
                <div class="alert alert-info">Không có shop nào cần duyệt.</div>
            @else
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Tên Shop</th>
                            <th>Chủ shop</th>
                            <th>Logo</th>
                            <th>Mô tả</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shops as $shop)
                        <tr>
                            <td>{{ $shop->name }}</td>
                            <td>{{ $shop->user?->name ?? '—' }}</td> {{-- Fix phụ: Đổi owner → user --}}
                            <td>
                                <img src="{{ $shop->logo_path ? asset('storage/'.$shop->logo_path) : asset('Picture/default_shop.png') }}" 
                                     alt="Logo" style="height:40px; width:40px; border-radius:6px;">
                            </td>
                            <td>{{ Str::limit($shop->description, 50) }}</td>
                            <td>{{ $shop->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center align-middle" style="vertical-align: middle;">
                                <div class="d-flex justify-content-center align-items-center gap-2" style="height: 100%;">
                                    <form action="{{ route('admin.shops.approve', $shop->user_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success btn-sm px-3 py-1" style="vertical-align: middle;">✔ Duyệt</button>
                                    </form>
                                    <form action="{{ route('admin.shops.reject', $shop->user_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-danger btn-sm px-3 py-1" style="vertical-align: middle;">✖ Từ chối</button>
                                    </form>
                                </div>
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