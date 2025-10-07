@extends('admin.admin')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid">
  <h1 class="mb-4">📊 Phân tích doanh thu</h1>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link {{ $period=='week'?'active':'' }}" href="{{ route('admin.analytics', ['period'=>'week']) }}">Theo tuần</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='month'?'active':'' }}" href="{{ route('admin.analytics', ['period'=>'month']) }}">Theo tháng</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='quarter'?'active':'' }}" href="{{ route('admin.analytics', ['period'=>'quarter']) }}">Theo quý</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='year'?'active':'' }}" href="{{ route('admin.analytics', ['period'=>'year']) }}">Theo năm</a></li>
  </ul>

  <!-- Bộ lọc -->
  <form method="GET" class="row g-3 mb-4 align-items-end">
    <input type="hidden" name="period" value="{{ $period }}">

    @if(in_array($period, ['week','month','quarter','year']))
      <div class="col-md-3">
        <label class="form-label fw-bold">Năm</label>
        <select name="year" class="form-select">
          @foreach($years as $y)
            <option value="{{ $y }}" {{ $y==$year?'selected':'' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
    @endif

    @if(in_array($period, ['week','month']))
      <div class="col-md-3">
        <label class="form-label fw-bold">Tháng</label>
        <select name="month" class="form-select">
          @for($m=1;$m<=12;$m++)
            <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>Tháng {{ $m }}</option>
          @endfor
        </select>
      </div>
    @endif

    @if($period=='week' && isset($weeksList))
      <div class="col-md-3">
        <label class="form-label fw-bold">Tuần</label>
        <select name="week" class="form-select">
          @foreach($weeksList as $i => $w)
            <option value="{{ $i }}" {{ $i==$week ? 'selected' : '' }}>
              Tuần {{ $i+1 }} ({{ $w['start']->format('d/m') }} - {{ $w['end']->format('d/m') }})
            </option>
          @endforeach
        </select>
      </div>
    @endif

    @if($period=='quarter')
      <div class="col-md-3">
        <label class="form-label fw-bold">Quý</label>
        <select name="quarter" class="form-select">
          @for($q=1;$q<=4;$q++)
            <option value="{{ $q }}" {{ $q==$quarter?'selected':'' }}>Quý {{ $q }}</option>
          @endfor
        </select>
      </div>
    @endif

    <div class="col-md-2 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary w-100">
        🔍 Xem thống kê
      </button>
    </div>
  </form>

  <!-- Biểu đồ -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="mb-3">{{ $label }}</h4>
      <canvas id="shopRevenueChart" height="100"></canvas>
    </div>
  </div>

  <!-- Bảng chi tiết -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">💰 Bảng doanh thu chi tiết</h5>
      <table class="table table-striped text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tên Shop</th>
            <th>Doanh thu (VNĐ)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index => $shop)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $shop->shop_name }}</td>
            <td>{{ number_format($shop->total, 0, ',', '.') }} đ</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- JS cập nhật tuần theo tháng -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const monthSelect = document.querySelector('select[name="month"]');
  const yearSelect = document.querySelector('select[name="year"]');
  const weekSelect = document.querySelector('select[name="week"]');

  if (monthSelect && weekSelect) {
    monthSelect.addEventListener('change', updateWeeks);
    yearSelect.addEventListener('change', updateWeeks);
  }

  function updateWeeks() {
    const year = yearSelect.value;
    const month = monthSelect.value;

    fetch(`/admin/analytics/weeks?year=${year}&month=${month}`)
      .then(res => res.json())
      .then(data => {
        weekSelect.innerHTML = '';
        data.forEach(w => {
          const opt = document.createElement('option');
          opt.value = w.index;
          opt.textContent = w.label;
          weekSelect.appendChild(opt);
        });
      })
      .catch(err => console.error(err));
  }
});
</script>

<!-- Chart -->
<script>
const ctx = document.getElementById('shopRevenueChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [{
      label: 'Doanh thu (VNĐ)',
      data: @json($values),
      backgroundColor: '#2563eb'
    }]
  },
  options: {
    plugins: {
      legend: { display: true },
      tooltip: {
        callbacks: {
          label: c => new Intl.NumberFormat('vi-VN').format(c.raw) + ' đ'
        }
      }
    },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('vi-VN').format(v) + ' đ' } }
    }
  }
});
</script>
@endsection
