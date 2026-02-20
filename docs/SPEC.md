# SPEC — DOPA Check

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

### Teams
- id
- name
- slug

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
POST /webhook/whatsapp

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

Fase 1:
- Estabilidade
- Relatórios
- Performance

Fase 2:
- Healthcheck WhatsApp
- Persistência group_jid
- Melhor UX de bot

Fase 3:
- OCR
- Classificação de imagem
- Insights comportamentais