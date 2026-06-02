<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Seller;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private ?Seller $seller,
        private string $from,
        private string $to
    ) {}

    public function query()
    {
        $query = Order::with(['user', 'items.product'])->whereBetween('created_at', [$this->from, $this->to.' 23:59:59']);

        if ($this->seller) {
            $productIds = $this->seller->products()->pluck('id');
            $query->whereHas('items', fn($q) => $q->whereIn('product_id', $productIds));
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            '#Commande', 'Client', 'Email', 'Téléphone',
            'Adresse livraison', 'Total (Ar)', 'Statut',
            'Produits', 'Date',
        ];
    }

    public function map($order): array
    {
        $products = $order->items->map(fn($i) => "{$i->product?->name} x{$i->quantity}")->implode(', ');

        return [
            $order->id,
            $order->user?->name ?? '—',
            $order->user?->email ?? '—',
            $order->phone ?? '—',
            $order->delivery_address ?? '—',
            number_format($order->total, 0, ',', ' '),
            ucfirst($order->status),
            $products,
            $order->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B5E20']],
            ],
        ];
    }

    public function title(): string { return 'Commandes'; }
}
