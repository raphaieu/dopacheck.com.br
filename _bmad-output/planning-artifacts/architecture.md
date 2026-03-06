---
stepsCompleted: [1, 2, 3, 4, 5, 6, 7, 8]
workflowType: 'architecture'
lastStep: 8
status: 'complete'
project_name: 'DOPA Check'
user_name: 'Raphael'
date: '2026-03-05'
completedAt: '2026-03-05'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
- **Unified Auth**: Google OAuth para onboarding simplificado.
- **Social Core**: Gestão de times (Jetstream) e desafios com hashtags únicas.
- **Multi-channel Tracking**: Check-in via interface web e automatizado via WhatsApp.
- **Monetization**: Fluxo Freemium/PRO com Stripe Cashier e sincronização via Webhooks.

**Non-Functional Requirements:**
- **High Availability**: Infraestrutura desacoplada para suportar picos de uso nos horários de pico (ex: 20h).
- **Data Integrity**: Deduplicação rigorosa de mensagens para evitar streaks duplicados.
- **Mobile-First UX**: Interface reativa (Vue/Inertia) otimizada para smartphones (VILT stack).

**Scale & Complexity:**
- Primary domain: Web + WhatsApp Integration
- Complexity level: Medium
- Estimated architectural components: 10 (Web, API, Workers, Redis, S3, EvolutionAPI, etc.)

### Technical Constraints & Dependencies
- **Stack**: Laravel 12 + Vue 3 (VILT) + PHP 8.3 (Strict Types).
- **Infra**: Dependência da Evolution API para ponte com WhatsApp e MinIO para storage externo. Fast-ACK em webhooks (<200ms).

### Cross-Cutting Concerns Identified
- **Autenticação & Autorização**: Gestão robusta de cargos (owner/admin) em times.
- **Idempotência**: Garantia de que reentregas de webhooks não corrompam os dados.

## Starter Template Evaluation

### Primary Technology Domain
Full-stack Web (Inertia.js + Laravel) baseado nos requisitos de alta interatividade e SEO.

### Selected Starter: Custom VILT Stack (Laravel 12 Edition)

**Rationale for Selection:**
O projeto já utiliza a stack VILT de última geração. A escolha de manter o monólito com Inertia 2.0 justifica-se pela redução de complexidade (Single Source of Truth no Laravel) e pelas novas capacidades de requests assíncronos e prefetching que dão uma experiência de SPA premium sem o overhead de uma API REST/GraphQL separada.

**Architectural Decisions Provided by Starter:**

**Language & Runtime:**
- PHP 8.3 com Strict Types ativo em todo o core.
- TypeScript (TSConfig configurado) para garantir segurança de tipos no frontend Vue.

**Styling Solution:**
- **Tailwind CSS 4.0**: Utilizando o novo engine otimizado para performance e zero runtime CSS.

**Build Tooling:**
- **Vite 6**: Fast Refresh e builds extremamente rápidos, essencial para a DX (Developer Experience) ágil do projeto.

**Testing Framework:**
- **Pest 3**: Testes fluídos e focados em legibilidade, garantindo cobertura de Features e Unit.

**Code Organization:**
- **Inertia Protocol**: Rotas definidas no Laravel, Views em Vue 3.5 (`<script setup>`).
- **Standard Laravel Directory**: Separação clara entre Models, Controllers e Service Layer (para WhatsApp/Stripe).

## Core Architectural Decisions

### Data Architecture
- **Database**: MySQL 8.0/8.4 (Migrações via Laravel).
- **Cache & Filas**: Redis (Laravel Horizon) — Essencial para o "Fast-ACK" do WhatsApp.
- **Validação**: Backend (FormRequests) + Frontend (Inertia Forms reativos).
- **Idempotência**: Dedupe via `message_id` do WhatsApp no banco/cache.

### Authentication & Security
- **Método**: Laravel Jetstream + Google OAuth.
- **Autorização**: Policies do Laravel (Owner/Admin/Member).
- **Middleware**: Verificação de e-mail e planos PRO integrados.

### API & Communication Patterns
- **Whatsapp Ingress**: Evolution API externa para webhooks assíncronos.
- **Padrão de Resposta**: Fast-ACK (200 OK) + Job enfileirado (`ProcessWhatsappCheckinJob`).
- **Storage**: Abstração `Storage` (MinIO/S3) para mídia persistente.

## Implementation Patterns & Consistency Rules

