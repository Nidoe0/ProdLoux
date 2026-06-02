@extends('layouts.app')
@section('title', 'Modération des avis')
@section('page-title', 'Modération des Avis')
@section('page-subtitle', 'Gérez les avis clients et signalements')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filters --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="?filter=" class="btn btn-sm {{ !request('filter') ? 'btn-success' : 'btn-outline-secondary' }}">Tous</a>
    <a href="?filter=pending" class="btn btn-sm {{ request('filter') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">En attente</a>
    <a href="?filter=flagged" class="btn btn-sm {{ request('filter') === 'flagged' ? 'btn-danger' : 'btn-outline-danger' }}">
        Signalés <span class="badge bg-danger ms-1">!</span>
    </a>
</div>

<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead style="background:#F1F8E9;">
    <tr>
        <th>#</th>
        <th>Client</th>
        <th>Produit</th>
        <th>Note</th>
        <th>Commentaire</th>
        <th>Statut</th>
        <th>Signalé</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
@forelse($reviews as $review)
<tr class="{{ $review->flagged ? 'table-danger' : '' }}">
    <td class="text-muted" style="font-size:0.8rem;">#{{ $review->id }}</td>
    <td style="font-size:0.85rem;">{{ $review->user?->name ?? '—' }}</td>
    <td style="font-size:0.85rem;">{{ Str::limit($review->product?->name, 25) }}</td>
    <td>
        @for($i=1; $i<=5; $i++)
        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} text-warning" style="font-size:0.75rem;"></i>
        @endfor
    </td>
    <td style="font-size:0.82rem;max-width:200px;">{{ Str::limit($review->body, 60) }}</td>
    <td>
        @php $colors=['pending'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
        <span class="badge bg-{{ $colors[$review->status] ?? 'secondary' }}">{{ ucfirst($review->status) }}</span>
    </td>
    <td>
        @if($review->flagged)
        <span class="badge bg-danger">🚩 {{ Str::limit($review->flag_reason, 30) }}</span>
        @else
        <span class="text-muted">—</span>
        @endif
    </td>
    <td style="font-size:0.78rem;" class="text-muted">{{ $review->created_at->format('d/m/Y') }}</td>
    <td>
        <div class="d-flex gap-1">
            @if($review->status !== 'approved')
            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                @csrf @method('PATCH')
                <button class="btn btn-sm btn-success py-0 px-2" title="Approuver">✓</button>
            </form>
            @endif
            @if($review->status !== 'rejected')
            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                @csrf @method('PATCH')
                <button class="btn btn-sm btn-warning py-0 px-2" title="Rejeter">✕</button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                  onsubmit="return confirm('Supprimer définitivement ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger py-0 px-2" title="Supprimer"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="9" class="text-center text-muted py-4">Aucun avis.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
