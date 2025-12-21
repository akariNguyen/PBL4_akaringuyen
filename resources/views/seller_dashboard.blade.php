@php
    $shop = \App\Models\Shop::where('user_id', auth()->id())->first();
    $categories = \App\Models\Category::orderBy('name')->get();
@endphp

@include('seller.layout')

    <!-- Hidden templates for center content -->
@include('seller.partials.orders')
@include('seller.partials.products')
@include('seller.partials.product-add', ['categories' => $categories])
@include('seller.partials.account')
@include('seller.partials.vouchers')
@include('seller.partials.voucher-add')
@include('seller.partials.revenue')
@include('seller.partials.scripts', ['shop' => $shop])

</body>
</html>
