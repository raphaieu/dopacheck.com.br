---
stepsCompleted: [1, 2, 3]
inputDocuments: ['docs/PRD.md', '_bmad-output/planning-artifacts/architecture.md']
---

# dopacheck.com.br - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for dopacheck.com.br, decomposing the requirements from the PRD and Architecture decisions into implementable stories.

## Requirements Inventory

### Functional Requirements

FR1: Login via Google OAuth.
FR2: Sistema de times (Teams) para organização de membros.
FR3: Onboarding público por link (/join/{slug}).
FR4: Aprovação manual de membros em times.
FR5: Criação de desafios (Challenges).
FR6: Tasks com hashtags únicas por escopo dentro de desafios.
FR7: Check-in via web interface (imagem opcional).
FR8: Dashboard com visualização de progresso do usuário e do time.
FR9: Sistema de monetização Freemium + PRO via Stripe.
FR10: Bot de WhatsApp integrado a grupos vinculados a times.
FR11: Check-in automático via WhatsApp enviando Foto + #hashtag.
FR12: Identificação automática de grupo, hashtag e usuário no recebimento de mensagem do WhatsApp.
FR13: Atualização automática de streaks (sequências) após check-in.
FR14: Confirmação de check-in enviada ao grupo ou privado via bot.
FR15: Limitação de 1 desafio ativo no plano Free.
FR16: Histórico ilimitado e storage permanente no plano PRO.

### NonFunctional Requirements

NFR1: Performance dos Webhooks (Fast-ACK): Resposta p95 < 200ms.
NFR2: Escalabilidade: Suporte a picos de 600-800 eventos simultâneos via Redis Horizon.
NFR3: Integridade de Dados: Idempotência rigorosa baseada em `message_id`.
NFR4: Disponibilidade: Processamento de webhooks 100% assíncrono.
NFR5: Mobile-First UX: Interface reativa otimizada (VILT stack).
NFR6: Segurança de Desenvolvimento: PHP 8.3 Strict Types e TypeScript.
NFR7: Persistência PRO: Storage externo (S3/MinIO/R2) para mídia persistente.

### Additional Requirements

- **Starter Template**: Custom VILT Stack (Laravel 12.0, Inertia 2.0, Vue 3.5, Tailwind 4.0).
- **Backend**: PHP 8.3+, Laravel 12.
- **Queue/Cache**: Redis + Laravel Horizon.
- **Admin**: Filament 3.2.
- **Auth**: Laravel Jetstream + Google OAuth.
- **Payment**: Laravel Cashier (Stripe).
- **WhatsApp**: Evolution API (Externo).
- **Storage Layer**: Flysystem (Local/MinIO/S3).

### FR Coverage Map

FR1: Epic 1 - Login via Google OAuth
FR2: Epic 1 - Sistema de times (Teams)
FR3: Epic 1 - Onboarding público por link
FR4: Epic 1 - Aprovação manual de membros
FR5: Epic 2 - Criação de desafios (Challenges)
FR6: Epic 2 - Tasks com hashtags únicas
FR7: Epic 2 - Check-in via web interface
FR8: Epic 2 - Dashboard de progresso
FR9: Epic 4 - Monetização Freemium/PRO (Stripe)
FR10: Epic 3 - Bot de WhatsApp integrado
FR11: Epic 3 - Check-in automático via WhatsApp
FR12: Epic 3 - Identificação automática de payloads
FR13: Epic 3 - Atualização automática de streaks
FR14: Epic 3 - Confirmação via bot
FR15: Epic 4 - Limitação de desafios no Free
FR16: Epic 4 - Histórico ilimitado no PRO

## Epic List

### Epic 1: Fundação & Identidade (Auth & Basic Teams)
*Goal*: Usuários podem se autenticar via Google e configurar seus times com links de onboarding personalizados.
**FRs covered:** FR1, FR2, FR3, FR4.

### Epic 2: Desafios & Disciplina (Core Web Challenges)
*Goal*: Usuários podem criar desafios, definir tarefas com hashtags e registrar check-ins via interface web com dashboard de progresso.
**FRs covered:** FR5, FR6, FR7, FR8.

### Epic 3: Automação "Zero Fricção" (WhatsApp Integration)
*Goal*: Usuários PRO podem realizar check-ins enviando apenas foto + hashtag no WhatsApp, com processamento assíncrono e atualização de streaks.
**FRs covered:** FR10, FR11, FR12, FR13, FR14.

