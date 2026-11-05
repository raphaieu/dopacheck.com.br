# STATUS — DOPA Check

> **Fonte única de verdade sobre o estado atual do produto.**
> Levantado em **2026-11-05** a partir de varredura direta no código (migrations, controllers, jobs, routes, tests, docker).
> Docs históricos/técnicos antigos foram movidos para `docs/archive/` — veja o mapa no fim deste arquivo.

---

## 1. Resumo

| Área | Estado |
|---|---|
| Fase 1 — Core Web (MVP) | ✅ ~90% completo e coeso |
| Fase 2 — WhatsApp (check-in + relatórios) | 🟡 Loop principal implementado e testado; faltam bot conversacional, healthcheck e lifecycle de instância |
| Fase 3 — IA (análise de imagem/texto) | ❌ Não iniciada (existe apenas plano em `docs/archive/DOC_TEC_WHATSAPP_IA.md`) |
| Monetização (Stripe/Cashier) | ✅ Funcional (checkout, webhooks, portal, planos) com gating real do FREE |
| Infra | ✅ Docker dev + produção nginx + php-fpm (ver `docs/master-plan.md`); stack EvolutionAPI separada |

---

## 2. Fase 1 — Core Web (implementado)

- **Auth**: Google OAuth (Socialite) + magic link por e-mail (`LoginLinkController`, rate limit 1/min) via Fortify.
- **Times/onboarding**: times Jetstream, onboarding público `/join/{team:slug}` com landing configurável no time (`TeamJoinController`, `TeamApplicationsController`) e aprovação manual no Filament.
- **Desafios**: CRUD completo com visibilidade `private/team/global`, hashtags com unicidade **por escopo** (`challenge_tasks.scope_team_id` + `UNIQUE(scope_team_id, hashtag)`), datas globais início/fim, **join bloqueado se expirado** (`ChallengeController.php:434`), edição com confirmação de reset de progresso (`confirm_reset_progress`), participação do criador automática.
- **Check-in web**: imagem opcional (≤5 MB) para S3-compatível (R2 via `AWS_ENDPOINT`), proteção anti-duplicidade por task/dia com `lockForUpdate`, check-ins retroativos dentro da janela, endpoint AJAX `quickCheckin` (throttle 30/min).
- **Streaks/estatísticas**: `UserChallenge` (`streak_days`, `best_streak`, `updateStats`, `updateCurrentDay`), lifecycle via comando agendado `CheckExpiredChallenges`.
- **Compartilhamento**: perfil público `/u/{username}`, páginas públicas de desafio, OG images 1200×630 (`OgImageController`), share card gerado com Intervention (`api/share-card`), JSON-LD/SEO.
- **PRO (Stripe/Cashier)**: planos em `config/subscriptions.php` (PRO mensal/anual), Stripe Checkout, billing portal, webhooks `/stripe/webhook` + `/webhook/stripe` sincronizando `users.plan`, limite **enforced**: FREE = 1 desafio ativo (`User::canCreateChallenge()`), gating em create/store/show/join.
- **Admin**: painel Filament `/admin` (Users, Teams, TeamApplications). Sem recurso de Challenges ou instâncias WhatsApp.
- **Relatórios**: `ReportController::index` (estatísticas gerais) e por desafio (matriz dia × task).
- **Extra**: PWA manifest + botão de instalação, sitemap agendado, Sentry, Resend instalado (env usa SMTP).

### Lacunas conhecidas da Fase 1
- `ReportController::export` retorna `"Export feature coming soon"` — não implementado.
- Features PRO anunciadas na UI (relatórios avançados, IA) não estão construídas/gated.
- Landing customizada real (dona) hardcodeada como página Vue (`Join/Custom/MicheleGramacho.vue`) — frágil.

---

## 3. Fase 2 — WhatsApp (parcial)

### 3.1 Loop principal ✅ (funcionando, com testes)

