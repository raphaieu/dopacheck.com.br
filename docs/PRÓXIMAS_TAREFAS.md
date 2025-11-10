# 📋 Próximas Tarefas - DOPA Check

**Última atualização**: 2025-01-XX  
**Status do Projeto**: Beta Funcional - Core Web Completo

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

## 🚀 Sprint 2 - Finalização Web (Em Andamento)

### ✅ Concluído
- [x] Geração de cards para compartilhamento
- [x] Correção de bugs críticos (current_day, progresso, stats)

### 📝 Pendente

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

## 📱 Sprint 3 - Integração WhatsApp (Próximo)

### 10. **Webhook EvolutionAPI Funcional**
- **Descrição**: Implementar webhook para receber mensagens do WhatsApp
- **Funcionalidades**:
  - Receber mensagens via POST `/webhook/whatsapp`
  - Validar assinatura/autenticação
  - Processar diferentes tipos de mensagem (texto, imagem, áudio)
- **Arquivos**: 
  - `app/Http/Controllers/WhatsAppController.php` (método `webhook()` já existe)
  - `app/Jobs/ProcessWhatsappBufferJob.php` (já existe)
- **Prioridade**: 🔴 Alta
- **Estimativa**: 4-6 horas

#### 11. **Parser de Mensagens com Hashtags**
- **Descrição**: Extrair hashtags e conteúdo das mensagens
- **Funcionalidades**:
  - Identificar hashtags (#leitura, #treino, etc)
  - Extrair texto da mensagem
  - Detectar imagens anexadas
  - Validar formato de mensagem
- **Arquivos**: 
  - `app/Services/WhatsappBufferService.php` (já existe)
  - `app/Services/WhatsappMessageParser.php` (criar)
- **Prioridade**: 🔴 Alta
- **Estimativa**: 3-4 horas

#### 12. **Check-ins Automáticos via Foto + #hashtag**
- **Descrição**: Criar check-in automaticamente quando receber foto + hashtag
- **Funcionalidades**:
  - Identificar task pela hashtag
  - Baixar imagem do WhatsApp
  - Criar check-in automaticamente
  - Validar se usuário tem desafio ativo
- **Arquivos**: 
  - `app/Jobs/ProcessWhatsappBufferJob.php` (atualizar)
  - `app/Services/WhatsappCheckinService.php` (criar)
- **Prioridade**: 🔴 Alta
- **Estimativa**: 4-5 horas

#### 13. **Bot Responses Personalizadas**
- **Descrição**: Enviar respostas automáticas ao usuário
- **Funcionalidades**:
  - Confirmação de check-in criado
  - Mensagens de erro (hashtag não encontrada, desafio inativo, etc)
  - Status do desafio (progresso, dias restantes)
  - Comandos especiais (/status, /help, etc)
- **Arquivos**: 
  - `app/Services/WhatsappBotResponseService.php` (criar)
  - `app/Jobs/SendWhatsappMessageJob.php` (criar)
- **Prioridade**: 🟡 Média
- **Estimativa**: 3-4 horas

#### 14. **QR Code para Conexão Fácil**
- **Descrição**: Gerar QR Code para conectar WhatsApp facilmente
- **Funcionalidades**:
  - Gerar QR Code com link wa.me
  - Exibir QR Code na página de conexão
  - Instruções de uso
- **Arquivos**: 
  - `app/Http/Controllers/WhatsAppController.php` (atualizar)
  - `resources/js/Pages/WhatsApp/Connect.vue` (atualizar)
- **Prioridade**: 🟢 Baixa
- **Estimativa**: 2-3 horas

#### 15. **Testes com Usuários Reais**
- **Descrição**: Testar integração WhatsApp com usuários reais
- **Funcionalidades**:
  - Recrutar beta testers
  - Coletar feedback
  - Ajustar baseado em feedback
- **Prioridade**: 🟡 Média
- **Estimativa**: Variável

---

## 💎 Sprint 4 - Features PRO (4-6 Semanas)

### 16. **Sistema de Pagamentos Stripe**
- **Descrição**: Integrar Stripe para assinaturas PRO
- **Funcionalidades**:
  - Checkout Stripe
  - Gerenciar assinaturas
  - Webhook de pagamentos
  - Upgrade/downgrade de plano
- **Arquivos**: 
  - `app/Http/Controllers/SubscriptionController.php` (já existe, atualizar)
  - `app/Models/User.php` (já tem Billable trait)
- **Prioridade**: 🔴 Alta (para monetização)
- **Estimativa**: 8-10 horas

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

**Última atualização**: 2025-01-XX  
**Próxima revisão**: Após conclusão das tarefas de alta prioridade

