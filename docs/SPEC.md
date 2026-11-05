# SPEC — DOPA Check

> **Status real (2026-11-05):** estado atual e lacunas em [`docs/STATUS.md`](STATUS.md). Resumo: Fase 1 concluída; Fase 2 com check-in via WhatsApp, relatório diário e persistência de `group_jid` no ar (faltam healthcheck, lifecycle de instância e UX de bot); Fase 3 (IA) não iniciada.

## 1. Arquitetura

Frontend:
- Vue 3 + TypeScript
- TailwindCSS
- Inertia.js

Backend:
- Laravel 12
- MySQL
- Redis
- Horizon

Auth:
- Laravel Fortify
- Socialite (Google)

Billing:
- Stripe + Cashier

Infra:
- Docker
- VPS

---

## 2. Modelagem Essencial

### Users
- id
- name
- email
- plan (free/pro)
- whatsapp_number
- whatsapp_confirmed *(auto-confirmado em DM com o bot)*

### Teams
- id
- name
- slug
- whatsapp_group_jid (único)
- whatsapp_group_name

### TeamApplications
- team_id
- name
- email
- whatsapp_number
- status (pending/approved/rejected)

### Challenges
- id
- title
- visibility (private/team/global)
- team_id
- duration_days

### ChallengeTasks
- challenge_id
- name
- hashtag
- scope_team_id
- UNIQUE(scope_team_id, hashtag)

### UserChallenges
- user_id
- challenge_id
- status
- current_day
- streak_days

### Checkins
- user_challenge_id
- task_id
- source (web/whatsapp)
- image_path
- image_url (WhatsApp e web)
- status (ex.: approved)
- whatsapp_message_id (chave de idempotência; UNIQUE)
- checked_at

---

## 3. Regras de Negócio

- Free pode ter apenas 1 desafio ativo
- Check-in único por task/dia
- Hashtag única por escopo
- Apenas membros do time veem desafios team

---

## 4. Fluxo WhatsApp (Especificação Técnica)

Webhook:
GET|POST /webhook/whatsapp (ativo em produção; CSRF-exempt; processamento async via queue — fast-ack)

1. Recebe mensagem
2. Extrai:
   - remoteJid
   - texto
   - imagem
3. Detecta hashtag via regex
4. Resolve:
   - group_jid → team
   - hashtag → task
   - phone → user
5. Valida participação ativa
6. Cria check-in
7. Atualiza estatísticas
8. Envia resposta

---

## 5. Escalabilidade

- Processamento via queue
- Redis cache para resolução de user por número
- Lock em criação de check-in
- Índices otimizados

---

## 6. Roadmap Técnico

Fase 1: *(✅ concluída)*
- Estabilidade
- Relatórios
- Performance

Fase 2: *(🟡 parcial — detalhes em STATUS.md §3)*
- Healthcheck WhatsApp — ❌ pendente
- Persistência group_jid — ✅ (`teams.whatsapp_group_jid`, auto-bind no webhook)
- Melhor UX de bot — ❌ pendente (hoje o feedback é só reação ✅/❌; lembretes/recap diários já ativos às 20h)

Fase 3: *(❌ não iniciada — plano original em `docs/archive/DOC_TEC_WHATSAPP_IA.md`)*
- OCR
- Classificação de imagem
- Insights comportamentais