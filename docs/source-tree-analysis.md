# Source Tree Analysis: DOPA Check

_An annotated directory tree highlighting the project's architecture and key files._

```
dopacheck.com.br/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Web & API logic (Stripe, Challenges, Teams)
│   │   └── Middleware/     # Standard Laravel + Jetstream/Inertia middleware
│   ├── Models/             # Domain entities (User, Challenge, Team, WhatsAppSession)
│   └── Providers/          # Service registration (App, Auth, Jetstream)
├── bootstrap/              # App initialization
├── config/                 # Service configurations
├── database/
│   ├── migrations/         # Schema definitions (Stripe, Teams, Challenges)
│   └── seeders/            # Initial and sample data
├── docs/                   # Project documentation (this folder)
├── public/                 # Static assets (images, og-image)
├── resources/
│   ├── js/
│   │   ├── components/     # UI Components (Custom + Shadcn-Vue)
│   │   ├── composables/    # Reusable Vue logic (SEO, state)
│   │   └── Pages/          # Inertia.js route-based pages
│   ├── css/                # Global styles (Tailwind 4)
│   └── views/              # Blade templates (root index.blade.php)
├── routes/
│   ├── web.php             # Primary application routes
│   ├── api.php             # JSON API endpoints
│   └── console.php         # Artisan commands
├── tests/                  # Pest/PHPUnit tests
├── composer.json           # PHP dependencies (Laravel, Cashier, Filament)
├── package.json            # JS dependencies (Vue, Inertia, Tailwind)
└── vite.config.ts          # Frontend build configuration
```

### Critical Paths
- **Business Logic**: `app/Http/Controllers/` and `app/Models/`.
- **Frontend Pages**: `resources/js/Pages/`.
- **UI Components**: `resources/js/components/`.
- **Stripe Webhook**: `app/Http/Controllers/StripeWebhookController.php`.