### Naming Patterns
- **Database**: Tabelas em `snake_case` (plural), Colunas em `snake_case`. Ex: `user_challenges`, `checked_at`.
- **PHP/Laravel**: Classes em `PascalCase`, Métodos e Variáveis em `camelCase`.
- **Vue Components**: `PascalCase` para o nome do arquivo e do componente. Ex: `CheckinModal.vue`.
- **CSS/Tailwind**: Uso direto de classes utilitárias no template, evitando `@apply` ou CSS customizado.

### Structure Patterns
- **Controllers**: `app/Http/Controllers` (Web e API).
- **Models**: `app/Models`.
- **Vue Pages**: `resources/js/Pages/{Domain}/{Page}.vue`.
- **Vue Components**: `resources/js/components/ui` (Base/Shadcn) e `resources/js/components` (Domínio).
- **Testes**: Sintaxe fluída do **Pest** em `tests/Feature` e `tests/Unit`.

### Format & Process Patterns
- **Inertia Responses**: Sempre usar `Inertia::render()` para navegação.
- **API Errors**: Estrutura padrão do Laravel/Inertia (errors via props).
- **Datas**: ISO 8601 strings para transporte JSON.
- **Fast-ACK Webhooks**: Validar e enfileirar imediatamente. Nenhuma lógica pesada síncrona no Controller.

## Project Structure & Boundaries

### Functional Mapping
- **Unified Auth (Google)**: `app/Http/Controllers/Auth` & `resources/js/Pages/Auth`.
- **Social Core (Desafios/Times)**: `app/Models/Challenge`, `app/Models/Team` & `resources/js/Pages/Challenges`.
- **WhatsApp Tracking**: `app/Http/Controllers/WhatsappWebhookController` & `app/Jobs/ProcessWhatsappCheckinJob`.
- **Monetization (Stripe)**: `app/Http/Controllers/StripeWebhookController` & `resources/js/Pages/Subscriptions`.

### Architectural Boundaries
- **API Boundary**: Webhooks externos (Evolution API) -> Redis Queue -> App Jobs.
- **Frontend Boundary**: Reatividade via Inertia 2.0 (Deferred Props para feeds sociais).
- **Data Boundary**: Eloquent ORM + Cache Layer (Idempotência).
- **Storage Boundary**: Laravel `Storage` (R2/S3/MinIO) para mídia persistente.

### Project Directory Highlights
```text
dopacheck.com.br/
├── app/
│   ├── Http/Controllers/ (Dashboard & Webhooks)
│   ├── Jobs/ (Cérebro assíncrono do projeto)
│   ├── Models/ (Social & Auth entities)
│   └── Services/ (Outer-world integrators)
├── resources/js/
│   ├── components/ (ui/ e domínio)
│   ├── Pages/ (Inertia Views)
│   └── layouts/ (Dynamic App Shell)
├── tests/
│   ├── Feature/ (E2E logic)
│   └── Unit/ (Domain logic)
└── docker-compose.yml (Infra local full: Web, MySQL, Redis, MinIO)
```

## Architecture Validation Results

### Coherence Validation ✅
Todas as escolhas tecnológicas são integradas nativamente (VILT Stack). O uso de Redis/Horizon para gerenciar o tráfego do WhatsApp evita gargalos no banco de dados principal.

### Requirements Coverage Validation ✅
- **Epics/Features**: Auth, Social e Monetização mapeados para componentes Jetstream e Cashier.
- **NFRs**: Requisitos de performance (webhooks) e escalabilidade (S3/MinIO) totalmente endereçados.

### Gap Analysis Results
- **Importante**: Mapear detalhadamente o Mocking da Evolution API nos testes feature para evitar chamadas reais durante o CI.
- **Sugestão**: Implementar um `CheckinRateLimiter` customizado para evitar spam de webhooks por usuário.

### Architecture Completeness Checklist
- [x] Contexto e complexidade avaliados.
- [x] Stack tecnológica especificada com versões 2026.
- [x] Padrões de implementação e nomenclatura definidos.
- [x] Estrutura de diretórios e fronteiras mapeadas.

### Architecture Readiness Assessment
**Overall Status**: READY FOR IMPLEMENTATION
**Confidence Level**: High
**Key Strengths**: Baixa complexidade de manutenção (Monólito) com alta resiliência (Filas/Storage Externo).

### Implementation Handoff
Agentes devem priorizar a configuração do **Laravel Horizon** e da **Evolution API** como primeira story técnica, garantindo que o "encanamento" de webhooks esteja pronto antes das telas.
