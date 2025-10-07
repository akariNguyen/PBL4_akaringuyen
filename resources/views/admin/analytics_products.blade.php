@extends('admin.admin')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid">
  <h1 class="mb-4">📈 Phân tích sản phẩm bán chạy</h1>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link {{ $period=='week'?'active':'' }}" href="{{ route('admin.analytics.products', ['period'=>'week']) }}">Theo tuần</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='month'?'active':'' }}" href="{{ route('admin.analytics.products', ['period'=>'month']) }}">Theo tháng</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='quarter'?'active':'' }}" href="{{ route('admin.analytics.products', ['period'=>'quarter']) }}">Theo quý</a></li>
    <li class="nav-item"><a class="nav-link {{ $period=='year'?'active':'' }}" href="{{ route('admin.analytics.products', ['period'=>'year']) }}">Theo năm</a></li>
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
            <option value="{{ $i }}" {{ $i==$week?'selected':'' }}>
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
      <canvas id="productChart" height="100"></canvas>
    </div>
  </div>

  <!-- Bảng chi tiết -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">📦 Top sản phẩm bán chạy</h5>
      <table class="table table-striped text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tên sản phẩm</th>
            <th>Số lượng bán</th>
            <th>Doanh thu (VNĐ)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index => $item)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->sold_qty }}</td>
            <td>{{ number_format($item->revenue, 0, ',', '.') }} đ</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script>
const ctx = document.getElementById('productChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [{
      label: 'Số lượng bán',
      data: @json($values),
      backgroundColor: '#10b981'
    }]
  },
  options: {
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: c => `${c.formattedValue} sản phẩm`
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: v => v.toLocaleString('vi-VN')
        }
      }
    }
  }
});
</script>
@endsection
