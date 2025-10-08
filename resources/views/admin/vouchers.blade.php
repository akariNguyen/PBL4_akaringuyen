@extends('admin.admin')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">🎟️ Danh sách Voucher</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary mb-3">➕ Thêm voucher mới</a>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Giảm giá</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                    <tr>
                        <td>{{ $v->code }}</td>
                        <td>{{ number_format($v->discount_amount) }} VND</td>
                        <td>{{ $v->expiry_date }}</td>
                        <td>{{ $v->status }}</td>
                        <td>
                            <a href="{{ route('admin.vouchers.edit', $v->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                            <form action="{{ route('admin.vouchers.destroy', $v->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa voucher này?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Chưa có voucher nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
