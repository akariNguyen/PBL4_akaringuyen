@extends('admin.admin')

@push('styles')
<style>
  .nav-tabs .nav-link.active {
    background-color: #2563eb !important;
    color: #fff !important;
    border: none;
    border-radius: 6px 6px 0 0;
  }
  .nav-tabs .nav-link {
    color: #6b7280;
    font-weight: 500;
    padding: 8px 16px;
  }
  .table th {
    background: #f3f4f6 !important;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: .5px;
  }
  .status-btn {
    font-size: 15px;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 8px;
    min-width: 120px;
    text-align: center;
    border: none;
    cursor: pointer;
    text-transform: capitalize;
  }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Quản lý người dùng</h1>

    <!-- 3 card thống kê -->
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Khách hàng</h6>
                    <h3>{{ \App\Models\User::where('role','customer')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Tổng người dùng</h6>
                    <h3>{{ $userCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Người bán hàng</h6>
                    <h3>{{ \App\Models\User::where('role','seller')->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card quản lý -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Danh sách người dùng</h4>

                <!-- Bộ lọc ngày -->
                <form id="filterForm" method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
                    <input type="date" name="from" class="form-control" 
                           value="{{ request('from', $defaultFrom) }}">
                    <input type="date" name="to" class="form-control" 
                           value="{{ request('to', $defaultTo) }}">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    @if(request()->has('from') || request()->has('to'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>
            </div>

            <!-- ✅ Search -->
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control"
                       placeholder="🔍 Tìm kiếm theo tên hoặc email...">
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="userTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="customers-tab" data-bs-toggle="tab" href="#customers" role="tab">Khách hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sellers-tab" data-bs-toggle="tab" href="#sellers" role="tab">Người bán hàng</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Khách hàng -->
                <div class="tab-pane fade show active" id="customers" role="tabpanel">
                    <table class="table table-striped text-start align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Ngày tạo</th>
                                <th>Tình trạng</th>
                            </tr>
                        </thead>
                        <tbody id="customerTable">
                            @forelse($users->where('role','customer') as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn đổi tình trạng tài khoản?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                class="status-btn {{ $user->status == 'active' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                                {{ $user->status }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Không tìm thấy kết quả</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Người bán hàng -->
                <div class="tab-pane fade" id="sellers" role="tabpanel">
                    <table class="table table-striped text-start align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Ngày tạo</th>
                                <th>Tình trạng</th>
                            </tr>
                        </thead>
                        <tbody id="sellerTable">
                            @forelse($users->where('role','seller') as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn đổi tình trạng tài khoản?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                class="status-btn {{ $user->status == 'active' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                                {{ $user->status }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Không tìm thấy kết quả</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ✅ Script: Tìm kiếm tức thì không reload -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');

    const filterTable = (tableId, query) => {
        const rows = document.querySelectorAll(`#${tableId} tr`);
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    };

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        filterTable('customerTable', q);
        filterTable('sellerTable', q);
    });
});
</script>
@endsection
