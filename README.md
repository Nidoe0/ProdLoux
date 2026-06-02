# 🛍️ Tsena Mora — Marketplace Laravel Complète

> Projet Laravel 12 complet — 3 exercices progressifs, tous les fichiers inclus.

---

## ⚡ Installation (5 minutes)

```bash
# 1. Extraire
tar -xzf Laravele_complet_final.tar.gz && cd Laravele

# 2. Dépendances PHP
composer install

# 3. Environnement
cp .env.example .env
php artisan key:generate

# 4. Base de données SQLite + seed
touch database/database.sqlite
php artisan migrate --seed

# 5. Lien storage (images Spatie)
php artisan storage:link

# 6. Démarrer
php artisan serve
```

**→ http://localhost:8000**

### Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | `kotonirina45@gmail.com` | `Koto2004@nidot` |
| Vendeur 1-5 | `vendeur1@test.com` … | `password` |
| Acheteur 1-3 | `acheteur1@test.com` … | `password` |

---

## 📚 Exercices — Ce qui a été construit

### EXO 01 — Base du marketplace

#### 1. Migrations (7 tables)
| Fichier | Table | Colonnes clés |
|---------|-------|---------------|
| `0001_01_01_000000` | `users` | `name, email, password, role (buyer\|seller\|admin)` |
| `2026_05_26_081230` | `sellers` | `user_id, shop_name, latitude, longitude, stripe_*` |
| `2026_05_26_081231` | `categories` | `name` |
| `2026_05_26_081232` | `products` | `seller_id, category_id, price, stock, latitude, longitude` |
| `2026_05_26_081233` | `carts` | `user_id, product_id, quantity` |
| `2026_05_26_081234` | `orders` | `user_id, total, status, stripe_payment_intent_id, delivery_address, phone` |
| `2026_05_26_081235` | `order_items` | `order_id, product_id, quantity, price` |

#### 2. Auth Sanctum + Rôles
- **Middleware** : `app/Http/Middleware/CheckRole.php` → `role:buyer`, `role:seller,admin`
- **Enregistré** dans `bootstrap/app.php`
- **3 rôles** : `buyer` (API seulement), `seller` (dashboard + API), `admin` (tout)

```bash
POST /api/register    → { name, email, password, role, shop_name? }
POST /api/login       → { token, user }
POST /api/logout   🔒
GET  /api/me       🔒
```

#### 3. GET /api/products avec filtres
```bash
GET /api/products                                      # tous (en stock)
GET /api/products?category_id=2                        # par catégorie
GET /api/products?latitude=-18.91&longitude=47.53      # GPS 10km par défaut
GET /api/products?latitude=-18.91&longitude=47.53&radius=3  # rayon custom
```
→ Formule Haversine SQL, résultats triés par distance, champ `distance` en km.

#### 4. Seeders
```bash
php artisan db:seed
```
- 1 admin, 5 vendeurs, 3 acheteurs
- 5 catégories (Fruits, Légumes, Artisanat, Épices, Boissons)
- **30 produits géolocalisés** (6 par vendeur, zone Antananarivo)
- Commandes + paiements + avis d'exemple

#### 5. Dashboard Blade (base)
→ `http://localhost:8000/login` puis `http://localhost:8000/vendor/dashboard`
- KPIs : produits, commandes, revenus
- Graphique donut Chart.js
- Dernières commandes

---

### EXO 02 — Fonctionnalités e-commerce

#### 1. CRUD produits avec images multiples (Spatie MediaLibrary)
- **Controller** : `app/Http/Controllers/Vendor/ProductController.php`
- **Model** : `app/Models/Product.php` → `implements HasMedia`
- Upload jusqu'à **5 images** (JPEG/PNG/WebP)
- Conversions auto : `thumb` (200×200) + `medium` (600×600)
- Suppression individuelle d'images en édition

```
app/Http/Controllers/
  Vendor/ProductController.php    ← Blade CRUD
  Api/VendorProductController.php ← API CRUD (seller + admin)
```

#### 2 & 3. POST /api/orders — stock vérifié + décrémenté
```bash
POST /api/orders  🔒 buyer
{ "delivery_address": "Rue 5, Tana", "phone": "034..." }
```
- Vérification stock avant création (ValidationException si insuffisant)
- Décrémentation atomique en transaction DB
- Notification vendeur (DB + mail) si stock bas post-commande

**Code** : `app/Services/OrderService.php::createFromCart()`

