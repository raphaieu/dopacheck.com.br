# 📋 Próximas Tarefas - DOPA Check

**Última atualização**: 07/01/2026  
**Status do Projeto**: Beta Funcional - Core Web (sem WhatsApp no MVP atual)

---

## 🎯 Prioridade Alta (Sprint Atual)

### 🔧 Correções e Melhorias Imediatas

#### 1. **Otimizar Performance do Cálculo de Streak**
- **Problema**: `calculateCurrentStreak()` itera dia por dia, pode ser lento para desafios longos
- **Solução**: Otimizar usando queries mais eficientes ou cache
- **Arquivo**: `app/Models/UserChallenge.php:297`
- **Prioridade**: 🔴 Alta
- **Estimativa**: 2-3 horas

#### 2. **Implementar Invalidação de Cache**
- **Problema**: Cache não é invalidado quando dados são atualizados
- **Solução**: Implementar invalidação de cache quando dados relevantes são atualizados
- **Arquivos**: Vários controllers com `Cache::remember()`
- **Prioridade**: 🔴 Alta
- **Estimativa**: 3-4 horas

#### 3. **Melhorar Tratamento de Erros em Upload**
- **Problema**: Se upload falhar, imagem pode ser parcialmente salva
- **Solução**: Melhorar tratamento de erros e cleanup de arquivos órfãos
- **Arquivo**: `app/Http/Controllers/CheckinController.php:142`
- **Prioridade**: 🟡 Média
- **Estimativa**: 1-2 horas

#### 4. **Agendar Comando de Verificação de Desafios Expirados**
- **Problema**: Comando existe mas não está agendado no cron
- **Solução**: Adicionar ao crontab ou Laravel Scheduler
- **Arquivo**: `app/Console/Commands/CheckExpiredChallenges.php`
- **Prioridade**: 🟡 Média
- **Estimativa**: 30 minutos

---

## 🚀 Roadmap curto (MVP “sem WhatsApp por enquanto”)

### ✅ Concluído (Core Web)
- [x] Core Web funcional (desafios, tasks, check-ins web)
- [x] MySQL + Redis (Horizon) no docker-compose “core web”
- [x] Termos e Política em pt-BR (marca DOPA Check)
- [x] Login Social (Google) end-to-end
- [x] Assinatura PRO (Stripe + Cashier) end-to-end (planos mensal/anual + sincronização de status)
- [x] Docs/README sincronizados com o estado real (DB/URLs/roadmap)

### 📝 Agora (pós-MVP sem WhatsApp)

#### 1. **Perfil público (`/u/{username}`)**
- **Descrição**: Finalizar a página pública do usuário para compartilhamento.
- **Prioridade**: 🔴 Alta

#### 2. **Configurações de usuário**
- **Descrição**: Consolidar a tela existente (perfil, preferências e gestão do plano).
- **Prioridade**: 🟡 Média

#### 3. **Relatórios/Métricas**
- **Descrição**: Página de relatórios com métricas e visão histórica do progresso.
- **Prioridade**: 🟡 Média

#### 4. **Otimizações e robustez**
- **Descrição**: Performance do streak, invalidação de cache, melhorias de upload e agendamentos.
- **Prioridade**: 🟡 Média

### 🕒 Depois (fora do escopo do MVP atual)

#### **Integração WhatsApp (EvolutionAPI)**
- **Status**: adiado para depois de Google + Stripe
- **Notas**:
  - `docker-compose.whatsapp.yml` existe para testes (EvolutionAPI + Postgres)
  - Webhook do DOPA fica em `POST /webhook/whatsapp` (hoje bufferiza eventos)
  - **Backlog UI/Redis**:
    - O card “WhatsApp Bot” (status “Conectado”, contadores e botão “Desconectar”) precisa refletir o **estado real** da sessão.
    - Hoje pode ficar “Conectado” após reload mesmo sem validarmos a instância/estado no backend.
    - Planejado: consolidar “fonte da verdade” (Redis/DB) + endpoint de status com healthcheck da Evolution API e contadores reais.
  - **Backlog (mapping Grupo ↔ Time)**:
    - Precisamos persistir `whatsapp_group_jid` (ex: `1203...@g.us`) no `Team` para resolver rapidamente “mensagem do grupo pertence a qual time”.
    - Opcional: armazenar também `whatsapp_group_name` (subject) quando disponível.

