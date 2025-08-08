# 📋 Documentação Técnica Completa - DOPA Check v1.0

## 🎯 **Resumo da Implementação**

Implementação completa da estrutura de banco de dados, models e controllers para o **DOPA Check** - Sistema de tracking de hábitos via WhatsApp + Web.

**Status**: ✅ **Sprint 1 COMPLETA** - Base funcional implementada

---

## 🗄️ **Estrutura do Banco de Dados**

### 📊 **Tabelas Principais**

#### **1. users (Extended)**
Extensão da tabela users existente com campos específicos do DOPA Check:

```sql
-- Novos campos adicionados via migration
username VARCHAR(255) UNIQUE NULL
plan VARCHAR(20) DEFAULT 'free'  -- 'free' | 'pro'
whatsapp_number VARCHAR(20) NULL
phone VARCHAR(20) NULL
subscription_ends_at TIMESTAMP NULL
preferences JSON NULL

-- Índices adicionados
INDEX(plan), INDEX(whatsapp_number), INDEX(subscription_ends_at)
```

#### **2. challenges**
Armazena desafios (templates oficiais + criados por usuários):

```sql
id BIGINT PRIMARY KEY
title VARCHAR(255)
description TEXT
duration_days INT DEFAULT 21
is_template BOOLEAN DEFAULT FALSE  -- Templates oficiais
is_public BOOLEAN DEFAULT TRUE
is_featured BOOLEAN DEFAULT FALSE  -- Destacados na home
created_by BIGINT NULL REFERENCES users(id)
participant_count INT DEFAULT 0   -- Cache de participantes
category VARCHAR(50)               -- fitness, mindfulness, etc
difficulty VARCHAR(20) DEFAULT 'beginner'
image_url VARCHAR(255)
tags JSON

-- Índices otimizados
INDEX(is_template, is_public), INDEX(is_featured, is_public), 
INDEX(participant_count), INDEX(category), INDEX(created_by)
```

#### **3. challenge_tasks**
Tasks de cada desafio com hashtags únicas:

```sql
id BIGINT PRIMARY KEY
challenge_id BIGINT REFERENCES challenges(id)
name VARCHAR(255)                  -- "Ler 30 minutos"
hashtag VARCHAR(50) UNIQUE         -- "leitura" (sem #)
description TEXT
order INT DEFAULT 0
is_required BOOLEAN DEFAULT TRUE
icon VARCHAR(10)                   -- Emoji
color VARCHAR(7) DEFAULT '#3B82F6' -- Hex color
validation_rules JSON              -- Regras para IA

-- Constraints únicos e índices
UNIQUE(hashtag), UNIQUE(challenge_id, hashtag)
INDEX(challenge_id, order), INDEX(hashtag)
```

#### **4. user_challenges**
Participação do usuário em desafios:

```sql
id BIGINT PRIMARY KEY
user_id BIGINT REFERENCES users(id)
challenge_id BIGINT REFERENCES challenges(id)
status ENUM('active', 'completed', 'paused', 'abandoned')
started_at TIMESTAMP
completed_at TIMESTAMP NULL
paused_at TIMESTAMP NULL
current_day INT DEFAULT 1
total_checkins INT DEFAULT 0
streak_days INT DEFAULT 0
best_streak INT DEFAULT 0
completion_rate DECIMAL(5,2) DEFAULT 0.00
stats JSON
notes TEXT

-- Constraint único importante
UNIQUE(user_id, challenge_id)  -- Um usuário por desafio ativo
INDEX(user_id, status), INDEX(challenge_id, status)
```

#### **5. checkins**
Check-ins das tasks com soft deletes (TTL para free users):

```sql
id BIGINT PRIMARY KEY
user_challenge_id BIGINT REFERENCES user_challenges(id)
task_id BIGINT REFERENCES challenge_tasks(id)
image_path VARCHAR(255)
image_url VARCHAR(255)
message TEXT
source ENUM('web', 'whatsapp')
status ENUM('pending', 'approved', 'rejected')
ai_analysis JSON                   -- Análise da IA (PRO)
confidence_score DECIMAL(3,2)      -- 0.00-1.00
challenge_day INT
checked_at TIMESTAMP
deleted_at TIMESTAMP NULL          -- Soft delete para TTL

-- Constraint único crítico
UNIQUE(user_challenge_id, task_id, challenge_day) -- Um check-in por task/dia
INDEX(user_challenge_id, checked_at), INDEX(created_at, deleted_at)
```

