<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop bị từ chối - Chỉnh sửa và gửi lại</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; margin: 0; padding: 20px; }
        .container { max-width: 720px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .popup { background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        .field { margin-bottom: 14px; }
        label { display: block; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input[type="text"], textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; }
        input[type="file"] { margin-top: 6px; width: 100%; }
        .btn { background: #2563eb; color: white; padding: 10px 16px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn:hover { background: #1d4ed8; }
        .logo-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px; display: block; }
        .error { color: #dc2626; font-size: 0.875em; margin-top: 0.25rem; display: block; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2563eb; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .success-popup { background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('home') }}" class="back-link">← Quay lại Trang chủ</a>

        <div class="popup">
            <strong>⚠️ Shop của bạn đã bị từ chối!</strong>
            <p>Vui lòng kiểm tra lại thông tin và gửi lại để được duyệt. Lý do từ chối (nếu có): {{ $shop->reject_reason ?? 'Không được chỉ định.' }}</p>
        </div>

        @if ($errors->any())
            <div class="popup">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="success-popup">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('seller.shop.resubmit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="name">Tên shop <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $shop->name) }}" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="description">Mô tả shop</label>
                <textarea id="description" name="description" rows="4" placeholder="Mô tả ngắn gọn về shop của bạn...">{{ old('description', $shop->description) }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="logo">Logo shop (khuyến nghị: 120x120px)</label>
                <input type="file" id="logo" name="logo" accept="image/*">
                @if($shop->logo_path)
                    <img src="{{ asset('storage/' . $shop->logo_path) }}" alt="Logo hiện tại" class="logo-preview">
                    <p style="font-size: 0.875em; color: #6b7280; margin-top: 5px;">Logo hiện tại (có thể thay thế bằng file mới).</p>
                @else
                    <p style="font-size: 0.875em; color: #6b7280; margin-top: 5px;">Chưa có logo, hãy upload một ảnh.</p>
                @endif
                @error('logo')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn">Gửi lại để duyệt</button>
        </form>
    </div>
    <script>
document.querySelector('form').addEventListener('submit', function(e) {
    // Ngăn submit mặc định để hiển thị thông báo tạm thời
    e.preventDefault();

    // Hiển thị thông báo gửi thành công
    const successBox = document.createElement('div');
    successBox.className = 'success-popup';
    successBox.innerText = '✅ Đã gửi yêu cầu thành công! Đang chuyển về trang đăng nhập...';
    document.querySelector('.container').prepend(successBox);

    // Gửi form thật lên server (ẩn)
    e.target.submit();

    // Sau 2 giây thì chuyển sang trang đăng nhập
    setTimeout(() => {
        window.location.href = "{{ route('login') }}";
    }, 2000);
});
</script>

</body>
</html>