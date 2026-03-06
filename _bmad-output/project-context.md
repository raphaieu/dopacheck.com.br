sections_completed:
  ['technology_stack', 'language_rules', 'framework_rules', 'testing_rules', 'style_rules', 'workflow_rules', 'critical_rules']
status: 'complete'
rule_count: 32
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

- **Backend:** PHP 8.3+, Laravel 12.0
- **Admin Panel:** Filament ^3.2
- **Frontend Framework:** Vue 3 (^3.5) com `<script setup>`
- **Interactivity:** Inertia.js (^2.0)
- **Styling:** Tailwind CSS ^4.0
- **UI Components:** Radix Vue, Iconify, Lucide Vue Next
- **Database:** MySQL (produção), Redis para Filas/Horizon
- **Testing:** Pest ^3.8
- **Infrastructure:**
  - **Produção:** Laravel Octane, Laravel Horizon, **EvolutionAPI** (WhatsApp) em VPS dedicada via Docker, **MinIO** (S3 compatible) na mesma VPS para storage de mídia.
  - **Desenvolvimento:** Ambiente local com `php artisan serve` e mocks/instâncias locais para testes.

## Critical Implementation Rules

### Language-Specific Rules

**PHP (8.3+):**
- **Strict Typing:** Todo arquivo PHP deve começar com `declare(strict_types=1);`.
- **Type Hinting:** Use type hints em todos os parâmetros de função e retornos.
- **Naming:** CamelCase para métodos e variáveis; PascalCase para Classes.

**JavaScript / TypeScript:**
- **Vue 3:** Use sempre a sintaxe `<script setup>`.
- **Types:** Prefira TypeScript quando possível (visto no `tsconfig.json`). No Vue, defina props usando `defineProps()`.
- **Linting:** O projeto segue uma configuração rigorosa de linting (visto no `package.json`).
### Framework-Specific Rules

**Laravel (12.0):**
- **Inertia Protocol:** Use `Inertia::render()` para respostas de página. Formulários devem usar a biblioteca `@inertiajs/vue3`.
- **Database:** MySQL é o banco principal. Migrations devem ser bem documentadas.
- **Horizon & Octane:** Octane está presente mas o uso em produção é experimental. Horizon deve ser usado para monitorar as filas do Redis.
- **Admin:** Filament (^3.2) é usado para o painel administrativo.
- **Portuguese Content:** Todo conteúdo voltado ao usuário deve estar em **Português (pt-br)**.

**Vue 3 & Tailwind 4:**
- **Interatividade:** Use a composição de componentes do Vue 3. Sintaxe `<script setup>` é obrigatória.
- **Styling:** Use classes utilitárias do Tailwind 4 diretamente nos templates.
- **Iconify:** Use o componente `<Icon />` do `@iconify/vue` para ícones.
- **SEO:** O projeto utiliza um composable `useSeoMetaTags` para gerenciar meta tags.

### Testing Rules

- **Framework:** Pest (^3.8). Todos os testes novos devem seguir a sintaxe fluida do Pest.
- **Organization:**
  - `tests/Feature`: Testes de integração, endpoints e fluxos de usuário (Inertia/Controller).
  - `tests/Unit`: Lógica pura de PHP e helpers.
- **Laravel Integration:** Use os helpers do `pest-plugin-laravel` para asserções de banco de dados e rotas.
- **Type Coverage:** O projeto monitora cobertura de tipos (`pest-plugin-type-coverage`). Garanta que novos códigos mantenham a cobertura alta.
- **Parallel Testing:** Os testes são configurados para rodar em paralelo (`--parallel`). Evite estados globais que possam causar race conditions.

### Code Quality & Style Rules

**Linting & Formatting:**
- **ESLint & Prettier:** Siga rigorosamente a configuração `@antfu/eslint-config`. Execute `npm run lint` e `npm run format` antes de concluir mudanças.
- **Pint & Rector:** Use `composer format` para manter o estilo do PHP alinhado.

**Naming Conventions:**
- **PHP Classes/Controllers:** `PascalCase`.
- **Vue Components:** `PascalCase`.
- **Variables/Methods:** `camelCase`.
- **Database Tables/Columns:** `snake_case`.

**Organization:**
- **Controllers:** Mantenha-os focados. Considere extrair lógica complexa para `Actions` ou `Services` (padrão Laravel).
- **Vue Components:** Organize componentes de UI em `resources/js/components/ui` e componentes de negócio em `resources/js/components`.

**Documentation:**
- **Comments:** Comentários em PHP devem seguir o padrão DocBlock quando necessário.
- **Language:** Prefira comentários em **Português (pt-br)** para explicar a lógica de negócio.

### Development Workflow Rules

