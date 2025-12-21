<template id="tpl-account-personal">
    
    <div class="card">
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        @if(session('success_password'))
            <div class="success-message">{{ session('success_password') }}</div>
        @endif
        <div class="tabs">
            <button class="tab active" data-tab="info">Thông tin</button>
            <button class="tab" data-tab="password">Đổi mật khẩu</button>
        </div>

        <!-- Thông tin cá nhân -->
        <div id="tab-info" class="section">
            <!-- ✅ Đã chỉnh đường dẫn tuyệt đối -->
            <form id="formInfo" method="post" action="/account/personal" enctype="multipart/form-data">
                @csrf
                <div class="row" style="justify-content:space-between; margin-bottom:16px;">
                    <h3 style="margin:0;">Thông tin cá nhân</h3>
                    <button type="button" id="btnEdit" class="icon-btn" title="Chỉnh sửa">✎</button>
                </div>
                <div class="row">
    <div class="label">Avatar</div>
    <div>
        @php
            use Illuminate\Support\Str;

            $user = auth()->user();
            $avatarPath = $user->avatar_path;

            if ($avatarPath) {
                // Nếu là ảnh upload trong storage
                if (Str::startsWith($avatarPath, 'avatars/')) {
                    $avatarUrl = asset('storage/' . $avatarPath);
                } else {
                    // Nếu là đường dẫn tuyệt đối /Picture/... thì giữ nguyên
                    $avatarUrl = asset($avatarPath);
                }
            } else {
                // Nếu không có ảnh → dùng ảnh mặc định theo giới tính
                $avatarUrl = $user->gender === 'female'
                    ? asset('/Picture/Avata/avatar_macdinh_nu.jpg')
                    : asset('/Picture/Avata/avatar_macdinh_nam.jpg');
            }
        @endphp

        <img id="avatar_img" src="{{ $avatarUrl }}" class="avatar" alt="avatar">
        <input id="avatar_input" type="file" name="avatar" accept="image/*" class="edit-input" style="margin-left:12px;">
    </div>
</div>

                <div class="row">
                    <div class="label">Họ tên</div>
                    <div>
                        <span id="name_text" class="view-text">{{ auth()->user()->name }}</span>
                        <input class="edit-input" type="text" name="name" value="{{ auth()->user()->name }}">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Số điện thoại</div>
                    <div>
                        <span class="view-text">{{ auth()->user()->phone ?? '—' }}</span>
                        <input class="edit-input" type="text" name="phone" value="{{ auth()->user()->phone ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Email</div>
                    <div class="inline">{{ auth()->user()->email }}</div>
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

        <!-- Đổi mật khẩu -->
        <div id="tab-password" class="section" style="display:none;">
            <!-- ✅ Đã chỉnh đường dẫn tuyệt đối -->
            <form id="formPassword" method="post" action="/account/password">
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
                @if ($errors->any())
                    <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                        <ul style="margin:0; padding-left:16px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Thông tin shop -->
    <div class="card">
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        @php
use Illuminate\Support\Facades\Storage;
use App\Models\Shop;

// ✅ Lấy shop theo user_id (không dùng find)
$shop = Shop::where('user_id', auth()->id())->first();
@endphp

<form id="formShop" method="post" action="/account/shop" enctype="multipart/form-data">
    @csrf
    <div class="row" style="justify-content:space-between; margin-bottom:16px;">
        <h3 style="margin:0;">Thông tin shop</h3>
        <button type="button" id="btnEditShop" class="icon-btn" title="Chỉnh sửa">✎</button>
    </div>

    <div class="row">
        <div class="label">Logo</div>
        <div>
            @if($shop && $shop->logo_path && Storage::disk('public')->exists($shop->logo_path))
                {{-- ✅ Logo trong storage/public/shops --}}
                <img id="logo_img" src="{{ Storage::url($shop->logo_path) }}" class="logo" alt="logo">
            @else
                {{-- 🖼️ Logo mặc định --}}
                <img id="logo_img" src="{{ asset('Picture/Logo.png') }}" class="logo" alt="logo">
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
            <div class="row">
                <div class="label">Trạng thái</div>
                <div>
                    <span class="view-text" id="shop_status">{{ $shop->status ?? '—' }}</span>
                    <select class="edit-input" name="status" disabled style="display:none;">
                        <option value="active" {{ ($shop && $shop->status=='active') ? 'selected' : '' }}>Hoạt động</option>
                        <option value="closed" {{ ($shop && $shop->status=='closed') ? 'selected' : '' }}>Đóng cửa</option>
                    </select>
                </div>
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
                <button type="button" id="btnCancelShop" class="btn">Hủy</button>
            </div>
        </form>
    </div>
</template>



