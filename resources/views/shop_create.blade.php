<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo shop</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background:#f9fafb; margin:0; }
        .container { max-width: 720px; margin: 24px auto; background:#fff; padding:24px; border-radius:12px; border:1px solid #e5e7eb; }
        .field { margin-bottom:14px; }
        label { display:block; margin-bottom:6px; font-weight:600; font-size:14px; color:#374151; }
        input[type="text"], textarea, select { width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:8px; background:#fff; }
        input[type="file"] { display:block; margin-top:6px; }
        .actions { margin-top:16px; display:flex; gap:12px; }
        .btn { padding:10px 16px; border-radius:8px; border:1px solid #e5e7eb; cursor:pointer; }
        .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .error { background:#fee2e2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px; }
        .header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
        .brand { display:flex; align-items:center; gap:10px; font-weight:700; font-size:20px; color:#111827; }
        .brand img { height:36px; width:auto; display:block; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand"><img src="/Picture/logo.png" alt="Logo"> E-Market</div>
            <div><a href="/" style="text-decoration:none; color:#2563eb;">Trang chủ</a></div>
        </div>

        <h2 style="margin:0 0 12px 0;">Tạo thông tin shop</h2>
        <p style="margin:0 0 16px 0; color:#6b7280;">Điền thông tin bên dưới để khởi tạo shop của bạn.</p>

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0; padding-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('shops.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="name">Tên shop</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label for="logo">Logo / Ảnh đại diện shop</label>
                <input type="file" id="logo" name="logo" accept="image/*">
            </div>
            <div class="field">
                <label for="description">Mô tả shop</label>
                <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="field">
                <label for="status">Trạng thái shop</label>
                <select id="status" name="status" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Tạo shop</button>
                <a class="btn" href="{{ route('seller.dashboard') }}" style="text-decoration:none; color:#111827;">Hủy</a>
            </div>
        </form>
    </div>
</body>
</html>