**Git & Version Control:**
- **Branches:** Utilize nomes descritivos (ex: `feat/whatsapp-reaction`, `fix/login-bug`).
- **Commits:** Mensagens claras e objetivas seguindo **Conventional Commits** (feat, fix, refactor, docs).

**Error Tracking & Logs:**
- **Sentry:** Monitoramento ativo em produção (`sentry-laravel`).
- **Logging:** Use `Log::error` ou `Log::warning` para falhas críticas (ex: falhas em webhooks do WhatsApp).

**Environment Management:**
- **Local Dev:** Roots em `/home/raphael/www/dopacheck.com.br`. Use `php artisan serve` e `npm run dev`.
- **Decoupled Architecture:** Em produção, as requisições de mídia e WhatsApp dependem da VPS externa via EvolutionAPI/MinIO. Desenvolva prevendo essa separação.
- **Storage:** Configure o `FILESYSTEM_DISK` corretamente. O MinIO em produção é gerenciado externamente ao servidor do Laravel.

### Critical Don't-Miss Rules

**Ambientes (Dev vs Prod):**
- **Nunca assuma acesso direto ao disco do MinIO:** Em produção, ele é externo. Use sempre as fachadas de `Storage` do Laravel para garantir portabilidade entre o ambiente local (public disk) e a VPS externa (s3 disk).
- **Fast-ACK no Webhook:** O webhook do WhatsApp deve responder o mais rápido possível. Processe apenas a validação inicial e jogue a carga pesada para filas (ex: `ProcessWhatsAppCheckinJob`).

**Patterns Locais:**
- **Inertia Responses:** Sempre retorne `Inertia::render()` para rotas de navegação. Retornos JSON diretos são permitidos apenas para endpoints da "API interna" consumidos via `axios` ou requisições AJAX específicas.
- **Strict Types:** Mantenha `declare(strict_types=1);` no topo de cada arquivo PHP.

**Segurança e Performance:**
- **Nginx & Octane:** O Octane gerencia o processo do Laravel de forma persistente. Evite Singletons que guardam estado entre requisições para prevenir vazamento de memória ou de dados entre sessões.
- **Dedupe de Mensagens:** Sempre verifique se o `message_id` do WhatsApp já foi processado antes de criar um novo check-in para evitar duplicidade.

### Premium Design System (v2.0)

**Aesthetics & Visual Layers:**
- **Atmospheric Backgrounds:** Use animated colored blurs (e.g., `bg-blue-400/10 blur-[120px] rounded-full animate-pulse`) to create depth.
- **Glassmorphism:** Components should use `bg-white/70 backdrop-blur-xl border border-white/80` for a high-end feel.
- **Deep Shadows:** Use `shadow-2xl shadow-slate-200/50` or custom brand-colored glows (`shadow-blue-500/10`).

**Typography:**
- **Emphasis:** Use `font-black`, `uppercase`, `tracking-widest` for headers and labels.
- **Italics:** Use `italic` on main titles for a dynamic, "speed" aesthetic.
- **Micro-copy:** Use `text-[10px]` with `font-bold` for metadata and metadata-labels.

**Interactive Elements:**
- **Buttons:** Standardize on large, rounded-2xl buttons with brand gradients (`from-blue-600 to-violet-600`) and subtle scale/hover transforms.
- **Inputs:** Maintain high contrast with `bg-slate-50/50` and `focus:bg-white`.
- **Icons:** Mandatory use of `@iconify/vue` (Lucide sets) for all UI controls.

### Storage & Cloud Integration

**S3 / MinIO Patterns:**
- **Disk-Agnostic Storage:** Use the `Storage` facade with dynamic disks (`FILESYSTEM_DISK`).
- **Web Check-ins:** Web-based check-ins must upload to the cloud (S3) and store full URLs using `Storage::disk($disk)->url($path)`.
- **Environment Parity:** Local dev uses `public` or local S3 (MinIO), while production uses dedicated S3/R2 storage.

---

## Code Patterns

- **PHP Components:**
  - Controllers: Standard Laravel controllers in `app/Http/Controllers`.
  - Models: Standard Eloquent models in `app/Models`.
  - Resources: Filament resources in `app/Filament/Resources`.
- **Vue Components:**
  - Pages: Located in `resources/js/Pages`.
  - Shared Components: Located in `resources/js/components`.
  - UI Library: Custom Radix-based components in `resources/js/components/ui`.
- **Naming Conventions:**
  - PHP Classes/Controllers: `PascalCase`.
  - Vue Components: `PascalCase`.
  - Variables/Methods: `camelCase`.
  - Routes: `kebab-case`.
- **State Management:** Use Vue's reactive `ref` and `computed` within `<script setup>`. Shared state via Inertia props or `usePage()`.
- **CSS:** Use Tailwind 4 utility classes directly in Vue templates. No custom CSS unless strictly necessary.
