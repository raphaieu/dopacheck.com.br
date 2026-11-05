# Architecture: DOPA Check

_Detailed architectural mapping of the DOPA Check platform._

## System Architecture (VILT)
DOPA Check follows the **VILT Stack** (Vue, Inertia, Laravel, Tailwind) architecture. This approach provides a Single Page Application (SPA) experience while keeping all business logic, routing, and state management in the backend.

### Core Components
1. **Frontend (Vue 3 + Inertia 2)**:
   - Uses `<script setup>` and Composition API.
   - Client-side navigation handled by Inertia.
   - UI defined by specialized DOPA components and a Shadcn-Vue based design system.
2. **Backend (Laravel 12)**:
   - Handles authentication (Fortify/Jetstream).
   - Manages team-based access control.
   - Processes business logic for habit tracking and streaks.
3. **Data Layer (MySQL + Redis)**:
   - MySQL for persistent storage.
   - Redis for caching, sessions, and asynchronous job processing (Horizon).

## Key Integration Patterns
### WhatsApp Data Flow
- **Ingress**: WhatsApp messages/images are received via an external API (Evolution API as per Master Plan).
- **Processing**: A webhook hits the Laravel backend, triggering a job in the queue to process the message and record a `Checkin`.
- **Response**: The system communicates back to the user via WhatsApp (planned).

### Payment System (Stripe)
- Integrated via **Laravel Cashier**.
- `StripeWebhookController` synchronizes subscription state to the `User` model (`plan`, `subscription_ends_at`).

## Security & Reliability
- **Strict Typing**: Mandatory `declare(strict_types=1);` in all PHP files.
- **Monitoring**: Sentry for error tracking.
- **Testing**: Pest-driven test suite for both feature and unit testing.