### Epic 4: Monetização & Experiência PRO (Stripe & Quotas)
*Goal*: Gerenciar o fluxo de pagamentos via Stripe e aplicar limites dinâmicos entre usuários Free e PRO.
**FRs covered:** FR9, FR15, FR16.

## Epic 1: Fundação & Identidade (Auth & Basic Teams)

*Goal*: Usuários podem se autenticar via Google e configurar seus times com links de onboarding personalizados.

### Story 1.1: Configuração Inicial do Projeto (VILT Stack)
As a desenvolvedor,
I want configurar o ambiente base usando o template VILT (Laravel 12 + Vue 3.5),
So that eu tenha uma estrutura de código consistente e moderna seguindo a arquitetura.

**Acceptance Criteria:**
**Given** os requisitos de stack técnica definida na arquitetura
**When** inicializo o projeto com Laravel 12, Inertia 2.0 e Tailwind 4.0
**Then** consigo rodar o servidor de desenvolvimento sem erros
**And** as tipagens TypeScript e Strict Types em PHP estão ativas

### Story 1.2: Autenticação via Google OAuth
As a usuário novo ou recorrente,
I want me autenticar usando minha conta Google,
So that o onboarding seja instantâneo e sem senhas.

**Acceptance Criteria:**
**Given** que estou na página de login
**When** clico no botão "Entrar com Google"
**Then** sou redirecionado para o fluxo da conta Google
**And** após o sucesso, minha conta é criada/carregada no Laravel Jetstream

### Story 1.3: Criação e Configuração de Times
As a capitão de um grupo,
I want criar um novo time e definir nome e descrição,
So that eu possa organizar meus amigos ou comunidade.

**Acceptance Criteria:**
**Given** que estou logado
**When** acesso a área de "Times" e preencho o formulário
**Then** o time é persistido no banco de dados
**And** eu me torno o "Owner" (Dono) desse time via Jetstream

### Story 1.4: Onboarding via Link Público (/join/{slug})
As a capitão,
I want gerar um link único de convite,
So that as pessoas entrem no meu time sem esforço manual.

**Acceptance Criteria:**
**Given** que o time possui um slug único
**When** um usuário externo acessa `dopacheck.com.br/join/slug-do-time`
**Then** ele visualiza as informações do time e o botão para solicitar entrada

### Story 1.5: Gestão de Membros e Aprovação Manual
As a capitão,
I want visualizar pedidos de entrada e aprová-los ou recusá-los,
So that eu tenha controle sobre quem entra na comunidade.

**Acceptance Criteria:**
**Given** que existem solicitações pendentes
**When** eu clico em "Aprovar" no dashboard administrativo
**Then** o usuário é adicionado à tabela `team_user` e notificado

## Epic 2: Desafios & Disciplina (Core Web Challenges)

*Goal*: Usuários podem criar desafios, definir tarefas com hashtags e registrar check-ins via interface web com dashboard de progresso.

### Story 2.1: Criação de Desafios (Challenges)
As a capitão de um time,
I want criar um desafio (ex: "75 Hard", "Leitura Diária") definindo data de início e término,
So that meu time tenha um objetivo comum.

**Acceptance Criteria:**
**Given** que sou dono de um time
**When** crio um desafio no painel
**Then** ele é vinculado ao meu time e fica visível para os membros