- **Infra**: stack completa EvolutionAPI v2 em `docker-compose.whatsapp.yml` (+ `env.whatsapp.example`): evolutionapi + postgres + redis, webhook `messages.upsert` → `http://host.docker.internal:8000/webhook/whatsapp`.
- **Webhook**: `GET|POST /webhook/whatsapp` (`routes/web.php:278`), CSRF-exempt (`bootstrap/app.php:32-37`). Parsing de formatos Baileys novos/antigos, imagem base64, `image_url` ou `media_url` S3, hashtags via regex, `videoMessage`/`audioMessage` tratados como anexo.
- **Confirmação automática**: DM com o bot confirma `users.whatsapp_confirmed` e backfill `whatsapp_number`.
- **Vínculo automático de grupo**: primeira interação no grupo grava `teams.whatsapp_group_jid/name` (`WhatsAppController.php:230-245`).
- **Check-in via WhatsApp**: `ProcessWhatsappCheckinJob` (479 linhas) — resolve usuário (variações de nº BR), team pelo JID, task pela primeira hashtag (escopada ao time no grupo; desafios ativos do usuário na DM), idempotência por `whatsapp_message_id` + 1/dia, cria check-in `source='whatsapp'` aprovado e **reage ✅/❌** na mensagem.
- **Relatórios/lembretes**: `SendDailyReminderJob` agendado diário às 20:00 (`routes/console.php:15`) — recap no grupo (conclusão, destaques, pendentes) e DM de lembrete, gated em `EVOLUTION_INSTANCE`.
- **Testes**: `tests/Feature/Jobs/ProcessWhatsappCheckinJobTest.php` — 5 casos reais (DM sucesso, usuário desconhecido ❌, `image_url`, `media_url` S3, escopo de grupo).

### 3.2 Lacunas da Fase 2 ❌

| # | Gap | Detalhe |
|---|---|---|
| G1 | **Bot conversacional** | Nada responde "oi"/comandos/texto. Textos vão p/ buffer Redis, mas `ProcessWhatsappBufferJob` é stub comentado ("Aqui você pode integrar com os agents de IA"). Feedback em grupo é só reação. |
| G2 | **Lifecycle de instância Evolution** | Sem create-instance, QR, connect/logout, **healthcheck** (parte do "Fase 2" do SPEC). `EvolutionApiService` só tem `sendReaction` + `sendTextMessage`. |
| G3 | **Página Inertia inexistente** | `GET /whatsapp/connect` renderiza `WhatsApp/Connect`, que **não existe** em `resources/js/Pages` → rota quebra. |
| G4 | **Composable quebrado** | `resources/js/composables/useWhatsApp.ts`: POST sem `body` (falha na validação) e `disconnect` usa POST em rota DELETE. O card `WhatsAppConnection.vue` funciona (não usa o composable). |
| G5 | **Schema morto** | Tabela `whatsapp_sessions` + model `WhatsAppSession` nunca usados (sessão real = cache Redis 24h). |
| G6 | **Nº do bot hardcoded** | Fallback `'5571993676365'` em `WhatsAppController.php:59` e `DopaController.php:71`. |
| G7 | **Sem config centralizada** | `EVOLUTION_*` lidos via `env()` espalhado (quebra `config:cache`); não existe `config/whatsapp.php`. |
| G8 | **Falta de testes** | Webhook/controller (`WhatsAppController`), `SendDailyReminderJob`, camada HTTP do `EvolutionApiService` e bus handlers sem testes. |

---

## 4. Código morto / abandonado

- **`ChatController` + `resources/js/Pages/Chat/**` + `/prism`** — resquício do starter Larasonic (referencia plano "Larasonic Pro ✨" inline; sem relação com o produto). Candidato a remoção.
- **`whatsapp_sessions`** (migrations/model) — nunca usados; remover tabela+model ou implementar de fato a persistência de sessão (G5).
- **`CheckinController::processWhatsAppCheckin`** — stub `TODO` e branch de IA comentada (`ProcessCheckinWithAI::dispatch`).
- **Rotas dev** `/dev/reset-demo`, `/dev/cache-clear` — guardadas por env, mas destrutivas.
- Duplicação de ~4 blocos idênticos de transformação de participantes/completion-rate em `ChallengeController::index` (rot-prone, não morto).

---

## 5. Roadmap priorizado (re-baseline de 2026-11)

Extraído e reavaliado do BMAD (`_bmad-output`, removido em 2026-11-05):

