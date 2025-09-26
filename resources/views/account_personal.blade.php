<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - E‑Market</title>
    <style>
        body { font-family: Inter, Arial, sans-serif; margin:0; background:#fafafa; }
        .topbar { height:56px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px; padding:0 16px; background:#fff; }
        .topbar a { text-decoration:none; color:#111827; }
        .brand { display:flex; align-items:center; gap:10px; font-weight:700; font-size:22px; }
        .brand img { height:80px; width:auto; }
        .container { max-width: 960px; margin: 16px auto; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; }
        .tabs { display:flex; gap:0; border-bottom:1px solid #e5e7eb; margin-bottom:12px; }
        .tab { padding:10px 14px; cursor:pointer; border:0; background:none; color:#6b7280; font-weight:600; }
        .tab.active { color:#111827; position:relative; }
        .tab.active::after { content:""; position:absolute; left:10px; right:10px; bottom:-1px; height:2px; background:#2563eb; }
        .section { padding-top:8px; }
        .row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
        .label { color:#6b7280; min-width:140px; }
        input[type="text"], input[type="password"] { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; }
        .edit-input { max-width: 250px; }
        .btn { padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
        .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .icon-btn { 
            border:none; 
            background:none; 
            cursor:pointer; 
            color:#2e7d32; 
            padding:10px; 
            font-size: 18px; 
            border-radius: 8px; 
        }
        .icon-btn:hover { background:#f3f4f6; color:#1b5e20; }
        .muted { color:#6b7280; }
        .avatar { height:48px; width:48px; border-radius:999px; object-fit:cover; }
        .actions { display:flex; gap:8px; }
        .hint { color:#6b7280; font-size:13px; }
        .inline { flex:1; }
        .view-text { display:inline; }
        .edit-input { display:none; }
        .editing .view-text { display:none; }
        .editing .edit-input { display:inline-block; }
        .save-bar { display:none; justify-content:flex-start; gap:8px; margin-top:8px; }
        .editing .save-bar { display:flex; }
        .success-message { background:#ecfdf5; color:#047857; padding:10px 12px; border:1px solid #a7f3d0; border-radius:8px; margin-bottom:12px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <img src="/Picture/logo.png" alt="E‑Market">
            <span style="color:#2563eb;">E‑Market</span>
        </div>
    </div>
    <div class="container">
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        @if(session('success_password'))
            <div class="success-message">{{ session('success_password') }}</div>
        @endif
        <div class="back-bar" style="margin-bottom:12px;">
            <button onclick="window.history.back()" 
                    style="background:none; border:none; color:#2563eb; font-weight:600; cursor:pointer; font-size:14px; display:flex; align-items:center; gap:6px;">
                ← Quay lại
            </button>
        </div>

        <div class="tabs">
            <button class="tab active" data-tab="info" id="tab-info-btn">Thông tin cá nhân</button>
            <button class="tab" data-tab="password" id="tab-password-btn">Đổi mật khẩu</button>
        </div>

        <div id="tab-info" class="section">
            <form id="formInfo" method="post" action="{{ route('account.personal.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row" style="justify-content:space-between; margin-bottom:16px;">
                    <h3 style="margin:0;">Thông tin</h3>
                    <button type="button" id="btnEdit" class="icon-btn" title="Chỉnh sửa" aria-label="Chỉnh sửa">
                        ✎
                    </button>
                </div>
                <div class="row">
                    <div class="label">Avatar</div>
                    <div class="inline">
                        @if(auth()->user()->avatar_path)
                            <img id="avatar_img" src="{{ strpos(auth()->user()->avatar_path, '/Picture/') === 0 ? auth()->user()->avatar_path : Storage::disk('public')->url(auth()->user()->avatar_path) }}" alt="avatar" class="avatar">
                        @else <img id="avatar_img" src="/Picture/avata_macdinh_nam.png" alt="avatar" class="avatar"> @endif
                        <input id="avatar_input" class="edit-input" type="file" name="avatar" accept="image/*" style="margin-left:12px;">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Họ tên</div>
                    <div class="inline">
                        <span id="name_text" class="view-text">{{ auth()->user()->name }}</span>
                        <input class="edit-input" type="text" name="name" value="{{ auth()->user()->name }}">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Email</div>
                    <div class="inline"><span class="muted">{{ auth()->user()->email }}</span></div>
                </div>
                <div class="row">
                    <div class="label">Số điện thoại</div>
                    <div class="inline">
                        <span id="phone_text" class="view-text">{{ auth()->user()->phone }}</span>
                        <input class="edit-input" type="text" name="phone" value="{{ auth()->user()->phone }}">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Giới tính</div>
                    <div class="inline">{{ auth()->user()->gender === 'female' ? 'Nữ' : 'Nam' }}</div>
                </div>
                <div class="row">
                    <div class="label">Vai trò</div>
                    <div class="inline">{{ auth()->user()->role }}</div>
                </div>
                <div class="row">
                    <div class="label">Trạng thái</div>
                    <div class="inline">{{ auth()->user()->status }}</div>
                </div>

                @if ($errors->any())
                    <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                        <ul style="margin:0; padding-left:16px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="save-bar">
                    <button type="submit" class="btn primary">Lưu</button>
                    <button type="button" id="btnCancel" class="btn">Hủy</button>
                </div>
            </form>
            <p class="hint" style="margin-top:8px;">Bấm biểu tượng ✎ để chỉnh sửa trực tiếp họ tên, số điện thoại và avatar. Chọn ảnh để xem trước, chỉ lưu khi bấm Lưu.</p>
        </div>

        <div id="tab-password" class="section" style="display:none;">
            <form id="formPassword" method="post" action="{{ route('account.password.update') }}">
                @csrf
                <div class="row">
                    <div class="label">Mật khẩu hiện tại</div>
                    <div class="inline"><input type="password" name="current_password" required></div>
                </div>
                <div class="row">
                    <div class="label">Mật khẩu mới</div>
                    <div class="inline"><input type="password" name="password" required></div>
                </div>
                <div class="row">
                    <div class="label">Xác nhận mật khẩu</div>
                    <div class="inline"><input type="password" name="password_confirmation" required></div>
                </div>
                <div class="actions" style="margin-top:8px;"><button type="submit" class="btn primary">Lưu</button></div>
            </form>
            @if ($errors->any())
                <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                    <ul style="margin:0; padding-left:16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function(){
            var tabButtons = document.querySelectorAll('.tab');
            var tabInfo = document.getElementById('tab-info');
            var tabPassword = document.getElementById('tab-password');

            // Hàm kích hoạt tab và cuộn đến
            function activateTab(tabId) {
                tabButtons.forEach(function(btn) {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-tab') === tabId) {
                        btn.classList.add('active');
                    }
                });
                if (tabId === 'info') {
                    tabInfo.style.display = 'block';
                    tabPassword.style.display = 'none';
                    tabInfo.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    tabPassword.style.display = 'block';
                    tabInfo.style.display = 'none';
                    tabPassword.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            // Kích hoạt tab khi tải trang (mặc định là 'info')
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab') || 'info';
                activateTab(tab);
            });

            tabButtons.forEach(function(btn){
                btn.addEventListener('click', function(){
                    var tabId = btn.getAttribute('data-tab');
                    activateTab(tabId);
                    window.history.pushState({}, '', `?tab=${tabId}`);
                });
            });

            var formInfo = document.getElementById('formInfo');
            var formPassword = document.getElementById('formPassword');
            var btnEdit = document.getElementById('btnEdit');
            var btnCancel = document.getElementById('btnCancel');
            var avatarInput = document.getElementById('avatar_input');
            var avatarImg = document.getElementById('avatar_img');
            var originalAvatarSrc = avatarImg ? avatarImg.src : '';

            if (btnEdit) btnEdit.addEventListener('click', function(){
                formInfo.classList.add('editing');
            });
            if (btnCancel) btnCancel.addEventListener('click', function(){
                formInfo.classList.remove('editing');
                if (avatarImg) {
                    avatarImg.src = originalAvatarSrc;
                }
                if (avatarInput) {
                    avatarInput.value = '';
                }
            });
            if (avatarInput && avatarImg) {
                avatarInput.addEventListener('change', function(){
                    if (avatarInput.files && avatarInput.files[0]) {
                        var file = avatarInput.files[0];
                        var url = URL.createObjectURL(file);
                        avatarImg.src = url;
                    }
                });
            }

            // Xử lý submit form bằng AJAX
            [formInfo, formPassword].forEach(function(form) {
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault(); // Ngăn chặn hành vi submit mặc định
                        var formData = new FormData(form);
                        var url = form.getAttribute('action');

                        fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Hiển thị thông báo thành công
                                var successDiv = document.createElement('div');
                                successDiv.className = 'success-message';
                                successDiv.textContent = data.message;
                                form.parentElement.insertBefore(successDiv, form);

                                // Cập nhật giao diện nếu cần
                                if (data.name) document.getElementById('name_text').textContent = data.name;
                                if (data.avatar) document.getElementById('avatar_img').src = data.avatar;

                                // Reload trang tại route hiện tại
                                form.classList.remove('editing');
                                location.reload();
                            } else {
                                // Hiển thị lỗi
                                alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
                            }
                        })
                        .catch(error => {
                            alert('Có lỗi xảy ra khi gửi yêu cầu.');
                        });
                    });
                }
            });
        })();
    </script>
</body>
</html>