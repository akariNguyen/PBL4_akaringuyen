<template id="tpl-product-add">
    @if($shop && $shop->status === 'suspended')
    <div class="suspended-alert">
        <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
        <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
    </div>
    @endif
    <div class="card">
        <h2 style="margin:0 0 12px 0;">Thêm sản phẩm</h2>
        <p style="margin:0 0 16px 0; color:#6b7280;">Nếu loại sản phẩm chưa có, chọn "Khác" và nhập loại mới.</p>
        <form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data" class="product-form">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Tên sản phẩm</label>
                <input type="text" name="name" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
            </div>
            <div>
                <label style="display:block; font-weight:600; margin-bottom:6px;">Loại sản phẩm</label>
                <select id="category_select"
                        style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}">{{ ucfirst($cat->name) }}</option>
                    @endforeach
                    <option value="__other__">Khác</option>
                </select>
            </div>

            <div id="category_other_wrap" style="display:none;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Loại khác</label>
                <input type="text" id="category_other" placeholder="Nhập loại sản phẩm"
                    style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
            </div>

            <input type="hidden" name="category" id="category_value">

            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Mô tả chi tiết</label>
                <textarea name="description" rows="4" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Giá bán (VNĐ)</label>
                    <input type="number" step="0.01" name="price" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Số lượng</label>
                    <input type="number" name="quantity" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Hình ảnh (có thể chọn nhiều)</label>
                <input type="file" name="images[]" accept="image/*" multiple required>

            </div>
            <div>
                <button type="submit" style="padding:10px 16px; border-radius:8px; border:1px solid #2563eb; background:#2563eb; color:#fff;">Tạo sản phẩm</button>
            </div>
        </form>
    </div>
</template>