### Story 2.2: Configuração de Tasks e Hashtags
As a capitão,
I want adicionar tarefas ao desafio e definir uma hashtag única para cada uma (ex: #treino, #leitura),
So that o sistema reconheça o check-in automaticamente depois.

**Acceptance Criteria:**
**Given** um desafio ativo
**When** adiciono uma tarefa e associo uma hashtag única
**Then** essa configuração é persistida e exibida na página do desafio

### Story 2.3: Check-in Manual via Web Interface
As a membro de um time,
I want marcar uma tarefa como concluída no dashboard (upload de imagem opcional),
So that eu não precise de WhatsApp se preferir a web.

**Acceptance Criteria:**
**Given** que participo de um desafio
**When** seleciono uma tarefa no dashboard e clico em "Concluir"
**Then** um registro de `Checkin` é criado no banco com o timestamp atual

### Story 2.4: Dashboard de Progresso (User & Team)
As a usuário,
I want visualizar minha barra de progresso e o ranking do time,
So that eu me sinta motivado pela gamificação social.

**Acceptance Criteria:**
**Given** que existem check-ins realizados
**When** acesso a página do desafio
**Then** visualizo meu streak atual e a tabela de líderes (leaderboard) do time

## Epic 3: Automação "Zero Fricção" (WhatsApp Integration)

*Goal*: Usuários PRO podem realizar check-ins enviando apenas foto + hashtag no WhatsApp, com processamento assíncrono e atualização de streaks.

### Story 3.1: Configuração do Webhook & Fast-ACK
As a sistema,
I want receber notificações da Evolution API e responder com sucesso em <200ms,
So that a conexão não sofra timeout e o processamento seja escalável.

**Acceptance Criteria:**
**Given** um evento de mensagem recebida
**When** o webhook atinge o endpoint do Laravel
**Then** o sistema valida o token e enfileira um Job no Redis imediatamente
**And** retorna um status 200 OK sem esperar o processamento da lógica

### Story 3.2: Identificação de Usuário e Contexto (Job)
As a sistema (Worker),
I want identificar o usuário pelo número de WhatsApp e o time pelo ID do grupo,
So that eu saiba onde registrar o progresso.

**Acceptance Criteria:**
**Given** um Job de mensagem sendo processado
**When** o sistema busca no banco pelo telefone (sender) e grupo (remoteJid)
**Then** ele encontra o `User` e o `Team` vinculados

### Story 3.3: Reconhecimento de Hashtag e Registro de Check-in
As a usuário PRO,
I want enviar uma foto com uma hashtag e ter meu check-in validado,
So that minha tarefa seja concluída sem abrir o app.

**Acceptance Criteria:**
**Given** que enviei uma mensagem com mídia e `#hashtag`
**When** a hashtag corresponde a uma tarefa ativa do meu desafio
**Then** um registro de `Checkin` é criado com o link da imagem (storage MinIO/S3)
**And** se o `message_id` já existir no cache/banco, o processamento é ignorado (idempotência)

### Story 3.4: Lógica de Streaks e Feedback via Bot
As a usuário,
I want receber uma confirmação no grupo de que bati minha meta e ver meu streak crescer,
So that eu tenha feedback social imediato.

**Acceptance Criteria:**
**Given** um check-in válido
**When** o sistema atualiza o contador de dias consecutivos
**Then** o bot envia uma mensagem de confirmação (ex: "Check-in feito! 🔥 Streak de 5 dias!") via API

## Epic 4: Monetização & Experiência PRO (Stripe & Quotas)

*Goal*: Gerenciar o fluxo de pagamentos via Stripe e aplicar limites dinâmicos entre usuários Free e PRO.

### Story 4.1: Checkout do Plano PRO via Stripe
As a usuário Free,
I want assinar o plano PRO usando meu cartão,
So that eu possa usar o check-in via WhatsApp e ter histórico ilimitado.

**Acceptance Criteria:**
**Given** que estou logado e no plano Free
**When** clico em "Assinar PRO" e completo o Checkout Stripe
**Then** sou redirecionado de volta para o app com uma mensagem de sucesso

### Story 4.2: Sincronização de Assinatura via Webhooks
As a sistema,
I want processar webhooks do Stripe (checkout.completed, customer.subscription.deleted),
So that o campo `plan` do usuário esteja sempre atualizado.

**Acceptance Criteria:**
**Given** uma notificação de pagamento confirmado do Stripe
**When** o webhook processa o evento
**Then** o campo `plan` do usuário vira "PRO" e a data de expiração é atualizada

### Story 4.3: Aplicação de Limites de Plano (Free vs PRO)
As a sistema,
I want bloquear a criação de mais de 1 desafio para usuários Free,
So that eu possa incentivar o upgrade para o PRO.

**Acceptance Criteria:**
**Given** que um usuário Free já possui 1 desafio ativo
**When** ele tenta criar o segundo
**Then** o sistema retorna um erro de validação e sugere o upgrade

### Story 4.4: Gerenciamento de Faturas e Cancelamento
As a usuário PRO,
I want acessar o portal do cliente Stripe,
So that eu possa baixar faturas ou cancelar minha assinatura.

**Acceptance Criteria:**
**Given** que sou assinante ativo
**When** clico em "Gerenciar Assinatura"
**Then** sou redirecionado para o Stripe Billing Portal (Laravel Cashier)
