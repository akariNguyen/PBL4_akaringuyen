@extends('admin.admin')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">{{ isset($voucher) ? '✏️ Cập nhật Voucher' : '➕ Thêm Voucher Mới' }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ isset($voucher) ? route('admin.vouchers.update', $voucher->id) : route('admin.vouchers.store') }}">
                @csrf
                @if(isset($voucher))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Mã Voucher</label>
                    <input type="text" name="code" value="{{ old('code', $voucher->code ?? '') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số tiền giảm</label>
                    <input type="number" name="discount_amount" value="{{ old('discount_amount', $voucher->discount_amount ?? '') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ngày hết hạn</label>
                    <input type="date" name="expiry_date"
                        value="{{ old('expiry_date', isset($voucher) ? \Carbon\Carbon::parse($voucher->expiry_date)->format('Y-m-d') : '') }}"
                        class="form-control" required>

                </div>

                <button class="btn btn-success">{{ isset($voucher) ? 'Lưu thay đổi' : 'Thêm mới' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