#### 5. **Página de Perfil Público (`/u/username`)**
- **Descrição**: Criar página pública de perfil do usuário
- **Funcionalidades**:
  - Exibir desafios completados
  - Estatísticas públicas (total de check-ins, streak record, etc)
  - Desafios ativos (se público)
  - Compartilhamento de perfil
- **Arquivos**: 
  - `app/Http/Controllers/ProfileController.php` (já existe método `public()`)
  - `resources/js/Pages/Profile/Public.vue` (criar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 4-6 horas

#### 6. **Página de Configurações de Usuário**
- **Descrição**: Melhorar página de configurações existente
- **Funcionalidades**:
  - Editar perfil (nome, username, avatar)
  - Configurar WhatsApp number
  - Preferências de notificações
  - Gerenciar plano (upgrade para PRO)
- **Arquivos**: 
  - `app/Http/Controllers/ProfileController.php` (já existe)
  - `resources/js/Pages/Profile/Settings.vue` (melhorar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 3-4 horas

#### 7. **Relatórios Detalhados com Métricas**
- **Descrição**: Criar página de relatórios avançados
- **Funcionalidades**:
  - Gráficos de progresso ao longo do tempo
  - Análise de padrões (dias da semana mais produtivos)
  - Comparação entre desafios
  - Exportação de dados (CSV/PDF)
- **Arquivos**: 
  - `app/Http/Controllers/ReportController.php` (já existe)
  - `resources/js/Pages/Reports/Index.vue` (criar/melhorar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 6-8 horas

#### 8. **Compartilhamento Nativo Mobile**
- **Descrição**: Implementar Web Share API para compartilhamento nativo
- **Funcionalidades**:
  - Compartilhar card gerado via Web Share API
  - Compartilhar perfil público
  - Compartilhar desafio
- **Arquivos**: 
  - `resources/js/composables/useShare.ts` (criar)
  - Componentes existentes (atualizar)
- **Prioridade**: 🟢 Baixa
- **Estimativa**: 2-3 horas

#### 9. **PWA (Service Worker)**
- **Descrição**: Transformar app em Progressive Web App
- **Funcionalidades**:
  - Service Worker para cache offline
  - Manifest.json configurado
  - Instalação no dispositivo
  - Notificações push (futuro)
- **Arquivos**: 
  - `public/sw.js` (criar)
  - `public/manifest.json` (criar/atualizar)
  - `vite.config.ts` (configurar)
- **Prioridade**: 🟢 Baixa
- **Estimativa**: 4-6 horas

---

## 💎 PRO / Monetização

- **Stripe + Cashier**: o esqueleto existe, mas ainda não está fechado como feature “entregue” (plano, price_id, webhook e bridge de status).

#### 17. **IA Analysis com OpenAI Vision**
- **Descrição**: Analisar imagens de check-ins com IA
- **Funcionalidades**:
  - Enviar imagem para OpenAI Vision API
  - Extrair informações relevantes
  - Validar se imagem corresponde à task
  - Gerar insights automáticos
- **Arquivos**: 
  - `app/Jobs/AnalyzeCheckinWithAIJob.php` (criar)
  - `app/Services/AIImageAnalysisService.php` (criar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 6-8 horas

#### 18. **Upgrade Flow Completo**
- **Descrição**: Criar fluxo completo de upgrade para PRO
- **Funcionalidades**:
  - Landing page de features PRO
  - Comparação de planos
  - Checkout integrado
  - Confirmação de upgrade
  - Onboarding PRO
- **Arquivos**: 
  - `resources/js/Pages/Subscription/Upgrade.vue` (criar)
  - `app/Http/Controllers/SubscriptionController.php` (atualizar)
- **Prioridade**: 🔴 Alta (para monetização)
- **Estimativa**: 4-6 horas

#### 19. **Analytics Avançados**
- **Descrição**: Dashboard de analytics para usuários PRO
- **Funcionalidades**:
  - Métricas detalhadas de engajamento
  - Análise de padrões comportamentais
  - Comparação com outros usuários (anônimo)
  - Insights personalizados
- **Arquivos**: 
  - `app/Http/Controllers/AnalyticsController.php` (criar)
  - `resources/js/Pages/Analytics/Index.vue` (criar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 8-10 horas

#### 20. **Integração Strava/Nike (APIs)**
- **Descrição**: Importar dados de apps de fitness
- **Funcionalidades**:
  - OAuth com Strava/Nike
  - Importar atividades automaticamente
  - Sincronizar check-ins
- **Arquivos**: 
  - `app/Http/Controllers/OAuthController.php` (atualizar)
  - `app/Services/StravaService.php` (criar)
- **Prioridade**: 🟢 Baixa
- **Estimativa**: 10-12 horas

#### 21. **Notificações Push**
- **Descrição**: Enviar notificações push para lembrar check-ins
- **Funcionalidades**:
  - Configurar notificações
  - Lembretes diários
  - Notificações de conquistas
  - Notificações de progresso
- **Arquivos**: 
  - `app/Jobs/SendDailyReminderJob.php` (criar)
  - `app/Notifications/CheckinReminderNotification.php` (criar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 4-6 horas

---

## 🧪 Melhorias Técnicas e Qualidade

### 22. **Testes Automatizados**
- **Descrição**: Implementar testes unitários e de integração
- **Funcionalidades**:
  - Testes de controllers
  - Testes de models
  - Testes de jobs
  - Testes E2E (Playwright/Cypress)
- **Arquivos**: 
  - `tests/Feature/` (criar/atualizar)
  - `tests/Unit/` (criar/atualizar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 10-15 horas

#### 23. **Otimização de Performance**
- **Descrição**: Melhorar performance geral do sistema
- **Funcionalidades**:
  - Otimizar queries N+1
  - Implementar cache estratégico
  - Lazy loading de imagens
  - Code splitting no frontend
- **Prioridade**: 🟡 Média
- **Estimativa**: 6-8 horas

#### 24. **Melhorias de Segurança**
- **Descrição**: Revisar e melhorar segurança
- **Funcionalidades**:
  - Rate limiting mais robusto
  - Validação de uploads mais rigorosa
  - Sanitização de inputs
  - Auditoria de segurança
- **Prioridade**: 🟡 Média
- **Estimativa**: 4-6 horas

#### 25. **Documentação de API**
- **Descrição**: Documentar APIs para uso futuro
- **Funcionalidades**:
  - Documentar endpoints REST
  - Exemplos de uso
  - Postman collection
- **Arquivos**: 
  - `docs/API.md` (criar)
  - Scribe já configurado (atualizar)
- **Prioridade**: 🟢 Baixa
- **Estimativa**: 3-4 horas

---

## 📊 Resumo por Prioridade

### 🔴 Alta Prioridade (Fazer Agora)
1. Otimizar Performance do Cálculo de Streak
2. Implementar Invalidação de Cache
3. Webhook EvolutionAPI Funcional
4. Parser de Mensagens com Hashtags
5. Check-ins Automáticos via Foto + #hashtag
6. Sistema de Pagamentos Stripe
7. Upgrade Flow Completo

### 🟡 Média Prioridade (Próximas Semanas)
8. Melhorar Tratamento de Erros em Upload
9. Agendar Comando de Verificação
10. Página de Perfil Público
11. Página de Configurações
12. Relatórios Detalhados
13. Bot Responses Personalizadas
14. Testes com Usuários Reais
15. IA Analysis com OpenAI Vision
16. Analytics Avançados
17. Notificações Push
18. Testes Automatizados
19. Otimização de Performance
20. Melhorias de Segurança

### 🟢 Baixa Prioridade (Futuro)
21. Compartilhamento Nativo Mobile
22. PWA (Service Worker)
23. QR Code para Conexão
24. Integração Strava/Nike
25. Documentação de API

---

## 🎯 Próximos Passos Imediatos (Esta Semana)

1. ✅ **Corrigir bugs críticos** (JÁ FEITO)
2. 🔄 **Otimizar cálculo de streak** (2-3h)
3. 🔄 **Implementar invalidação de cache** (3-4h)
4. 🔄 **Melhorar tratamento de erros** (1-2h)
5. 🔄 **Agendar comando de verificação** (30min)

**Total estimado**: ~7-10 horas

---

## 📝 Notas

- **Estimativas** são aproximadas e podem variar
- **Prioridades** podem mudar baseado em feedback de usuários
- **Features PRO** são críticas para monetização
- **Integração WhatsApp** é diferencial competitivo

---

**Próxima revisão**: após concluir Google login + Stripe PRO

