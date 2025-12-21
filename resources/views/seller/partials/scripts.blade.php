@php
    $year = request()->get('year', now()->year);
    $sellerId = auth()->id();

    $sellerOrders = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
        $q->where('seller_id', $sellerId);
    })->whereYear('created_at', $year)
      ->where('status', 'completed')
      ->with(['items' => function($q) use ($sellerId) { $q->where('seller_id', $sellerId); }])
      ->get();

    $totalOrders  = $sellerOrders->count();
    $soldCount    = $sellerOrders->flatMap(fn($o) => $o->items ?? collect())->sum('quantity');
    $totalRevenue = $sellerOrders->sum('total_price');

    // Doanh thu theo tháng
    $currentMonth = ($year == now()->year) ? now()->month : 12;
    $revenues = [];
    for ($m = 1; $m <= $currentMonth; $m++) {
        $revenues[$m] = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereYear('created_at', $year)
        ->whereMonth('created_at', $m)
        ->where('status', 'completed')
        ->sum('total_price');
    }
    $revenuesChart = array_values($revenues ?: array_fill(0, $currentMonth, 0));
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const shopStatus = @json($shop ? $shop->status : 'active');
if (shopStatus === 'pending') {
    console.log("Shop đang chờ duyệt — bỏ qua JavaScript dashboard.");
} else {
(function(){
    // 🔄 Flag để track khi có đơn hàng mới hoàn thành
    let revenueNeedsRefresh = false;
    function show(viewId){
        var main = document.getElementById('mainContent');
        if(!main) {
            console.error('Main content element not found');
            return;
        }
        main.innerHTML = '';
        var tpl = document.getElementById(viewId);
        if (tpl) {
            main.appendChild(tpl.content.cloneNode(true));
        } else {
            console.error('Template not found:', viewId);
        }
    }
    function navigate(view){
        var map = {
            'orders_all': 'tpl-orders-all',
            'orders_bulk': 'tpl-orders-all',
            'shipping': 'tpl-orders-all',
            'products_all': 'tpl-products-all',
            'product_add': 'tpl-product-add',
            'account_personal': 'tpl-account-personal',
            'vouchers': 'tpl-vouchers',
            'voucher_add': 'tpl-voucher-add',
            'revenue_report': 'tpl-revenue-report',
        };
        show(map[view] || 'tpl-orders-all');

        history.pushState({}, '', `/seller/${view.replace('_', '/')}`);

        setTimeout(function(){
            if (view === 'product_add') bindCategory();
            if (view === 'products_all') bindProductsSearch();
            if (view === 'account_personal') bindAccountPersonal();
            if (view === 'account_personal') bindAccountShop();
            if (view === 'orders_all') bindOrders();
            if (view === 'vouchers') bindVouchers();
            if (view === 'voucher_add') bindVoucherAdd();
            if (view === 'revenue_report') bindRevenueChart();
        }, 0);
    }
    function getParameterByName(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    (function init(){
    console.log('Initializing dashboard...');
    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    const path = window.location.pathname;

    if (redirect === 'account_personal') {
        navigate('account_personal');
        return;
    }

    if (path.includes('/seller/vouchers')) {
        navigate('vouchers');
    } else if (path.includes('/seller/vouchers/create')) {
        navigate('voucher_add');
    } else if (path.includes('/seller/dashboard')) {
        navigate('orders_all');
    } else {
        navigate('orders_all');
    }
})();

    document.querySelectorAll('.sidebar a[data-view]').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            navigate(a.getAttribute('data-view'));
        });
    });
    function bindCategory(){
        var sel = document.getElementById('category_select');
        var otherWrap = document.getElementById('category_other_wrap');
        var other = document.getElementById('category_other');
        var hidden = document.getElementById('category_value');
        if(!sel || !hidden) {
            console.error('Category select or hidden input not found');
            return;
        }
        function sync(){
            if (sel.value === '__other__') {
                otherWrap.style.display = 'block';
                hidden.value = (other && other.value.trim()) ? other.value.trim() : '';
            } else {
                otherWrap.style.display = 'none';
                hidden.value = sel.value;
            }
        }
        sel.addEventListener('change', sync);
        if (other) other.addEventListener('input', sync);
        sync();
        // Bind submit for add product
        const addForm = document.querySelector('.product-form[action*="products/store"]');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                if (shopStatus === 'suspended') {
                    e.preventDefault();
                    alert('Shop đã bị đình chỉ. Không thể thêm sản phẩm mới.');
                    return false;
                }
            });
        }
    }
    function bindProductsSearch() {
    const input = document.getElementById('productsSearch');
    const grid = document.getElementById('productsGrid');
    const statusFilter = document.getElementById('statusFilter');
    if (!grid) return;

    // 🔍 Lọc theo tên sản phẩm
    if (input) {
        input.addEventListener('input', function () {
            const q = input.value.trim().toLowerCase();
            grid.querySelectorAll('.card.product-card').forEach(card => {
                const name = (card.querySelector('h3 span.view-text')?.textContent || '').toLowerCase();
                card.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }

    // 🏷️ Lọc theo trạng thái
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            const selected = statusFilter.value.trim().toLowerCase();
            grid.querySelectorAll('.card.product-card').forEach(card => {
                const status = (card.dataset.status || '').toLowerCase();
                card.style.display = (!selected || status === selected) ? '' : 'none';
            });
        });
    }
}

    function bindAccountPersonal(){
        var tabs = document.querySelectorAll('.tab');
        tabs.forEach(function(btn){
            btn.addEventListener('click', function(){
                tabs.forEach(function(b){ b.classList.remove('active'); });
                btn.classList.add('active');
                var tab = btn.getAttribute('data-tab');
                document.getElementById('tab-info').style.display = (tab==='info')?'block':'none';
                document.getElementById('tab-password').style.display = (tab==='password')?'block':'none';
            });
        });
        var form = document.getElementById('formInfo');
        if(form){
            var btnEdit = document.getElementById('btnEdit');
            var btnCancel = document.getElementById('btnCancel');
            if (btnEdit) {
    btnEdit.addEventListener('click', function (e) {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop đang bị đình chỉ — không thể chỉnh sửa thông tin cá nhân.');
            return;
        }
        form.classList.add('editing');
    });
}

            if(btnCancel) btnCancel.addEventListener('click', function(){ form.classList.remove('editing'); });
            var avatarInput = document.getElementById('avatar_input');
            var avatarImg = document.getElementById('avatar_img');
            if(avatarInput && avatarImg){
                avatarInput.addEventListener('change', function(){
                    if(this.files && this.files[0]){
                        var reader = new FileReader();
                        reader.onload = function(e){ avatarImg.src = e.target.result; }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(form);
                fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        form.classList.remove('editing');
                        var successDiv = document.createElement('div');
                        successDiv.className = 'success-message';
                        successDiv.textContent = data.message;
                        form.parentElement.insertBefore(successDiv, form);
                        if(data.name) document.getElementById('name_text').textContent = data.name;
                        if(data.avatar) document.getElementById('avatar_img').src = data.avatar;
                        window.location.href = '/seller/dashboard?redirect=account_personal';


                    } else {
                        alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    alert('Có lỗi xảy ra khi gửi yêu cầu.');
                });
            });
        }
        var formPassword = document.getElementById('formPassword');
        if (formPassword) {
            formPassword.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(formPassword);
                fetch(formPassword.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var successDiv = document.createElement('div');
                        successDiv.className = 'success-message';
                        successDiv.textContent = data.message;
                        formPassword.parentElement.insertBefore(successDiv, formPassword);
                       window.location.href = '/seller/dashboard?redirect=account_personal';

                    } else {
                        alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
                    }
                })
                .catch(error => {
                    console.error('Password form submission error:', error);
                    alert('Có lỗi xảy ra khi gửi yêu cầu.');
                });
            });
        }
    }
    function bindAccountShop() {
    var form = document.getElementById('formShop');
    if(!form) {
        console.error('Shop form not found');
        return;
    }

    var btnEdit = document.getElementById('btnEditShop');
    var btnCancel = document.getElementById('btnCancelShop');

    if (btnEdit) {
        btnEdit.addEventListener('click', function (e) {
            if (shopStatus === 'suspended') {
                e.preventDefault();
                alert('🚫 Shop của bạn đang bị đình chỉ — không thể chỉnh sửa thông tin.');
                return;
            }
            form.classList.add('editing');
        });
    }

    if(btnCancel) btnCancel.addEventListener('click', function(){ form.classList.remove('editing'); });

    var logoInput = document.getElementById('logo_input');
    var logoImg = document.getElementById('logo_img');

    if(logoInput && logoImg){
        logoInput.addEventListener('change', function(){
            if(this.files && this.files[0]){
                var reader = new FileReader();
                reader.onload = function(e){ logoImg.src = e.target.result; }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        var formData = new FormData(form);

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                form.classList.remove('editing');
                var successDiv = document.createElement('div');
                successDiv.className = 'success-message';
                successDiv.textContent = data.message;
                form.parentElement.insertBefore(successDiv, form);

                if(data.name) form.querySelector('.view-text').textContent = data.name;
                if(data.logo) document.getElementById('logo_img').src = data.logo;

                // 🔁 Reload về seller dashboard và mở lại tab account_personal
                window.location.href = '/seller/dashboard?redirect=account_personal';
            } else {
                alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
            }
        })
        .catch(error => {
            console.error('Shop form submission error:', error);
            alert('Có lỗi xảy ra khi gửi yêu cầu.');
        });
    });
}

    function renderOrders(status, fromDate = null, toDate = null) {
        console.log('Rendering orders for status:', status, 'from:', fromDate, 'to:', toDate);
        const list = document.getElementById('ordersList');
        if (!list) {
            console.error('Orders list element not found');
            alert('Lỗi: Không tìm thấy danh sách đơn hàng.');
            return;
        }
        list.innerHTML = '';
        console.log('Available orders:', allOrders); // Debug
        let startDate = fromDate ? new Date(fromDate) : null;
        let endDate = toDate ? new Date(toDate) : null;
        if (startDate) startDate.setHours(0, 0, 0, 0);
        if (endDate) endDate.setHours(23, 59, 59, 999);
        const filteredOrders = allOrders.filter(order => {
            if (!order) {
                console.warn('Invalid order:', order);
                return false;
            }
            if (status && order.status !== status) return false;
            const orderDate = new Date(order.created_at);
            if (startDate && orderDate < startDate) return false;
            if (endDate && orderDate > endDate) return false;
            return true;
        });
        console.log('Filtered orders:', filteredOrders); // Debug
        if (filteredOrders.length === 0) {
            list.innerHTML = '<p style="color:#6b7280; margin:0;">Chưa có đơn hàng phù hợp.</p>';
            return;
        }
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'separate';
        table.style.borderSpacing = '0 8px';
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr style="background:#f3f4f6; text-align:left;">
                <th style="padding:12px;">Mã đơn</th>
                <th style="padding:12px;">Khách hàng</th>
                <th style="padding:12px;">Tổng giá</th>
                <th style="padding:12px;">Sản phẩm</th>
                <th style="padding:12px;">Trạng thái</th>
                <th style="padding:12px;">Ngày tạo</th>
                <th style="padding:12px;">Hành động</th>
            </tr>
        `;
        table.appendChild(thead);
        const tbody = document.createElement('tbody');
        filteredOrders.forEach(order => {
            if (!order.id) {
                console.error('Invalid order data:', order);
                return;
            }
            const row = document.createElement('tr');
            row.style.background = '#fff';
            row.style.border = '1px solid #e5e7eb';
            row.style.borderRadius = '8px';
            let itemsHtml = '';
            if (order.items && Array.isArray(order.items)) {
                order.items.forEach(item => {
                    itemsHtml += `<div>${item.product_name || '—'} x ${item.quantity || 0} (${numberFormat(item.price || 0)} VND)</div>`;
                });
            } else {
                console.warn('No items for order:', order.id);
                itemsHtml = '<div>—</div>';
            }
            let actionHtml = '';
            let statusText = '';
            let statusColor = '#e5e7eb';
            switch (order.status) {
                case 'pending':
                    actionHtml = `
                        <button onclick="updateOrderStatus(${order.id}, this)" class="btn green" style="padding:6px 12px; border-radius:4px;" data-order-id="${order.id}" data-original-text="Xử lý đơn hàng">Xử lý đơn hàng</button>
                    `;
                    statusText = 'Chờ xử lý';
                    statusColor = '#f59e0b';
                    break;
                case 'shipped':
                    actionHtml = `
                        <button onclick="markDelivered(${order.id}, this)" class="btn orange" style="padding:6px 12px; border-radius:4px;" data-order-id="${order.id}" data-original-text="Giao hàng thành công">Giao hàng thành công</button>
                    `;
                    statusText = 'Đang giao';
                    statusColor = '#92400e';
                    break;
                case 'completed':
                    actionHtml = `
                        <span style="padding:4px 8px; border-radius:4px; background:#d1d5db; color:#6b7280; font-size:12px;">Hoàn thành</span>
                    `;
                    statusText = 'Hoàn thành';
                    statusColor = '#6b7280';
                    break;
                case 'cancelled':
                    actionHtml = `
                        <span style="padding:4px 8px; border-radius:4px; background:#fecaca; color:#dc2626; font-size:12px;">Đã hủy</span>
                    `;
                    statusText = 'Đã hủy';
                    statusColor = '#dc2626';
                    break;
                default:
                    console.warn('Unknown order status:', order.status);
                    statusText = 'Không xác định';
            }
            row.innerHTML = `
                <td style="padding:12px;">#${order.id}</td>
                <td style="padding:12px;">${order.user?.name || '—'}</td>
                <td style="padding:12px;">${numberFormat(order.total_price || 0)} VND</td>
                <td style="padding:12px;">${itemsHtml}</td>
                <td style="padding:12px;"><span style="padding:4px 8px; border-radius:4px; background:${statusColor}20; color:${statusColor};">${statusText}</span></td>
                <td style="padding:12px;">${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                <td style="padding:12px;">${actionHtml}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);
        list.appendChild(table);
    }
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function updateOrderStatus(orderId, button) {
        console.log('Clicked Xử lý đơn hàng for order:', orderId);
        if (!button) {
            console.error('Button element missing for order:', orderId);
            alert('Lỗi: Không tìm thấy nút xử lý.');
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Lỗi: Không tìm thấy CSRF token.');
            return;
        }
        button.disabled = true;
        button.textContent = 'Đang xử lý...';
        console.log('Sending AJAX to update order:', orderId, 'to status: shipped');
        fetch(`/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: 'shipped' })
        })
        .then(response => {
            console.log('Server response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data);
            if (data.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'success-message';
                successDiv.textContent = 'Đã chuyển trạng thái sang Đang giao!';
                successDiv.style.marginBottom = '12px';
                const list = document.getElementById('ordersList');
                if (list) {
                    list.parentElement.insertBefore(successDiv, list);
                }
                // Cập nhật trạng thái trong allOrders
                const order = allOrders.find(o => o.id === orderId);
                if (order) {
                    order.status = 'shipped';
                }
                
                // 🟩 Cập nhật số liệu thống kê (vẫn trong "Chưa giao")
                const undeliveredCount = allOrders.filter(o => ['pending', 'shipped'].includes(o.status)).length;
                document.querySelectorAll('.metric').forEach(metric => {
                    const title = metric.querySelector('h3')?.textContent?.trim();
                    if (title === 'Chưa giao') {
                        const val = metric.querySelector('.val');
                        if (val) val.textContent = undeliveredCount;
                    }
                });
                
                // Tự động chuyển sang tab "Đang giao" và làm mới danh sách
                const shippedTab = document.querySelector('.tab[data-tab="shipped"]');
                const tabs = document.querySelectorAll('.tab[data-tab]');
                tabs.forEach(t => t.classList.remove('active'));
                if (shippedTab) shippedTab.classList.add('active');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders('shipped', from, to);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái.'));
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            alert('Lỗi khi gửi yêu cầu: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Xử lý đơn hàng';
        });
    }
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit')) {
            const card = e.target.closest('.product-card');
            card.classList.add('editing');
        }
        if (e.target.classList.contains('btn-cancel')) {
            const card = e.target.closest('.product-card');
            card.classList.remove('editing');
        }
    });
    // Expose to global so inline onclick can access
    window.updateOrderStatus = updateOrderStatus;
    function markDelivered(orderId, button) {
        console.log('Clicked Giao hàng thành công for order:', orderId);
        if (!button) {
            alert('Lỗi: Không tìm thấy nút.');
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            alert('Lỗi: Thiếu CSRF token');
            return;
        }
        button.disabled = true;
        const original = button.textContent;
        button.textContent = 'Đang cập nhật...';
        fetch(`/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: 'completed' })
        })
        .then(r => {
            if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Cập nhật thất bại'); });
            return r.json();
        })
        .then(data => {
            if (data.success) {
                const order = allOrders.find(o => o.id === orderId);
                if (order) order.status = 'completed';

                // 🔄 Đánh dấu cần refresh revenue
                revenueNeedsRefresh = true;

                // 🟩 Đếm lại số liệu từ allOrders array
                const completedCount = allOrders.filter(o => o.status === 'completed').length;
                const undeliveredCount = allOrders.filter(o => ['pending', 'shipped'].includes(o.status)).length;
                
                document.querySelectorAll('.metric').forEach(metric => {
                    const title = metric.querySelector('h3')?.textContent?.trim();
                    const val = metric.querySelector('.val');
                    
                    if (!val) return;

                    if (title === 'Đã giao') {
                        val.textContent = completedCount;
                    } else if (title === 'Chưa giao') {
                        val.textContent = undeliveredCount;
                    }
                });

                // 🟦 Chuyển tab sang "Hoàn thành"
                const completedTab = document.querySelector('.tab[data-tab="completed"]');
                const tabs = document.querySelectorAll('.tab[data-tab]');
                tabs.forEach(t => t.classList.remove('active'));
                if (completedTab) completedTab.classList.add('active');

                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders('completed', from, to);
            } else {
                alert(data.message || 'Không thể cập nhật.');
            }
        })

        .catch(err => {
            console.error(err);
            alert(err.message);
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = original;
        });
    }
    window.markDelivered = markDelivered;
    
    // 📊 Hàm cập nhật số liệu thống kê từ allOrders
    function updateOrderStats() {
        if (!window.allOrders) return;
        
        const completedCount = allOrders.filter(o => o.status === 'completed').length;
        const undeliveredCount = allOrders.filter(o => ['pending', 'shipped'].includes(o.status)).length;
        const cancelledCount = allOrders.filter(o => o.status === 'cancelled').length;
        
        document.querySelectorAll('.metric').forEach(metric => {
            const title = metric.querySelector('h3')?.textContent?.trim();
            const val = metric.querySelector('.val');
            
            if (!val) return;
            
            if (title === 'Đã giao') {
                val.textContent = completedCount;
            } else if (title === 'Chưa giao') {
                val.textContent = undeliveredCount;
            } else if (title === 'Hủy') {
                val.textContent = cancelledCount;
            }
        });
    }
    
    function bindOrders() {
        console.log('Binding orders tab...');
        
        // 🔄 Cập nhật số liệu thống kê từ allOrders
        updateOrderStats();
        
        const tabs = document.querySelectorAll('.tab[data-tab]');
        let currentStatus = 'pending';
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                console.log('Tab clicked:', tab.getAttribute('data-tab'));
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentStatus = tab.getAttribute('data-tab');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders(currentStatus, from, to);
            });
        });
        const btnFilter = document.getElementById('btnFilter');
        if (btnFilter) {
            btnFilter.addEventListener('click', () => {
                console.log('Filter button clicked');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders(currentStatus, from, to);
            });
        }
        renderOrders('pending');
    }
    window.navigate = navigate;
    function bindVouchers() {
    // ✅ Lấy tbody thực trong DOM
    const tbody = document.querySelector('#mainContent tbody');
    if (!tbody) {
        console.warn('Không tìm thấy tbody trong giao diện voucher');
        return;
    }
    fetch('/seller/vouchers/json')
        .then(res => {
            if (!res.ok) throw new Error('Server trả lỗi ' + res.status);
            return res.json();
        })
        .then(vouchers => {
            tbody.innerHTML = '';
            if (!vouchers.length) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">
                        ⚠️ Chưa có voucher nào.
                    </td></tr>`;
                return;
            }
            vouchers.forEach(v => {
                const row = document.createElement('tr');
                row.dataset.id = v.id;
                // Chuẩn hoá ngày từ "2025-11-15T00:00:00Z" -> "2025-11-15"
                const expiry = (v.expiry_date || '').toString().split('T')[0] || '';
                row.innerHTML = `
                    <td style="padding:12px;">${v.code}</td>
                    <td style="padding:12px;">
                        <input type="number" value="${v.discount_amount}" style="width:100px;text-align:center;" disabled>
                    </td>
                    <td style="padding:12px;">
                        <input type="date" value="${expiry}" disabled>
                    </td>
                    <td style="padding:12px;">
                        <select disabled>
                            <option value="active" ${v.status === 'active' ? 'selected' : ''}>Hoạt động</option>
                            <option value="expired" ${v.status === 'expired' ? 'selected' : ''}>Hết hạn</option>
                        </select>
                    </td>
                    <td style="padding:12px;">
                        <button class="btn orange btn-edit-voucher">Sửa</button>
                        <button class="btn red btn-delete">Xóa</button>
                    </td>`;
                tbody.appendChild(row);
            });
            // 🔴 XÓA
tbody.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể xóa voucher.');
            return;
        }

        const row = btn.closest('tr');
        const id = row.dataset.id;
        if (!confirm('⚠️ Bạn có chắc muốn xóa voucher này không?')) return;

        try {
            const res = await fetch(`/seller/vouchers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            if (data.success) {
                alert('🗑️ Đã xóa voucher thành công!');
                bindVouchers(); // reload
            } else {
                alert('❌ ' + (data.message || 'Không thể xóa voucher!'));
            }
        } catch (err) {
            console.error('Lỗi khi xóa voucher:', err);
            alert('⚠️ Lỗi kết nối server!');
        }
    });
});