#### 4. Dashboard : commandes, stock, revenus
- `vendor/orders` : tableau de bord commandes avec changement de statut
- `vendor/dashboard` : KPIs temps réel
- `vendor/statistics` : graphiques + top produits + alertes stock bas

#### 5. Email confirmation (Mailable)
- **Classe** : `app/Mail/OrderConfirmationMail.php`
- **Vue** : `resources/views/emails/order-confirmation.blade.php`
- Envoyé automatiquement à la création de commande

---

### EXO 03 — Fonctionnalités avancées

#### 1. Stripe + Commission plateforme
```bash
# 1. Créer PaymentIntent
POST /api/orders/{order}/payment-intent  🔒 buyer
→ { client_secret, payment_intent_id }

# 2. Confirmer après paiement Stripe.js
POST /api/orders/{order}/confirm-payment 🔒 buyer
{ "payment_intent_id": "pi_xxx" }
→ Split automatique : 10% plateforme, 90% vendeur

# 3. Webhook Stripe (automatique)
POST /api/stripe/webhook   (no auth, signature vérifiée)
```
**Config** `.env` : `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `PLATFORM_COMMISSION=10`

Tables : `payments` (seller_id, commission_amount, seller_amount, status)

#### 2. Avis avec modération admin
```bash
POST /api/products/{product}/reviews  🔒   { rating:1-5, body }
POST /api/reviews/{review}/flag       🔒   { reason }

# Admin Blade
GET  /admin/reviews              # liste avec filtres pending/flagged
PATCH /admin/reviews/{id}/approve
PATCH /admin/reviews/{id}/reject
DELETE /admin/reviews/{id}
```

#### 3. Alertes stock bas (notifications push vendeur)
- **Notification** : `app/Notifications/LowStockNotification.php`
- **Canaux** : `database` + `mail`
- **Déclencheur** : automatique si stock ≤ seuil après commande
- **Seuil** : configurable via `LOW_STOCK_THRESHOLD=5` dans `.env`
- **Cloche** : topbar Blade avec badge non-lus

```bash
GET  /api/notifications/unread-count  🔒
GET  /api/notifications               🔒
POST /api/notifications/{id}/read     🔒
POST /api/notifications/read-all      🔒
```

#### 4. Statistiques vendeur
`GET /vendor/statistics?period=week|month|year`

- **CA par jour** (graphique ligne)
- **Top 5 produits** par quantité vendue
- **Statuts commandes** (graphique donut)
- **Produits stock bas** (alerte visuelle)
- **Retours/annulations**
- **Vue admin** séparée : commission totale, top vendeurs

**Code** : `app/Services/StatisticsService.php`

#### 5. Export Excel commandes
```bash
GET /vendor/statistics/export?from=2026-01-01&to=2026-05-31
```
→ Télécharge `.xlsx` formaté (vert Tsena Mora, colonnes auto-sized)
**Code** : `app/Exports/OrdersExport.php`

#### 6. Tests PHPUnit (55 tests)
```bash
php artisan test
# ou
./vendor/bin/phpunit --colors=always
```

| Suite | Fichier | Nb |
|-------|---------|-----|
| Auth API | `tests/Feature/AuthTest.php` | 8 |
| Catalogue produits | `tests/Feature/ProductApiTest.php` | 6 |
| Commandes | `tests/Feature/OrderTest.php` | 8 |
| Panier | `tests/Feature/CartTest.php` | 7 |
| Avis | `tests/Feature/ReviewTest.php` | 9 |
| CRUD vendeur | `tests/Feature/VendorProductTest.php` | 7 |
| Notifications | `tests/Feature/NotificationTest.php` | 6 |
| OrderService (Unit) | `tests/Unit/OrderServiceTest.php` | 7 |
| StatisticsService (Unit) | `tests/Unit/StatisticsServiceTest.php` | 7 |
| **Total** | | **65** |

---

## 🗂️ Architecture des fichiers

```
app/
├── Exports/
│   └── OrdersExport.php              ← Excel export (Maatwebsite)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/ReviewController.php ← Modération avis
│   │   ├── Api/
│   │   │   ├── CartController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php  ← GET /api/products + GPS
│   │   │   ├── ReviewController.php
│   │   │   ├── StripeWebhookController.php
│   │   │   └── VendorProductController.php
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   ├── ForgotPasswordController.php
│   │   │   └── ResetPasswordController.php
│   │   ├── AuthController.php         ← register/login/logout/me
│   │   └── Vendor/
│   │       ├── DashboardController.php
│   │       ├── OrderController.php
│   │       ├── ProductController.php  ← CRUD + Spatie
│   │       └── StatisticsController.php
│   └── Middleware/
│       └── CheckRole.php              ← role:buyer|seller|admin
├── Mail/
│   └── OrderConfirmationMail.php
├── Models/
│   ├── Cart.php
│   ├── Category.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Product.php                    ← HasMedia (Spatie)
│   ├── Review.php
│   ├── Seller.php
│   └── User.php                       ← HasApiTokens, Notifiable
├── Notifications/
│   ├── LowStockNotification.php       ← DB + Mail
│   └── NewOrderNotification.php       ← DB + Mail
└── Services/
    ├── OrderService.php               ← createFromCart(), createPayments()
    ├── StatisticsService.php          ← forSeller(), admin()
    └── StripeService.php              ← PaymentIntent, Connect, Transfer