#### **6. whatsapp_sessions**
Sessões WhatsApp dos usuários:

```sql
id BIGINT PRIMARY KEY
user_id BIGINT REFERENCES users(id)
phone_number VARCHAR(20)
bot_number VARCHAR(20)
session_id VARCHAR(255) UNIQUE     -- EvolutionAPI session
instance_name VARCHAR(100)
is_active BOOLEAN DEFAULT TRUE
last_activity TIMESTAMP
connected_at TIMESTAMP
disconnected_at TIMESTAMP
metadata JSON
message_count INT DEFAULT 0
checkin_count INT DEFAULT 0

-- Constraints únicos
UNIQUE(user_id, phone_number), UNIQUE(session_id)
INDEX(user_id, is_active), INDEX(phone_number)
```

---

## 🏗️ **Models Implementados (100% Funcionais)**

### **1. User Model (Extended)**

#### **Relacionamentos Corrigidos:**
```php
// OAuth connections (corrigido)
public function oauthConnections(): HasMany

// Check-ins através de user challenges (corrigido)
public function checkins(): HasManyThrough

// Desafios ativos
public function activeChallenges(): HasMany

// Todas as participações
public function userChallenges(): HasMany

// Sessão WhatsApp
public function whatsappSession(): HasOne

// Desafios criados
public function createdChallenges(): HasMany
```

#### **Métodos de Negócio:**
```php
// Verificação de plano
public function getIsProAttribute(): bool

// URL pública (com fallback)
public function getPublicProfileUrlAttribute(): string

// Verificação de limites
public function canCreateChallenge(): bool

// Desafio atual
public function currentChallenge(): ?UserChallenge

// Estatísticas completas
public function calculateStats(): array

// Check-ins de hoje
public function todayCheckins(): HasMany
```

### **2. Challenge Model**

#### **Funcionalidades Completas:**
```php
// Estatísticas do desafio
public function getStats(): array

// Recomendações personalizadas
public static function getRecommendedForUser(User $user): Collection

// Gerenciamento de participantes
public function updateParticipantCount(): void
public function isUserParticipating(User $user): bool

// Scopes otimizados
public function scopePublic($query)
public function scopeFeatured($query)
public function scopePopular($query, int $limit = 10)
public function scopeSearch($query, string $search)
```

### **3. ChallengeTask Model**

#### **Funcionalidades para WhatsApp:**
```php
// Hashtags formatadas
public function getHashtagWithPrefixAttribute(): string  // #leitura
public function getFormattedHashtagAttribute(): string

// Verificações de check-in
public function hasUserCheckedInToday(User $user): bool
public function getUserCheckinForDay(User $user, int $day): ?Checkin

// Validação de conteúdo (IA)
public function validateContent(array $content): array

// Exemplo de uso WhatsApp
public function getExampleUsageAttribute(): string
```

### **4. UserChallenge Model**

#### **Controle de Estado:**
```php
public function complete(): void    // Auto-atualiza stats
public function pause(): void       // Pausa com timestamp
public function resume(): void      // Resume ajustando datas (corrigido)
public function abandon(): void     // Abandona e atualiza counters
```

#### **Sistema de Check-ins:**
```php
public function addCheckin(ChallengeTask $task, array $data): Checkin
public function updateStats(): void  // Atualiza streaks, completion rate
public function getMissingTasksForToday(): Collection
```

#### **Cálculos Automáticos:**
```php
public function getDaysRemainingAttribute(): int
public function getProgressPercentageAttribute(): float
public function getExpectedCheckinsAttribute(): int
```

### **5. Checkin Model (Relacionamentos Corrigidos)**

#### **Relacionamentos Funcionais:**
```php
// Usuário através de HasOneThrough (corrigido)
public function user(): HasOneThrough

// User challenge e task
public function userChallenge(): BelongsTo
public function task(): BelongsTo
```

#### **Funcionalidades:**
```php
// Aprovação/Rejeição
public function approve(): void
public function reject(string $reason = null): void

// IA Analysis (PRO)
public function addAiAnalysis(array $analysis): void

// Compartilhamento (corrigido)
public function getShareTextAttribute(): string

// Scopes úteis
public function scopeNeedsReview($query)
public function scopeFromWhatsapp($query)
```

