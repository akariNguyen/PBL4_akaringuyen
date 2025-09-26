<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin shop - E‑Market</title>
    <style>
        body { font-family: Inter, Arial, sans-serif; margin:0; background:#fafafa; }
        .topbar { height:56px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; padding:0 16px; background:#fff; }
        .topbar a { text-decoration:none; color:#111827; }
        .container { max-width: 960px; margin: 16px auto; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; }
        .row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
        .label { color:#6b7280; min-width:160px; }
        input[type="text"], textarea, select { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; }
        .btn { padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
        .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .icon-btn { border:none; background:none; cursor:pointer; color:#2e7d32; padding:10px; font-size:18px; border-radius:8px; }
        .icon-btn:hover { background:#f3f4f6; color:#1b5e20; }
        .logo { height:56px; width:56px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; }
        .view-text { display:inline; }
        .edit-input { display:none; max-width:300px; }
        .editing .view-text { display:none; }
        .editing .edit-input { display:inline-block; }
        .save-bar { display:none; justify-content:flex-start; gap:8px; margin-top:8px; }
        .editing .save-bar { display:flex; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="/">
            <img src="/Picture/logo.png" alt="E‑Market" style="height:28px; width:auto;"> E‑Market
        </a>
    </div>
    <div class="container">
        @if(session('success'))
            <div style="background:#ecfdf5; color:#047857; padding:10px 12px; border:1px solid #a7f3d0; border-radius:8px; margin-bottom:12px;">{{ session('success') }}</div>
        @endif
        @php($shop = \App\Models\Shop::find(auth()->id()))
        <form id="formShop" method="post" action="{{ route('account.shop.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="row" style="justify-content:space-between; margin-bottom:16px;">
                <h3 style="margin:0;">Thông tin shop</h3>
                <button type="button" id="btnEditShop" class="icon-btn" title="Chỉnh sửa">✎</button>
            </div>
            <div class="row">
                <div class="label">Logo</div>
                <div>
                    @if($shop && $shop->logo_path)
                        <img id="logo_img" src="{{ Storage::disk('public')->url($shop->logo_path) }}" class="logo" alt="logo">
                    @else
                        <img id="logo_img" src="/Picture/logo.png" class="logo" alt="logo">
                    @endif
                    <input id="logo_input" type="file" name="logo" accept="image/*" class="edit-input" style="margin-left:12px;">
                </div>
            </div>
            <div class="row">
                <div class="label">Tên shop</div>
                <div>
                    <span class="view-text">{{ $shop->name ?? '—' }}</span>
                    <input class="edit-input" type="text" name="name" value="{{ $shop->name ?? '' }}">
                </div>
            </div>
            <div class="row">
                <div class="label">Mô tả</div>
                <div>
                    <span class="view-text">{{ $shop->description ?? '—' }}</span>
                    <textarea class="edit-input" name="description" rows="3" style="width:100%; max-width:480px;">{{ $shop->description ?? '' }}</textarea>
                </div>
            </div>