1. **P1 — Correções baratas e de rota**: criar página `WhatsApp/Connect.vue`; corrigir `useWhatsApp.ts`; remover nº do bot hardcoded → env/config central.
2. **P2 — Robustez da Fase 2**: `config/whatsapp.php`; healthcheck de instância Evolution (integrar ao `/whatsapp/status`); testes do webhook/controller e do reminder job.
3. **P3 — Bot conversacional (Fase 2 completa)**: comandos básicos no buffer (`oi/menu/meu status`), respostas de confirmação em texto, drop do schema `whatsapp_sessions` morto.
4. **P4 — Monetização honesta**: implementar ou remover das promises de plano (export de relatórios, insights).
5. **P5 — Fase 3 (IA)**: reviver o buffer com `AnalyzeImageJob`/`AnalyzeTextJob` (`docs/archive/DOC_TEC_WHATSAPP_IA.md` tem o plano original).
6. **P6 — Limpeza**: remover Chat do Larasonic, consolidar `ChallengeController::index`, considerar unificar webhooks Stripe duplicados.

---

## 6. Histórico (do BMAD, removido)

- **Sprint status (2026-03-05)**: Epics 1–4 concluídos:
  - Epic 1 — Projeto VILT + OAuth Google + times + onboarding `/join/{slug}` + aprovação manual ✅
  - Epic 2 — Desafios + tasks/hashtags + check-in web + dashboard ✅
  - Epic 3 — Webhook (fast-ack), identificação de usuário, check-in por hashtag, streaks e reações do bot ✅ (lifecycle/healthcheck e UX conversacional pendentes — ver §3.2)
  - Epic 4 — Stripe checkout + webhooks + limites FREE/PRO + faturas ✅
- **Bugs do "Quick Spec Sprint 2"** — reavaliados em 2026-11-05:
  - ✅ Re-join em desafio **finalizado** não apaga mais histórico (redirect p/ relatório — `ChallengeController::join:1124-1129`).
  - ✅ Join bloqueado em desafio expirado (`:434`).
  - ✅ Recap diário do grupo SQL-based implementado no `SendDailyReminderJob`.
  - ⚠️ Re-join em participação **pausada/abandonada** ainda reseta progresso e `forceDelete` check-ins (decisão de design atual, não bug — revisar se quiser preservação).

---

## 7. Mapa de documentação

### Fontes de verdade (vivas)
| Doc | Papel |
|---|---|
| `docs/STATUS.md` | **Este arquivo** — estado atual + lacunas + roadmap |
| `docs/PRD.md` | Visão de produto (+ bloco de status real anotado) |
| `docs/SPEC.md` | Arquitetura/modelagem/regras de negócio (+ anotações de status) |
| `docs/master-plan.md` | Plano de evolução de infra (produção: **nginx + php-fpm**, último status 2026-02-25) |
| `docs/DOCUMENTACAO_COMPLETA.md` | Referência técnica completa (⚠️ base de 13/01/2026 — conferir `STATUS.md` para o que mudou desde então) |
| `docs/DOCKER_SETUP.md` | Ops/dev local com Docker |
| `README.md` | Setup rápido + overview |
| `config/` + `routes/` | Código como fonte da verdade — base para gerar (re)documentação de API e modelos |

### Arquivados (`docs/archive/`)
Snapshots pontuais, planos concluídos/defasados ou redundantes:
`DOPACheck.md`, `DOC_TEC_BASE_SPRINT_01.md`, `DOC_TEC_BASE_SPRINT_02.md`, `DOC_TEC_COMPLEMENTAR.md`, `DOC_TEC_WHATSAPP_IA.md`, `PRÓXIMAS_TAREFAS.md`, `TESTES_E_PROBLEMAS.md`, `ESTRUTURA_APLICACAO.md`, `source-tree-analysis.md`, `project-scan-report.json`, `plan-onboarding-grupos-automatizado.md`, `project-overview.md`, `architecture.md`, `api-contracts.md`, `data-models.md`, `component-inventory.md`, `development-guide.md`, `deployment-guide.md`.

> Os docs gerados automaticamente em 2026-03-05 (`api-contracts`, `data-models`, `development-guide`, `deployment-guide`, `project-overview`) foram arquivados por darem informações erradas (rota do webhook, nomes de campos, stack de deploy). Quando for preciso documentar API/modelos, gerar de novo a partir do código.
