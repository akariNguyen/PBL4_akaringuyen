<template id="tpl-vouchers">
<?php
    $shop = \App\Models\Shop::where('user_id', auth()->id())->first();
    $vouchers = $shop ? \App\Models\Voucher::where('shop_id', $shop->user_id)->latest()->get() : collect();
?>
   
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2>🎟️ Quản lý Voucher</h2>
        <a href="#" onclick="event.preventDefault(); navigate('voucher_add')" class="btn primary">+ Thêm Voucher</a>
    </div>
    <table style="width:100%; border-collapse:separate; border-spacing:0 8px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;">Mã</th>
                <th style="padding:12px;">Giảm giá</th>
                <th style="padding:12px;">Hết hạn</th>
                <th style="padding:12px;">Trạng thái</th>
                <th style="padding:12px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $v)
            <tr data-id="{{ $v->id }}" style="background:#fff; border:1px solid #e5e7eb;">
                <td style="padding:12px;">{{ $v->code }}</td>
                <td style="padding:12px;"><input type="number" value="{{ $v->discount_amount }}" style="width:100px; text-align:center;"></td>
                <td style="padding:12px;"><input type="date" value="{{ $v->expiry_date->format('Y-m-d') }}"></td>
                <td style="padding:12px;">
                    <select>
                        <option value="active" {{ $v->status=='active'?'selected':'' }}>Hoạt động</option>
                        <option value="expired" {{ $v->status=='expired'?'selected':'' }}>Hết hạn</option>
                    </select>
                </td>
                <td style="padding:12px;">
                    <button class="btn green btn-save">Lưu</button>
                    <button class="btn red btn-delete">Xóa</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</template>



