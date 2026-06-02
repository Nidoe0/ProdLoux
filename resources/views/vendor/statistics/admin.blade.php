@extends('layouts.app')
@section('title', 'Statistiques Admin')
@section('page-title', 'Statistiques Globales')
@section('page-subtitle', 'Vue d\'ensemble de la plateforme')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div class="btn-group" role="group">
        @foreach(['week'=>'7 jours','month'=>'Ce mois','year'=>'Cette année'] as $p => $label)
        <a href="?period={{ $p }}" class="btn btn-sm {{ $period === $p ? 'btn-success' : 'btn-outline-secondary' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('vendor.statistics.export') }}" class="d-flex gap-2 align-items-center">
        <input type="date" name="from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="form-control form-control-sm" style="width:140px;">
        <input type="date" name="to"   value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm" style="width:140px;">
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-2-5 col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #2E7D32!important;">
            <div class="text-muted small mb-1">Revenus vendeurs</div>
            <div class="fw-bold text-success" style="font-size:1.3rem;">{{ number_format($data['totalRevenue'], 0, ',', ' ') }} Ar</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #E65100!important;">
            <div class="text-muted small mb-1">Commission plateforme</div>
            <div class="fw-bold text-warning" style="font-size:1.3rem;">{{ number_format($data['totalCommission'], 0, ',', ' ') }} Ar</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #1565C0!important;">
            <div class="text-muted small mb-1">Commandes</div>
            <div class="fw-bold text-primary" style="font-size:1.3rem;">{{ $data['totalOrders'] }}</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Vendeurs</div>
            <div class="fw-bold" style="font-size:1.3rem;">{{ $data['totalSellers'] }}</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Produits</div>
            <div class="fw-bold" style="font-size:1.3rem;">{{ $data['totalProducts'] }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                    <i class="bi bi-graph-up text-success me-1"></i>Revenus 30 derniers jours
                </h6>
                <canvas id="revenueChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                    <i class="bi bi-trophy text-warning me-1"></i>Top Vendeurs
                </h6>
                @forelse($data['topSellers'] as $seller)
                <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;">{{ $seller->shop_name }}</div>
                        <small class="text-muted">{{ $seller->user->name ?? '?' }}</small>
                    </div>
                    <div class="text-success fw-semibold" style="font-size:.85rem;">
                        {{ number_format($seller->total_revenue ?? 0, 0, ',', ' ') }} Ar
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const revenueData = @json($data['revenueByDay']);
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(revenueData),
        datasets: [{
            label: 'Revenus (Ar)',
            data: Object.values(revenueData),
            backgroundColor: 'rgba(46,125,50,0.75)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endsection