### **6. WhatsAppSession Model**

#### **Controle de Sessão:**
```php
public function markAsConnected(string $botNumber = null): void
public function markAsDisconnected(): void
public function updateLastActivity(): void
public function incrementMessageCount(): void
public function incrementCheckinCount(): void
```

#### **Formatação e Links:**
```php
public function getFormattedPhoneNumberAttribute(): string
public function getWhatsappLinkAttribute(): ?string
public function getConnectionStatusAttribute(): string
```

---

## 🎮 **Controllers Implementados**

### **1. DopaController**
**Dashboard principal do DOPA Check**
```php
public function dashboard(Request $request): Response
// - Desafio atual do usuário
// - Tasks do dia com status
// - Estatísticas gerais
// - Desafios recomendados
// - Feed de atividades
```

### **2. ProfileController**
**Perfis públicos e configurações**
```php
public function public(string $username): Response       // Perfil público
public function settings(Request $request): Response     // Configurações
public function updateSettings(Request $request): RedirectResponse
public function stats(Request $request): Response        // Estatísticas detalhadas
```

### **3. ChallengeController**
**Gestão completa de desafios**
```php
public function index(Request $request): Response        // Listagem com filtros
public function show(Request $request, Challenge $challenge): Response
public function create(Request $request): Response|RedirectResponse
public function store(Request $request): RedirectResponse
public function join(Request $request, Challenge $challenge): RedirectResponse
public function leave(Request $request, Challenge $challenge): RedirectResponse
```

### **4. CheckinController**
**Sistema de check-ins web e AJAX**
```php
public function index(Request $request): Response        // Lista check-ins
public function store(Request $request): JsonResponse|RedirectResponse
public function destroy(Request $request, Checkin $checkin): RedirectResponse
public function quickCheckin(Request $request): JsonResponse  // AJAX
public function todayTasks(Request $request): JsonResponse    // API
```

### **5. WhatsAppController**
**Integração WhatsApp (estrutura pronta)**
```php
public function connect(Request $request): Response      // Página conexão
public function store(Request $request): RedirectResponse // Criar sessão
public function disconnect(Request $request): RedirectResponse
public function status(Request $request): JsonResponse   // Status AJAX
```

---

## 🛣️ **Rotas Organizadas**

### **Públicas (sem autenticação):**
```php
// Perfis públicos
GET /u/{username}                 -> ProfileController@public

// Desafios públicos
GET /challenges                   -> ChallengeController@index
GET /challenges/{challenge}       -> ChallengeController@show
```

### **Autenticadas:**
```php
// Dashboard principal
GET /dopa                         -> DopaController@dashboard

// Gestão de desafios
GET /challenges/create            -> ChallengeController@create
POST /challenges                  -> ChallengeController@store
POST /challenges/{id}/join        -> ChallengeController@join

// Check-ins
GET /checkins                     -> CheckinController@index
POST /checkins                    -> CheckinController@store

// WhatsApp
GET /whatsapp/connect             -> WhatsAppController@connect
POST /whatsapp/connect            -> WhatsAppController@store

// Perfil e configurações
GET /profile/settings             -> ProfileController@settings
GET /profile/stats                -> ProfileController@stats
```

### **API/AJAX:**
```php
// Quick actions
POST /api/quick-checkin           -> CheckinController@quickCheckin
GET /api/today-tasks              -> CheckinController@todayTasks
GET /api/whatsapp-status          -> WhatsAppController@status
```

---

## 🌱 **Dados Iniciais (Seeders)**

### **ChallengeSeeder - 6 Desafios Templates:**

1. **📚 21 Dias de Leitura**
   - Task: `#leitura` - Ler 30 minutos
   - 847 participantes simulados

2. **🏃‍♂️ 30 Dias de Movimento**
   - Tasks: `#treino` - Exercitar 30min, `#agua` - Beber 2L (opcional)
   - 623 participantes simulados

3. **🧘‍♀️ 14 Dias de Mindfulness**
   - Task: `#meditacao` - Meditar 10 minutos
   - 412 participantes simulados

4. **📱 7 Dias Detox Digital**
   - Tasks: `#detox` - Max 1h redes sociais, `#offline` - Atividade offline
   - 289 participantes simulados

5. **🙏 21 Dias de Gratidão**
   - Task: `#gratidao` - Escrever 3 gratidões
   - 334 participantes simulados