database/
├── factories/     ← User, Seller, Category, Product, Order
├── migrations/    ← 14 migrations (toutes fusionnées proprement)
└── seeders/
    └── DatabaseSeeder.php
resources/views/
├── admin/reviews/index.blade.php
├── auth/login.blade.php
├── emails/order-confirmation.blade.php
├── layouts/app.blade.php              ← Sidebar + notifications
└── vendor/
    ├── dashboard/index.blade.php
    ├── orders/index.blade.php
    ├── products/{index,create,edit}.blade.php
    └── statistics/{index,admin}.blade.php
```

---

## 🔑 Toutes les routes

### API (`/api`)
```
POST   /register          register buyer ou seller
POST   /login             obtenir token
POST   /logout         🔒 révoquer token
GET    /me             🔒 profil
GET    /categories        liste catégories
GET    /products          catalogue (filtres GPS + catégorie)
GET    /products/{id}     détail produit + avis
GET    /products/{id}/reviews  avis approuvés
POST   /products/{id}/reviews  🔒 soumettre avis
POST   /reviews/{id}/flag      🔒 signaler avis
GET    /notifications          🔒 liste
GET    /notifications/unread-count 🔒
POST   /notifications/{id}/read    🔒
POST   /notifications/read-all     🔒
GET    /cart              🔒 buyer
POST   /cart              🔒 buyer  { product_id, quantity }
PUT    /cart/{id}         🔒 buyer  { quantity }
DELETE /cart/{id}         🔒 buyer
DELETE /cart              🔒 buyer (vider)
GET    /orders            🔒 buyer  historique
POST   /orders            🔒 buyer  { delivery_address?, phone? }
POST   /orders/{id}/payment-intent   🔒 buyer
POST   /orders/{id}/confirm-payment  🔒 buyer
GET    /vendor/products   🔒 seller/admin
POST   /vendor/products   🔒 seller/admin (multipart)
GET    /vendor/products/{id}     🔒 seller/admin
PUT    /vendor/products/{id}     🔒 seller/admin
DELETE /vendor/products/{id}     🔒 seller/admin
POST   /stripe/webhook    (Stripe signature)
```

### Web (`/`)
```
GET    /login
POST   /login
POST   /logout
GET    /forgot-password
POST   /forgot-password
GET    /vendor/dashboard        🔒 seller/admin
GET    /vendor/products         🔒 seller/admin
GET    /vendor/products/create  🔒 seller/admin
POST   /vendor/products         🔒 seller/admin
GET    /vendor/products/{id}/edit    🔒
PUT    /vendor/products/{id}         🔒
DELETE /vendor/products/{id}         🔒
GET    /vendor/orders               🔒
PATCH  /vendor/orders/{id}/status/{status} 🔒
GET    /vendor/statistics           🔒
GET    /vendor/statistics/export    🔒
GET    /admin/reviews               🔒 admin
PATCH  /admin/reviews/{id}/approve  🔒 admin
PATCH  /admin/reviews/{id}/reject   🔒 admin
DELETE /admin/reviews/{id}          🔒 admin
```

---

## 🛠️ Commandes utiles

```bash
php artisan migrate:fresh --seed    # Repart de zéro
php artisan test                    # Lance les 65 tests
php artisan test --filter=AuthTest  # Un seul fichier
php artisan route:list              # Toutes les routes
php artisan storage:link            # Lien public/storage
php artisan tinker                  # REPL interactif
```
# ProdLoux
