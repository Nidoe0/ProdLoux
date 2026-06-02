@extends('layouts.app')
@section('title', 'Statistiques')
@section('page-title', 'Mes Statistiques')
@section('page-subtitle', 'Analyse de votre activité commerciale')

@section('content')

{{-- Period filter --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div class="btn-group" role="group">
        @foreach(['week'=>'7 jours','month'=>'Ce mois','year'=>'Cette année'] as $p => $label)
        <a href="?period={{ $p }}"
           class="btn btn-sm {{ $period === $p ? 'btn-success' : 'btn-outline-secondary' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    {{-- Export Excel --}}
    <form method="GET" action="{{ route('vendor.statistics.export') }}" class="d-flex gap-2 align-items-center">
        <input type="date" name="from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="form-control form-control-sm" style="width:140px;">
        <input type="date" name="to"   value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm" style="width:140px;">
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </button>
    </form>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Revenus</div>
            <div class="fw-bold fs-4 text-success">{{ number_format($data['revenue'], 0, ',', ' ') }} Ar</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Commandes</div>
            <div class="fw-bold fs-4 text-primary">{{ $data['totalOrders'] }}</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Retours / Annulés</div>
            <div class="fw-bold fs-4 text-danger">{{ $data['returns'] }}</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Stock bas</div>
            <div class="fw-bold fs-4 text-warning">{{ $data['lowStockProducts']->count() }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Revenue chart --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:.08em;">
                    <i class="bi bi-graph-up text-success me-1"></i>Revenus 30 derniers jours
                </h6>
                <canvas id="revenueChart" height="180"></canvas>
            </div>
        </div>
    </div>

    {{-- Top products --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:.08em;">
                    <i class="bi bi-trophy text-warning me-1"></i>Top 5 Produits
                </h6>
                @forelse($data['topProducts'] as $item)
                <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div class="fw-semibold" style="font-size:0.85rem;">{{ $item->product?->name ?? '—' }}</div>
                        <small class="text-muted">{{ $item->total_qty }} vendus</small>
                    </div>
                    <div class="text-success fw-semibold" style="font-size:0.85rem;">
                        {{ number_format($item->total_revenue, 0, ',', ' ') }} Ar
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Low stock alert --}}
@if($data['lowStockProducts']->count() > 0)
<div class="card border-0 shadow-sm mt-3 border-warning">
    <div class="card-body">
        <h6 class="fw-semibold text-warning mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i>Produits en stock bas</h6>
        <div class="row g-2">
            @foreach($data['lowStockProducts'] as $p)
            <div class="col-sm-3">
                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#FFF8E1;">
                    <span style="font-size:0.85rem;">{{ $p->name }}</span>
                    <span class="badge bg-warning text-dark">{{ $p->stock }} restants</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Order status doughnut --}}
<div class="row g-3 mt-0">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.72rem;letter-spacing:.08em;">
                    Statuts commandes
                </h6>
                <canvas id="statusChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const revenueData = @json($data['revenueByDay']);
const labels = Object.keys(revenueData);
const values = Object.values(revenueData);

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Revenus (Ar)',
            data: values,
            borderColor: '#2E7D32',
            backgroundColor: 'rgba(46,125,50,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

const statuses = @json($data['orderStatuses']);
const statusLabels = { pending: 'En attente', confirmed: 'Confirmées', delivered: 'Livrées', cancelled: 'Annulées' };
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(statuses).map(k => statusLabels[k] ?? k),
        datasets: [{
            data: Object.values(statuses),
            backgroundColor: ['#FFC107','#4CAF50','#2196F3','#f44336'],
            borderWidth: 0,
        }]
    },
    options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});
</script>
@endsection