6. **⏰ 14 Dias de Foco Total**
   - Tasks: `#pomodoro` - 25min focado, `#organizacao` - Organizar workspace (opcional)
   - 456 participantes simulados

### **DatabaseSeeder - Usuários de Teste:**
```php
// Usuário PRO completo
rapha@raphael-martins.com (password: password)
- Plano: PRO até 2025
- WhatsApp: 5511948863848
- Username: raphaieu

// Usuário Free para testes
free@test.com (password: password)
- Plano: Free
- WhatsApp: 5511999998888
- Username: usuarioteste

// + 13 usuários adicionais (10 free, 3 pro)
```

---

## ⚡ **Comando de Setup Automatizado**

```bash
# Setup completo com resumo
php artisan dopa:setup

# Setup fresh (apaga tudo)
php artisan dopa:setup --fresh
```

**O comando executa:**
1. ✅ Migrations com verificação
2. ✅ Seeders com dados realistas  
3. ✅ Cache clearing
4. ✅ Optimization
5. ✅ Resumo visual dos dados criados
6. ✅ Próximos passos sugeridos

---

## 🏭 **Factories Completos**

**Todos os models possuem factories funcionais:**
- ✅ **ChallengeFactory** - Estados: template, featured, private
- ✅ **ChallengeTaskFactory** - Required/optional, cores e ícones
- ✅ **UserChallengeFactory** - Estados: active, completed, paused, abandoned
- ✅ **CheckinFactory** - Com/sem imagem, fontes, análise IA
- ✅ **WhatsAppSessionFactory** - Active/inactive, high activity

---

## 🔧 **Correções Implementadas**

### **Problemas Resolvidos:**
1. ✅ **User Model**: Relacionamento `oauthConnections()` adicionado
2. ✅ **User Model**: `checkins()` corrigido para `HasManyThrough`
3. ✅ **User Model**: `getPublicProfileUrlAttribute()` com fallback
4. ✅ **Checkin Model**: Relacionamento `user()` corrigido para `HasOneThrough`
5. ✅ **UserChallenge Model**: Método `resume()` corrigido (bug de lógica)
6. ✅ **Controllers**: Uso de `$request->user()` em vez de `auth()->id()`
7. ✅ **Controllers**: Tipos de retorno corretos (`Response|RedirectResponse`)

---

## 📊 **Performance e Otimização**

### **Índices Estratégicos:**
- **users**: plan, whatsapp_number, subscription_ends_at
- **challenges**: compostos para public/featured, participant_count
- **challenge_tasks**: hashtag (unique), challenge_id+order
- **user_challenges**: user_id+status, challenge_id+status
- **checkins**: user_challenge_id+checked_at, TTL cleanup
- **whatsapp_sessions**: user_id+is_active, phone_number

### **Constraints de Integridade:**
- ✅ **Hashtags únicas** globalmente (WhatsApp)
- ✅ **Um usuário por desafio** ativo
- ✅ **Um check-in por task/dia**
- ✅ **Uma sessão WhatsApp por usuário**

### **Otimizações de Query:**
```php
// Cache de dados frequentes
Cache::tags(['user', $userId])->remember("user_active_challenge_{$userId}", 3600, ...);

// Eager loading padrão
User::with(['activeChallenges.challenge.tasks', 'todayCheckins']);

// Scopes otimizados
Challenge::public()->featured()->popular(10)
```

---

## 🎯 **Regras de Negócio Implementadas**

### **Sistema de Planos:**
- **Free**: 1 desafio ativo, check-ins manuais, TTL 90 dias imagens
- **PRO**: Desafios ilimitados, IA automática, storage permanente

### **Sistema de Desafios:**
- Templates oficiais vs. criados por usuários
- Hashtags únicas para integração WhatsApp
- Participação controlada (um usuário por desafio)

### **Sistema de Check-ins:**
- Um check-in por task por dia (constraint)
- Soft delete para TTL de imagens (free users)
- Status de aprovação com IA (PRO)
- Origem rastreada (web/whatsapp)

### **Sistema WhatsApp:**
- Uma sessão por usuário
- Números únicos e bot atribuído
- Controle de atividade e métricas

---

## ✅ **Status Atual - Sprint 1 Completa**