// 🟠 SỬA / 💾 LƯU
tbody.querySelectorAll('.btn-edit-voucher').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể chỉnh sửa voucher.');
            return;
        }

        const row = btn.closest('tr');
        const inputs = row.querySelectorAll('input, select');
        const id = row.dataset.id;

        if (btn.textContent === 'Sửa') {
            // 🔓 Cho phép chỉnh sửa
            inputs.forEach(i => i.disabled = false);
            btn.textContent = 'Lưu';
            btn.classList.remove('orange');
            btn.classList.add('green');
            return;
        }

        // 💾 Lưu thay đổi
        const discount = row.querySelector('input[type="number"]').value.trim();
        const expiry = row.querySelector('input[type="date"]').value;
        const status = row.querySelector('select').value;
        try {
            const res = await fetch(`/seller/vouchers/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    discount_amount: discount,
                    expiry_date: expiry,
                    status: status
                })
            });
            const data = await res.json();
            if (data.success) {
                alert('✅ Cập nhật voucher thành công!');
                inputs.forEach(i => i.disabled = true);
                btn.textContent = 'Sửa';
                btn.classList.remove('green');
                btn.classList.add('orange');
            } else {
                alert('❌ ' + (data.message || 'Không thể cập nhật voucher!'));
            }
        } catch (err) {
            console.error('Lỗi khi cập nhật voucher:', err);
            alert('⚠️ Lỗi kết nối server!');
        }
    });
});

        })
        .catch(err => {
            console.error('Lỗi khi tải voucher:', err);
            alert('⚠️ Không thể tải danh sách voucher.');
        });
}
function bindVoucherAdd() {
    const form = document.getElementById('voucherAddForm');
    if (!form) return;
    form.onsubmit = async e => {
        e.preventDefault();
        const data = new FormData(form);
        try {
            const res = await fetch('/seller/vouchers', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });
            if (!res.ok) throw new Error(`Server trả lỗi ${res.status}`);
            const d = await res.json();
            if (d.success) {
                alert('✅ Thêm voucher thành công!');
                window.location.href = '/seller/vouchers';
            } else {
                alert('❌ Có lỗi xảy ra khi thêm voucher!');
            }
        } catch (err) {
            console.error('❌ Lỗi khi thêm voucher:', err);
            alert('⚠️ Lỗi kết nối đến server hoặc phản hồi không hợp lệ!');
        }
    };
}
function bindRevenueChart() {
    const ctx = document.getElementById('chartRevenue');
    if (!ctx) return;

    const yearSelect = document.getElementById('yearSelect');
    let chartInstance = null;

    // ✅ Cập nhật thống kê tổng quan
    const updateStats = (totalOrders, soldCount, totalRevenue) => {
        const ordersDisplay = document.getElementById('totalOrdersDisplay');
        const soldDisplay = document.getElementById('soldCountDisplay');
        const revenueDisplay = document.getElementById('totalRevenueDisplay');

        if (ordersDisplay) ordersDisplay.textContent = totalOrders;
        if (soldDisplay) soldDisplay.textContent = soldCount;
        if (revenueDisplay) {
            revenueDisplay.textContent = new Intl.NumberFormat('vi-VN').format(totalRevenue) + ' ₫';
        }
    };

    // ✅ Hàm vẽ lại biểu đồ
    const renderChart = (revenues, year) => {
        if (chartInstance) chartInstance.destroy();

        const months = Array.from({length: revenues.length}, (_, i) => 'T' + (i + 1));

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: `Doanh thu ${year}`,
                    data: revenues,
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    barThickness: 'flex'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: 10 },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => new Intl.NumberFormat('vi-VN').format(v) + ' ₫'
                        }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
    datalabels: {
        anchor: 'end',          // điểm neo ở đỉnh cột
        align: 'end',           // căn phía trên đỉnh
        offset: -6,             // đẩy lên cao 6px để tách khỏi cột
        color: '#111',
        font: { weight: '600', size: 13 },
        formatter: v => v > 0 ? new Intl.NumberFormat('vi-VN').format(v) + ' ₫' : ''
    }
}

            },
            plugins: [ChartDataLabels]
        });
    };

    // 🔄 Hàm load dữ liệu mới từ server
    const loadFreshData = (year) => {
        const yearToUse = year || new Date().getFullYear();
        
        // Load cả biểu đồ và thống kê
        Promise.all([
            fetch(`/seller/revenue/json?year=${yearToUse}`).then(res => res.json()),
            fetch(`/seller/revenue/stats?year=${yearToUse}`).then(res => res.json())
        ])
        .then(([chartData, statsData]) => {
            renderChart(chartData.revenues || [], chartData.year);
            updateStats(
                statsData.totalOrders || 0,
                statsData.soldCount || 0,
                statsData.totalRevenue || 0
            );
            revenueNeedsRefresh = false; // Reset flag
        })
        .catch(err => {
            console.error('Error loading revenue data:', err);
            // Nếu lỗi, hiển thị dữ liệu trống thay vì crash
            renderChart([], yearToUse);
            updateStats(0, 0, 0);
        });
    };

    // ✅ Luôn load fresh data khi vào tab revenue
    console.log('📊 Loading revenue data...');
    loadFreshData();

    // ✅ Khi chọn năm khác → gọi API để lấy dữ liệu mới
    if (yearSelect && !yearSelect.dataset.bound) {
        yearSelect.dataset.bound = 'true';
        yearSelect.addEventListener('change', e => {
            const year = e.target.value;
            loadFreshData(year);
        });
    }
}

})();
// 🔒 KHÓA CHỨC NĂNG KHI SHOP ĐANG BỊ ĐÌNH CHỈ
if (shopStatus === 'suspended') {
    alert('⚠️ Shop của bạn hiện đang bị đình chỉ. Một số chức năng như thêm, sửa, xóa sản phẩm hoặc voucher đã bị giới hạn.');

    // 🧩 Hàm tiện ích chung
    const blockClick = (selector, message) => {
        document.querySelectorAll(selector).forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert(message);
            }, { capture: true });
        });
    };
    const blockSubmit = (selector, message) => {
        const form = document.querySelector(selector);
        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert(message);
            }, { capture: true });
        }
    };

    // 🛍️ 1️⃣ Khóa sản phẩm (thêm, sửa, xóa)
    const disableProducts = () => {
        blockClick('.btn-edit', '🚫 Shop đang bị đình chỉ — không thể chỉnh sửa sản phẩm.');
        blockClick('.btn-delete-product', '🚫 Shop đang bị đình chỉ — không thể xóa sản phẩm.');
        blockSubmit('.product-form[action*="products/store"]', '🚫 Shop đang bị đình chỉ — không thể thêm sản phẩm mới.');
    };

    // 🎫 2️⃣ Khóa voucher (thêm, sửa, xóa)
    const disableVouchers = () => {
        blockClick('.btn-edit-voucher', '🚫 Shop đang bị đình chỉ — không thể chỉnh sửa voucher.');
        blockClick('.btn-delete', '🚫 Shop đang bị đình chỉ — không thể xóa voucher.');
        blockSubmit('#voucherAddForm', '🚫 Shop đang bị đình chỉ — không thể thêm voucher mới.');
    };

    // 👤 3️⃣ Khóa tài khoản cá nhân
    const disableAccount = () => {
        blockSubmit('#formInfo', '🚫 Shop đang bị đình chỉ — không thể thay đổi thông tin cá nhân.');
        blockSubmit('#formPassword', '🚫 Shop đang bị đình chỉ — không thể đổi mật khẩu.');
    };

    // 🏪 4️⃣ Khóa form shop
    const disableShopForm = () => {
        blockSubmit('#formShop', '🚫 Shop đang bị đình chỉ — không thể cập nhật thông tin shop.');
    };

    // 🔁 5️⃣ Gọi lại mỗi khi chuyển tab (vì nội dung render động)
    const origNavigate = window.navigate;
    window.navigate = function(view) {
        origNavigate(view);
        setTimeout(() => {
            disableProducts();
            disableVouchers();
            disableAccount();
            disableShopForm();
        }, 800);
    };

    // 🚀 6️⃣ Gọi khi mới vào dashboard
    disableProducts();
    disableVouchers();
    disableAccount();
    disableShopForm();
}

// 🚫 Chặn riêng nút "Thêm sản phẩm" và "Thêm voucher" khi shop bị đình chỉ
document.addEventListener('click', function(e) {
    if (shopStatus === 'suspended') {
        if (e.target.closest('a[href*="/seller/products/create"]') || e.target.closest('button.add-product-btn')) {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể thêm sản phẩm mới.');
            return;
        }

        if (e.target.closest('a[href*="/seller/vouchers/create"]') || e.target.closest('button.add-voucher-btn')) {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể thêm voucher mới.');
            return;
        }
    }
});

// 🟦 Lọc theo trạng thái (chính xác theo data-status)
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const grid = document.getElementById('productsGrid');

    if (statusFilter && grid) {
        statusFilter.addEventListener('change', function() {
            const selected = statusFilter.value.trim().toLowerCase();

            grid.querySelectorAll('.card.product-card').forEach(function(card) {
                const status = (card.dataset.status || '').toLowerCase();

                if (!selected || status === selected) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
}
</script>
