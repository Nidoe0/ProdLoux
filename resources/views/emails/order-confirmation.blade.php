<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmation de commande</title>
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin:0; padding:0; }
  .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  .header { background: linear-gradient(135deg, #1B5E20, #2E7D32); padding: 32px 40px; text-align: center; }
  .header h1 { color: #FFB300; margin: 0; font-size: 1.6rem; }
  .header p { color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 0.95rem; }
  .body { padding: 32px 40px; }
  .greeting { font-size: 1rem; color: #333; margin-bottom: 20px; }
  .order-box { background: #F1F8E9; border: 1px solid #C8E6C9; border-radius: 10px; padding: 20px; margin: 20px 0; }
  .order-box h3 { margin: 0 0 12px; color: #1B5E20; font-size: 1rem; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; color: #555; font-size: 0.8rem; text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid #E8F5E9; }
  td { padding: 10px 0; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; color: #333; }
  .total-row td { font-weight: 700; color: #1B5E20; border-bottom: none; }
  .status-badge { display: inline-block; background: #FFF3E0; color: #E65100; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
  .footer { background: #F9FBE7; padding: 20px 40px; text-align: center; font-size: 0.8rem; color: #888; }
  .btn { display: inline-block; background: #2E7D32; color: white; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 16px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🛍️ Tsena Mora</h1>
    <p>Votre commande a bien été reçue !</p>
  </div>
  <div class="body">
    <p class="greeting">Bonjour <strong>{{ $order->user?->name }}</strong>,</p>
    <p>Merci pour votre commande. Voici le récapitulatif :</p>

    <div class="order-box">
      <h3>Commande #{{ $order->id }} &nbsp; <span class="status-badge">En attente</span></h3>
      <table>
        <thead>
          <tr>
            <th>Produit</th>
            <th>Qté</th>
            <th style="text-align:right">Prix</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $item)
          <tr>
            <td>{{ $item->product?->name ?? '—' }}</td>
            <td>{{ $item->quantity }}</td>
            <td style="text-align:right">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} Ar</td>
          </tr>
          @endforeach
          <tr class="total-row">
            <td colspan="2">Total</td>
            <td style="text-align:right">{{ number_format($order->total, 0, ',', ' ') }} Ar</td>
          </tr>
        </tbody>
      </table>
    </div>

    @if($order->delivery_address)
    <p><strong>📍 Adresse de livraison :</strong> {{ $order->delivery_address }}</p>
    @endif

    <p style="color:#555;font-size:0.9rem;">Votre vendeur va traiter votre commande dans les plus brefs délais. Vous serez notifié dès qu'elle sera confirmée.</p>

    <div style="text-align:center">
      <a href="{{ url('/') }}" class="btn">Voir mes commandes</a>
    </div>
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} Tsena Mora — Marketplace Malgache</p>
    <p>Antananarivo, Madagascar</p>
  </div>
</div>
</body>
</html>
