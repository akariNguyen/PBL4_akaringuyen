<template id="tpl-voucher-add">
    @if($shop && $shop->status === 'suspended')
    <div class="suspended-alert">
        <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
        <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
    </div>
    @endif
<div class="card">
    <h2 style="margin-bottom:12px;">➕ Thêm Voucher Mới</h2>
    <form id="voucherAddForm">
        @csrf
        <div style="margin-bottom:12px;">
            <label>Mã voucher</label>
            <input type="text" name="code" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="margin-bottom:12px;">
            <label>Số tiền giảm (VNĐ)</label>
            <input type="number" name="discount_amount" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="margin-bottom:12px;">
            <label>Ngày hết hạn</label>
            <input type="date" name="expiry_date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn green">Lưu</button>
            <a href="#" onclick="event.preventDefault(); navigate('vouchers')" class="btn">Quay lại</a>
        </div>
    </form>
</div>
</template>