### **✅ Implementado e Funcionando:**
- ✅ **Database**: 6 migrations otimizadas
- ✅ **Models**: 6 models com relacionamentos corretos
- ✅ **Controllers**: 5 controllers principais funcionais
- ✅ **Routes**: Sistema de rotas organizado
- ✅ **Seeders**: Dados iniciais realistas
- ✅ **Factories**: Testing completo
- ✅ **Commands**: Setup automatizado
- ✅ **Validations**: Regras de negócio implementadas
- ✅ **Performance**: Índices e otimizações

### **🔧 Testado e Validado:**
- ✅ Login/Dashboard funcionando
- ✅ Settings acessível (OAuth corrigido)
- ✅ Relacionamentos funcionais
- ✅ Seeders populando dados
- ✅ Controllers sem erros de tipo

---

## 🚀 **Próximas Etapas (Sprint 2)**

### **1. Frontend/Interface (PRIORIDADE):**
- 🎯 **Dashboard Vue** com tasks do dia
- 🎯 **Componentes de check-in** com upload
- 🎯 **Interface de desafios** (listar, criar, participar)
- 🎯 **Páginas de perfil** público e privado
- 🎯 **Componentes WhatsApp** (conexão, status)

### **2. Integração WhatsApp:**
- 📱 **Webhook EvolutionAPI**
- 📱 **Parser de mensagens** com hashtags
- 📱 **Check-ins automáticos** via foto + #hashtag
- 📱 **Bot responses** personalizadas

### **3. Features Avançadas:**
- 🤖 **Jobs IA** para análise de imagens (PRO)
- 📧 **Notificações** e lembretes
- 📊 **Analytics** e relatórios detalhados
- 💳 **Sistema de pagamentos** Stripe

---

## 🎯 **Arquivos de Configuração**

### **Migrations Criadas:**
```bash
add_dopa_check_fields_to_users_table.php
create_challenges_table.php
create_challenge_tasks_table.php
create_user_challenges_table.php
create_checkins_table.php
create_whatsapp_sessions_table.php
```

### **Models Criados/Atualizados:**
```bash
app/Models/User.php (extended)
app/Models/Challenge.php
app/Models/ChallengeTask.php  
app/Models/UserChallenge.php
app/Models/Checkin.php
app/Models/WhatsAppSession.php
app/Models/OauthConnection.php (se necessário)
```

### **Controllers Criados:**
```bash
app/Http/Controllers/DopaController.php
app/Http/Controllers/ProfileController.php
app/Http/Controllers/ChallengeController.php
app/Http/Controllers/CheckinController.php
app/Http/Controllers/WhatsAppController.php
```

### **Seeders:**
```bash
database/seeders/ChallengeSeeder.php
database/seeders/DatabaseSeeder.php (updated)
```

### **Commands:**
```bash
app/Console/Commands/SetupDopaCheckCommand.php
```

---

## 📝 **Notas de Desenvolvimento**

### **Decisões Arquiteturais:**
- **HasManyThrough** para relacionamentos indiretos (User -> Checkins)
- **Soft Deletes** para TTL de imagens (free users)
- **Unique Constraints** para garantir integridade
- **JSON Fields** para flexibilidade (stats, preferences, metadata)
- **Enum Fields** para estados controlados

### **Padrões Implementados:**
- **Repository Pattern** implícito nos models
- **Service Layer** nos controllers (métodos de negócio)
- **Observer Pattern** para events (booted methods)
- **Factory Pattern** para testing
- **Command Pattern** para setup

---

**📋 Documentação Técnica Completa - DOPA Check v1.0**  
*Gerado em: 04/07/2025*  
*Sprint 1 - Base Funcional Completa*  
*Próximo: Sprint 2 - Frontend/Interface*

## Stack Atualizada
- **PHP:** >= 8.3
- **Laravel:** 12
- **Node.js/Bun**
- **Redis** (para cache de sessões WhatsApp)

## Integração WhatsApp (Novo Fluxo)
- Apenas um número de WhatsApp (bot/agent) para toda a comunicação.
- O botão "Conectar WhatsApp" apenas abre conversa com o bot.
- O backend identifica o usuário pelo número do WhatsApp e valida permissões via cache/Redis.
- Se PRO, libera funções. Se não, incentiva upgrade.
- Não há múltiplas sessões EvolutionAPI.

## Resumo do Fluxo
1. Usuário abre conversa com o bot.
2. Envia mensagem.
3. Bot identifica e valida permissões (cache/banco).
4. Libera funções ou incentiva upgrade.