# Crystal Car Audio

Laravel storefront modernization for Crystal Car Audio Kenya. The app includes a responsive storefront, product search and category filtering, session cart, checkout with Cash on Delivery and eConfirm Escrow, signed webhook handling, a basic customer order confirmation page, and a gated store admin for order and catalogue management.

## Local setup

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --force
npm run build
php artisan serve
```

The app uses SQLite by default. For production, set `APP_URL` to the public HTTPS site URL and configure a durable database, session store, backups, mail transport, and HTTPS proxy settings.

## Import the legacy catalogue

The supplied SQL dump and the old website upload directory remain unchanged. The importer maps legacy category IDs, slugs, product fields, stock, prices, brand, descriptions, and media references into the new catalogue. Customer, order, invoice, payment, review, wishlist, coupon, brand, and tag rows are preserved with their original fields in the encrypted `legacy_archive_records` table. Legacy customer accounts are enabled for sign-in using their existing password hashes. Existing URL slugs are retained, including duplicates if they exist in the source data.

```powershell
php artisan legacy:import-catalog
```

By default, the command reads `../crystal3_crystal.sql`. Use `--sql="C:\path\to\dump.sql"` to choose another file. Product images are served from `public/uploads`; unmatched image references are retained in `legacy_image` and shown in the admin inventory for follow-up rather than rendered as broken links.

The 2,081 files from `Old-website/public/uploads` have been copied to `public/uploads`. The current dump references 35 product image filenames that are not present in the supplied upload directory. Those original filenames remain in the database and SQL source.

## eConfirm configuration

Set these server-side values in `.env`:

```dotenv
APP_URL=https://crystalcaraudio.com
ECONFIRM_API_V2_URL=https://api.econfirm.co.ke/api/v2
ECONFIRM_API_KEY=...
ECONFIRM_SELLER_EMAIL=...
ECONFIRM_RECEIVER_PHONE=2547XXXXXXXX
```

eConfirm requires a linked merchant business and the registered HTTPS origin to match `APP_URL`. The integration creates a local pending order, creates the escrow and hosted checkout session on the server, exposes the short-lived checkout secret only to that buyer's active session, and marks escrow funds held only after validating the signed webhook or reconciling the verified transaction status. The webhook endpoint is `/api/webhooks/econfirm/checkout`. Back up `APP_KEY` securely; the imported historical customer and order archive is encrypted with it.

## Store admin

Set `STORE_ADMIN_EMAIL` to the email of an existing user record. Sign in at `/admin/login`; users with any other email cannot access admin pages. Create the initial admin user through Laravel Tinker or an approved user provisioning workflow. The dashboard supports order/payment status updates, product creation, image upload, and in-page catalogue search.

## Legacy public routes carried forward

The current route set preserves storefront paths including `/`, `/home`, `/products`, `/products/{category}`, `/products/brand/{brand}`, `/product/{slug}`, `/product-quick-view/{slug}`, `/product-tags/{slug}`, search/filter URLs, `/shopping-cart`, legacy add/update/remove cart URLs, the checkout payment URLs, and the main policy/contact/portfolio endpoints. The informational pages currently use a temporary refreshed-content notice until their legacy CMS copy is migrated.
