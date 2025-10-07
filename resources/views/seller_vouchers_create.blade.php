@extends('seller_dashboard')

@section('content')
<div class="container mt-4">
    <h2>➕ Thêm Voucher Mới</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="voucherAddForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Mã Voucher</label>
                    <input type="text" name="code" class="form-control" placeholder="VD: SALE2025" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số tiền giảm (₫)</label>
                    <input type="number" name="discount_amount" class="form-control" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ngày hết hạn</label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">💾 Lưu</button>
                <a href="{{ route('seller.vouchers.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
            </form>
        </div>
    </div>
</div>

{{-- 🔥 Script xử lý thêm voucher --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('voucherAddForm');
    if (!form) return;

    form.onsubmit = async (e) => {
        e.preventDefault();
        const data = new FormData(form);

        try {
            const res = await fetch('{{ route("seller.vouchers.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });

            if (!res.ok) throw new Error(`Server trả lỗi ${res.status}`);
            const d = await res.json();

            if (d.success) {
                alert('✅ Thêm voucher thành công!');
                window.location.href = '{{ route("seller.vouchers.index") }}';
            } else {
                alert('❌ ' + (d.message || 'Không thể thêm voucher!'));
            }
        } catch (err) {
            console.error('❌ Lỗi khi thêm voucher:', err);
            alert('⚠️ Lỗi kết nối tới server hoặc phản hồi không hợp lệ!');
        }
    };
});
</script>
@endsection
