@extends('layouts.app')
@section('title', 'Commandes')
@section('page-title', 'Commandes')
@section('page-subtitle', 'Suivez et gérez les commandes de vos clients')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
$statusColors = ['pending'=>'warning','confirmed'=>'success','delivered'=>'primary','cancelled'=>'danger'];
$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','delivered'=>'Livrée','cancelled'=>'Annulée'];
@endphp

{{-- Summary badges --}}
<div class="d-flex gap-2 flex-wrap mb-3">
    @foreach($statusLabels as $s => $label)
    @php $count = $orders->where('status', $s)->count(); @endphp
    <span class="badge bg-{{ $statusColors[$s] }} bg-opacity-15 text-{{ $statusColors[$s] }} border border-{{ $statusColors[$s] }}-subtle px-3 py-2" style="font-size:.78rem;">
        {{ $label }} : {{ $count }}
    </span>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead style="background:#F1F8E9;">
    <tr>
        <th>#</th>
        <th>Client</th>
        <th>Téléphone</th>
        <th>Produits</th>
        <th>Total</th>
        <th>Statut</th>
        <th>Payé via</th>
        <th>Date</th>
        <th>Changer statut</th>
    </tr>
</thead>
<tbody>
@forelse($orders as $order)
<tr>
    <td class="fw-semibold text-muted" style="font-size:.82rem;">#{{ $order->id }}</td>
    <td>
        <div style="font-size:.85rem;font-weight:600;">{{ $order->user->name ?? '—' }}</div>
        <small class="text-muted">{{ $order->user->email ?? '' }}</small>
    </td>
    <td style="font-size:.82rem;">{{ $order->phone ?? '—' }}</td>
    <td style="max-width:180px;">
        @foreach($order->items as $item)
        <small class="d-block text-muted" style="font-size:.75rem;">
            {{ Str::limit($item->product->name ?? '?', 22) }} × {{ $item->quantity }}
        </small>
        @endforeach
    </td>
    <td class="fw-bold text-success" style="font-size:.88rem;">{{ number_format($order->total, 0, ',', ' ') }} Ar</td>
    <td>
        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} rounded-pill">
            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
        </span>
    </td>
    <td>
        @if($order->stripe_payment_intent_id)
        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:.7rem;">
            <i class="bi bi-credit-card me-1"></i>Stripe
        </span>
        @else
        <span class="text-muted" style="font-size:.75rem;">—</span>
        @endif
    </td>
    <td style="font-size:.78rem;" class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
    <td>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" data-bs-toggle="dropdown">
                Statut
            </button>
            <ul class="dropdown-menu shadow-sm">
                @foreach(['pending','confirmed','delivered','cancelled'] as $s)
                @if($s !== $order->status)
                <li>
                    <form method="POST" action="{{ route('vendor.orders.status', [$order, $s]) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2" style="font-size:.85rem;">
                            <span class="badge bg-{{ $statusColors[$s] }}" style="width:10px;height:10px;padding:0;border-radius:50%;"></span>
                            {{ $statusLabels[$s] }}
                        </button>
                    </form>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        <i class="bi bi-receipt display-5 d-block mb-2 opacity-25"></i>
        Aucune commande pour le moment.
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endsection
