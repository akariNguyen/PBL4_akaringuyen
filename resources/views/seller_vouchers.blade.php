@extends('seller_dashboard')

@section('content')
<div class="container mt-4">
    <h2>🎟️ Quản lý Voucher</h2>

    <a href="{{ route('seller.vouchers.create') }}" class="btn btn-primary mb-3">
        + Thêm Voucher Mới
    </a>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered text-center align-middle" id="tpl-vouchers">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Giảm giá (₫)</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#888;">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🔥 Script quản lý voucher --}}
<script>
// 🧾 Hàm load danh sách voucher
function bindVouchers() {
    const tbody = document.querySelector('#tpl-vouchers tbody');
    if (!tbody) return;

    fetch('/seller/vouchers/json')
        .then(res => res.json())
        .then(vouchers => {
            tbody.innerHTML = '';

            if (!vouchers.length) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center; padding:20px; color:#888;">
                        ⚠️ Chưa có voucher nào.
                    </td></tr>`;
                return;
            }

            vouchers.forEach(v => {
                const row = document.createElement('tr');
                row.dataset.id = v.id;
                row.innerHTML = `
                    <td>${v.code}</td>
                    <td><input type="number" value="${v.discount_amount}" style="width:100px;text-align:center;"></td>
                    <td><input type="date" value="${v.expiry_date}"></td>
                    <td>
                        <select>
                            <option value="active" ${v.status === 'active' ? 'selected' : ''}>Hoạt động</option>
                            <option value="expired" ${v.status === 'expired' ? 'selected' : ''}>Hết hạn</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-success btn-save">Lưu</button>
                        <button class="btn btn-danger btn-delete">Xóa</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(err => {
            console.error('Lỗi tải voucher:', err);
            tbody.innerHTML = `
                <tr><td colspan="5" style="text-align:center;color:red;">
                    ❌ Lỗi tải dữ liệu voucher.
                </td></tr>`;
        });
}

// 🗑️ Xóa voucher
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.dataset.id;

    if (!confirm('⚠️ Bạn có chắc muốn xóa voucher này không?')) return;

    try {
        const res = await fetch(`/seller/vouchers/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();

        if (data.success) {
            alert('🗑️ Đã xóa voucher!');
            row.remove();
        } else {
            alert('❌ ' + (data.message || 'Không thể xóa voucher!'));
        }
    } catch (err) {
        console.error('Fetch error:', err);
        alert('❌ Lỗi kết nối tới server!');
    }
});

// 🚀 Khi trang load xong, tự động load voucher
document.addEventListener('DOMContentLoaded', bindVouchers);
</script>
@endsection
