# UI Component Inventory: DOPA Check

_This document catalogs the frontend components, categorized by their role in the application._

## Custom DOPA Components
These are domain-specific components located in `resources/js/components/`.

- **Layout & Navigation**: `DopaHeader.vue`, `DopaHeaderGuest.vue`, `AppSidebarContent.vue`.
- **Domain: Challenges**: `ChallengeCard.vue`, `FeaturedChallengeCard.vue`, `CheckinModal.vue`, `TaskCard.vue`, `ParticipantCard.vue`.
- **Domain: Teams**: `AppTeamManager.vue`.
- **Domain: WhatsApp**: `WhatsAppConnection.vue`.
- **Common UI**: `PricingCard.vue`, `ProgressRing.vue`, `SocialLoginButton.vue`, `StatsCard.vue`, `FullPageLoader.vue`.

## Base UI Library (Shadcn-Vue)
Located in `resources/js/components/ui/`, these are low-level reusable components.

- **Inputs**: `button`, `input`, `checkbox`, `select`, `textarea`, `radio-group`, `switch`, `pin-input`.
- **Feedback**: `alert`, `alert-dialog`, `sonner` (Toast), `progress`, `skeleton`.
- **Data Display**: `badge`, `card`, `table`, `avatar`, `accordion`, `tabs`, `aspect-ratio`.
- **Navigation**: `breadcrumb`, `pagination`, `navigation-menu`, `sheet`, `sidebar`.
- **Overlays**: `dialog`, `drawer`, `popover`, `tooltip`, `hover-card`, `context-menu`, `dropdown-menu`.
- **Complex**: `calendar`, `range-calendar`, `carousel`, `command`, `resizable`, `scroll-area`, `stepper`.

## Design System Notes
- **Styling**: Tailwind CSS 4.
- **Icons**: Iconify (`@iconify/vue`), Lucide (`lucide-vue-next`).
- **Animations**: `tailwindcss-animate`, `tw-animate-css`.
