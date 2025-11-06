<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - E-Market</title>
    <style>
        body { font-family: Inter, Arial, sans-serif; margin:0; background:#fafafa; }

        /* NAVBAR / TOPBAR */
        .topbar {
            height:64px;
            border-bottom:1px solid #e5e7eb;
            display:flex;
            align-items:center;
            gap:10px;
            padding:0 24px;
            background:#fff;
            position:sticky;
            top:0;
            z-index:50;
        }
        .brand {
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:700;
            font-size:22px;
            color:#111827;
            white-space:nowrap;
        }
        .brand img {
            height:80px;
            width:auto;
            display:block;
        }

        /* CONTAINER + FORM STYLES */
        .container { max-width: 960px; margin: 16px auto; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; }
        .tabs { display:flex; gap:0; border-bottom:1px solid #e5e7eb; margin-bottom:12px; }
        .tab { padding:10px 14px; cursor:pointer; border:0; background:none; color:#6b7280; font-weight:600; }
        .tab.active { color:#111827; position:relative; }
        .tab.active::after { content:""; position:absolute; left:10px; right:10px; bottom:-1px; height:2px; background:#2563eb; }

        .section { padding-top:8px; }
        .row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
        .label { color:#6b7280; min-width:140px; }
        input[type="text"], input[type="password"] { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; }
        .btn { padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; }
        .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .icon-btn { border:none; background:none; cursor:pointer; color:#2e7d32; padding:10px; font-size:18px; border-radius:8px; }
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
             <img src="{{ asset('Picture/Logo.png') }}" alt="E-Market">
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
            <button onclick="window.location.href='{{ route('customer.dashboard') }}'"

                    style="background:none; border:none; color:#2563eb; font-weight:600; cursor:pointer; font-size:14px; display:flex; align-items:center; gap:6px;">
                ← Quay lại trang chủ
            </button>
        </div>


        <div class="tabs">
            <button class="tab active" data-tab="info" id="tab-info-btn">Thông tin cá nhân</button>
            <button class="tab" data-tab="password" id="tab-password-btn">Đổi mật khẩu</button>
            <button class="tab" data-tab="addresses" id="tab-addresses-btn">Sổ địa chỉ</button>
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
                            {{-- Nếu user đã upload avatar --}}
                            @php
                                $avatarPath = auth()->user()->avatar_path;
                                // Nếu ảnh nằm trong /storage (upload), hiển thị đúng URL công khai
                                if (Str::startsWith($avatarPath, 'avatars/') || Str::startsWith($avatarPath, 'users/') || Str::startsWith($avatarPath, 'public/')) {
                                    $avatarUrl = asset('storage/' . ltrim($avatarPath, 'public/'));
                                }
                                // Nếu lưu trong /Picture (ảnh mặc định)
                                elseif (Str::startsWith($avatarPath, '/Picture/')) {
                                    $avatarUrl = asset(ltrim($avatarPath, '/'));
                                }
                                // Trường hợp khác, fallback ảnh mặc định
                                else {
                                    $avatarUrl = asset('Picture/Avata/avatar_macdinh_nam.jpg');
                                }
                            @endphp

                            <img id="avatar_img" src="{{ $avatarUrl }}" alt="avatar" class="avatar">

                        @else
                            {{-- Nếu chưa có avatar --}}
                            @if(auth()->user()->gender === 'female')
                                <img id="avatar_img" src="{{ asset('Picture/Avata/avatar_macdinh_nu.jpg') }}" alt="avatar" class="avatar">
                            @else
                                <img id="avatar_img" src="{{ asset('Picture/Avata/avatar_macdinh_nam.jpg') }}" alt="avatar" class="avatar">
                            @endif
                        @endif


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

        <div id="tab-addresses" class="section" style="display:none;">
            <div class="row" style="justify-content:space-between; margin-bottom:8px;">
                <h3 style="margin:0;">Sổ địa chỉ</h3>
                <button class="btn primary" id="btnAddAddress">Thêm địa chỉ</button>
            </div>
            <div id="addressList" class="section"></div>

            <template id="tplAddressItem">
                <div class="row" style="align-items:flex-start; border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:8px;">
                    <div class="inline">
                        <div style="font-weight:600" data-field="full_name"></div>
                        <div class="muted" data-field="phone"></div>
                        <div data-field="address_line"></div>
                        <div class="muted"><span data-field="ward"></span> <span data-field="district"></span> <span data-field="city"></span></div>
                        <div class="muted" data-field="default"></div>
                    </div>
                    <div class="actions">
                        <button class="btn" data-action="set-default">Đặt mặc định</button>
                        <button class="btn" data-action="delete">Xóa</button>
                    </div>
                </div>
            </template>

            <dialog id="dlgAddress" style="border:none; border-radius:12px; padding:16px;">
                <h3 style="margin-top:0;">Thêm địa chỉ</h3>
                <form id="formAddress">
                    <div class="row"><div class="label">Họ tên</div><input type="text" name="full_name" required></div>
                    <div class="row"><div class="label">SĐT</div><input type="text" name="phone" required></div>
                    <div class="row"><div class="label">Địa chỉ</div><input type="text" name="address_line" required style="width:320px;"></div>
                    <div class="row"><div class="label">Phường/Xã</div><input type="text" name="ward"></div>
                    <div class="row"><div class="label">Quận/Huyện</div><input type="text" name="district"></div>
                    <div class="row"><div class="label">Tỉnh/TP</div><input type="text" name="city"></div>
                    <div class="row"><label><input type="checkbox" name="is_default"> Đặt làm mặc định</label></div>
                    <div class="actions"><button type="submit" class="btn primary">Lưu</button> <button type="button" id="btnCloseDlg" class="btn">Đóng</button></div>
                </form>
            </dialog>
        </div>
    </div>

    <script>
        (function(){
            var tabButtons = document.querySelectorAll('.tab');
            var tabInfo = document.getElementById('tab-info');
            var tabPassword = document.getElementById('tab-password');
            var tabAddresses = document.getElementById('tab-addresses');

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
                    tabAddresses.style.display = 'none';
                    tabInfo.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else if (tabId === 'password') {
                    tabPassword.style.display = 'block';
                    tabInfo.style.display = 'none';
                    tabAddresses.style.display = 'none';
                    tabPassword.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    tabAddresses.style.display = 'block';
                    tabInfo.style.display = 'none';
                    tabPassword.style.display = 'none';
                    loadAddresses();
                    tabAddresses.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            // Address book handlers
            const addressList = document.getElementById('addressList');
            const tplAddressItem = document.getElementById('tplAddressItem');
            const dlgAddress = document.getElementById('dlgAddress');
            const btnAddAddress = document.getElementById('btnAddAddress');
            const btnCloseDlg = document.getElementById('btnCloseDlg');
            const formAddress = document.getElementById('formAddress');

            function renderAddresses(addresses){
                addressList.innerHTML = '';
                if(!addresses || addresses.length === 0){
                    addressList.innerHTML = '<p class="muted">Chưa có địa chỉ nào.</p>';
                    return;
                }
                addresses.forEach(function(a){
                    const node = tplAddressItem.content.cloneNode(true);
                    node.querySelector('[data-field="full_name"]').textContent = a.full_name;
                    node.querySelector('[data-field="phone"]').textContent = a.phone;
                    node.querySelector('[data-field="address_line"]').textContent = a.address_line;
                    node.querySelector('[data-field="ward"]').textContent = a.ward || '';
                    node.querySelector('[data-field="district"]').textContent = a.district || '';
                    node.querySelector('[data-field="city"]').textContent = a.city || '';
                    node.querySelector('[data-field="default"]').textContent = a.is_default ? 'Mặc định' : '';
                    const el = node.firstElementChild;
                    el.querySelector('[data-action="set-default"]').addEventListener('click', function(){ setDefaultAddress(a.id); });
                    el.querySelector('[data-action="delete"]').addEventListener('click', function(){ deleteAddress(a.id); });
                    addressList.appendChild(node);
                });
            }

            function loadAddresses(){
                fetch('{{ route('account.addresses.list') }}')
                    .then(r=>r.json())
                    .then(d=>{ if(d.success) renderAddresses(d.addresses); });
            }

            function setDefaultAddress(id){
                fetch(`/account/addresses/${id}/default`, { method:'POST', headers:{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(r=>r.json()).then(d=>{ if(d.success) loadAddresses(); else alert('Không thể đặt mặc định'); });
            }
            function deleteAddress(id){
                if(!confirm('Xóa địa chỉ này?')) return;
                fetch(`/account/addresses/${id}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(r=>r.json()).then(d=>{ if(d.success) loadAddresses(); else alert('Không thể xóa'); });
            }

            if(btnAddAddress) btnAddAddress.addEventListener('click', function(){ dlgAddress.showModal(); });
            if(btnCloseDlg) btnCloseDlg.addEventListener('click', function(){ dlgAddress.close(); });
            if(formAddress){
                formAddress.addEventListener('submit', function(e){
                    e.preventDefault();
                    const fd = new FormData(formAddress);
                    const submitBtn = formAddress.querySelector('button[type="submit"]');
                    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Đang lưu...'; }
                    fetch('{{ route('account.addresses.add') }}', {
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    })
                    .then(r=>{
                        if(!r.ok){ return r.json().then(e=>{ throw new Error(e.message || 'Lưu thất bại'); }); }
                        return r.json();
                    })
                    .then(d=>{ 
                        if(d.success){ 
                            dlgAddress.close(); 
                            formAddress.reset(); 
                            loadAddresses(); 
                        } else { 
                            alert('Không thể lưu địa chỉ'); 
                        } 
                    })
                    .catch(err=>{ alert(err.message); })
                    .finally(()=>{ if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Lưu'; } });
                });
            }
        })();
    </script>
</body>
</html>