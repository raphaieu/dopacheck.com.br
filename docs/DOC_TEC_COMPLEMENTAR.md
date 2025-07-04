# 📋 Documentação Técnica Complementar - DOPA Check

## 🎯 **Especificações Técnicas Definidas**

### 🔐 **Autenticação e Autorização**
- **Provider**: Laravel Socialite (GitHub, Google + extensível para outras redes)
- **Base**: Laravel Fortify + Jetstream já configurados
- **Middleware**: Verificação de plano (Free/PRO) em rotas sensíveis
- **Sessão**: Standard Laravel + Redis para cache de verificações

### 🗄️ **Arquitetura de Banco de Dados**

#### **Estrutura Principal**
```sql
-- Usuários (já existente - User.php)
users (
    id, name, email, avatar, phone, plan, 
    whatsapp_number, subscription_ends_at, created_at, updated_at
)

-- Desafios Templates/Criados
challenges (
    id, title, description, duration_days, 
    is_template, is_public, created_by, 
    participant_count, created_at, updated_at
)

-- Tasks do Desafio
challenge_tasks (
    id, challenge_id, name, hashtag, 
    description, order, created_at, updated_at
)

-- Participação do Usuário
user_challenges (
    id, user_id, challenge_id, status, 
    started_at, completed_at, current_day, 
    created_at, updated_at
)

-- Check-ins das Tasks
checkins (
    id, user_challenge_id, task_id, 
    image_path, ai_analysis, status, 
    checked_at, deleted_at, created_at, updated_at
)

-- Configurações WhatsApp
whatsapp_sessions (
    id, user_id, phone_number, session_id, 
    is_active, last_activity, created_at, updated_at
)
```

#### **Relacionamentos**
- **User** hasMany **UserChallenges**
- **Challenge** hasMany **ChallengeTasks**, **UserChallenges**
- **UserChallenge** hasMany **Checkins**
- **Checkin** belongsTo **ChallengeTask**

#### **Regras de Negócio**
```php
// Validação no Controller/Service
if ($user->plan === 'free' && $user->activeChallenges()->count() >= 1) {
    throw new PlanLimitException('Upgrade para PRO para múltiplos desafios');
}

// TTL para imagens (Free: 90 dias)
if ($user->plan === 'free') {
    Checkin::where('created_at', '<', now()->subDays(90))
          ->whereNull('deleted_at')
          ->update(['deleted_at' => now()]);
}
```

### 🤖 **Integração WhatsApp - EvolutionAPI**

#### **Webhook Strategy**
```php
// Middleware: VerifyWhatsAppUser
class VerifyWhatsAppUser {
    public function handle($request, $next) {
        $phone = $request->input('data.key.remoteJid');
        
        // Cache Redis (5min TTL)
        $user = Cache::remember("whatsapp_user_{$phone}", 300, function() use ($phone) {
            return User::where('whatsapp_number', $phone)->first();
        });
        
        if (!$user) {
            // Enviar mensagem de cadastro
            return response()->json(['status' => 'unregistered']);
        }
        
        $request->merge(['whatsapp_user' => $user]);
        return $next($request);
    }
}
```

#### **Identificação de Contexto**
```php
// Service: WhatsAppMessageParser
class WhatsAppMessageParser {
    public function parseMessage($messageData) {
        $text = $messageData['message']['conversation'] ?? '';
        $hasImage = isset($messageData['message']['imageMessage']);
        
        // Detectar hashtags (#leitura, #treino, etc)
        preg_match_all('/#(\w+)/', $text, $hashtags);
        
        return [
            'type' => $hasImage ? 'checkin' : 'text',
            'hashtags' => $hashtags[1] ?? [],
            'image_url' => $hasImage ? $this->downloadImage($messageData) : null,
            'raw_text' => $text
        ];
    }
}
```

### 🎨 **Frontend - Mobile First**

#### **Stack Confirmada**
- **Framework**: Vue 3 + Composition API + TypeScript
- **Build**: Vite + Bun
- **Styling**: TailwindCSS + Componentes ShadCN
- **State**: Pinia (para estado global)
- **Routing**: Inertia.js (já configurado)

#### **Estrutura de Componentes**
```
resources/js/
├── Pages/
│   ├── Auth/
│   │   ├── Login.vue
│   │   └── Register.vue
│   ├── Dashboard/
│   │   ├── Index.vue (Home com tasks do dia)
│   │   ├── ChallengeSelector.vue
│   │   └── Reports.vue
│   ├── Challenges/
│   │   ├── Create.vue
│   │   ├── Show.vue
│   │   └── Join.vue
│   └── Profile/
│       ├── Public.vue (/u/username)
│       └── Settings.vue
├── Components/
│   ├── UI/ (ShadCN components)
│   ├── TaskCard.vue
│   ├── ProgressRing.vue
│   ├── CheckinModal.vue
│   ├── ShareModal.vue
│   └── WhatsAppConnect.vue
└── Composables/
    ├── useAuth.ts
    ├── useChallenges.ts
    ├── useCheckins.ts
    └── useWhatsApp.ts
```

### 🔄 **Jobs e Background Processing**

#### **Jobs Essenciais**
```php
// Jobs Queue
ProcessWhatsAppMessage::class     // Webhook processing
GenerateShareImage::class         // Card para compartilhamento  
AnalyzeCheckinImage::class        // IA análise (PRO)
SendDailyReminder::class          // Lembretes opcionais
CleanupExpiredImages::class       // TTL soft delete
UpdateChallengeStats::class       // Contadores de participantes
```

#### **Configuração de Queues**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
]

// Horizon para monitoramento
'whatsapp' => [
    'connection' => 'redis',
    'queue' => ['whatsapp', 'default'],
    'balance' => 'auto',
    'processes' => 3,
    'tries' => 3,
]
```

### 📊 **Performance e Otimização**

#### **Estratégias de Cache**
```php
// Cache de dados frequentes
Cache::tags(['user', $userId])->remember("user_active_challenge_{$userId}", 3600, function() {
    return UserChallenge::active()->with('challenge.tasks')->first();
});

// Cache de contadores
Cache::remember("challenge_participants_{$challengeId}", 1800, function() {
    return UserChallenge::where('challenge_id', $challengeId)->count();
});
```

#### **Otimizações de Query**
```php
// Eager Loading padrão
User::with(['activeChallenges.challenge.tasks', 'todayCheckins'])
    ->find($userId);

// Índices necessários
Schema::table('checkins', function (Blueprint $table) {
    $table->index(['user_challenge_id', 'checked_at']);
    $table->index(['created_at', 'deleted_at']); // TTL cleanup
});
```

### 🔒 **Segurança e Validação**

#### **Rate Limiting**
```php
// Webhook WhatsApp
RateLimiter::for('whatsapp', function (Request $request) {
    return Limit::perMinute(60)->by($request->input('data.key.remoteJid'));
});

// Upload de imagens
RateLimiter::for('image_upload', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

#### **Validação de Upload**
```php
$request->validate([
    'image' => ['required', 'image', 'max:5120', 'mimes:jpeg,png,jpg'], // 5MB
    'hashtag' => ['required', 'string', 'exists:challenge_tasks,hashtag'],
]);
```

### 🎯 **Roadmap de Desenvolvimento**

#### **Sprint 1 (Semana 1-2): Core Web**
1. ✅ **Autenticação**: Socialite GitHub/Google
2. ✅ **Models**: User, Challenge, UserChallenge, Checkin
3. ✅ **Migrations**: Estrutura completa do BD
4. ✅ **Dashboard**: Interface principal (mobile-first)
5. ✅ **CRUD Challenges**: Criar/Participar desafios

#### **Sprint 2 (Semana 3): Checkins Web**
1. ✅ **Upload**: Sistema de upload de imagens
2. ✅ **Checkin Logic**: Marcar tasks como concluídas
3. ✅ **Progress**: Visualização de progresso
4. ✅ **TTL Jobs**: Cleanup de imagens (Free)
5. ✅ **Perfil Público**: `/u/username` compartilhável

#### **Sprint 3 (Semana 4): WhatsApp Bot**
1. ✅ **Webhook**: Receber mensagens EvolutionAPI
2. ✅ **Parser**: Identificar hashtags + imagens
3. ✅ **Auto Checkin**: Processar via WhatsApp
4. ✅ **Bot Responses**: Confirmações automáticas
5. ✅ **Conectar Web**: Integração perfil ↔ bot

#### **Sprint 4 (Semana 5-6): Polish & PRO**
1. ✅ **Share Images**: Geração automática de cards
2. ✅ **IA Analysis**: OpenAI Vision para PRO
3. ✅ **Subscription**: Stripe + upgrade flow
4. ✅ **Analytics**: Dashboard de insights
5. ✅ **Testing**: Testes com usuários reais

### 📱 **API Endpoints**

#### **Autenticação**
```
GET  /auth/github/redirect
GET  /auth/github/callback
GET  /auth/google/redirect  
GET  /auth/google/callback
POST /logout
```

#### **Web App**
```
GET  /dashboard
GET  /challenges
POST /challenges
GET  /challenges/{id}/join
POST /checkins
GET  /u/{username}
```

#### **WhatsApp Webhook**
```
POST /api/webhook/whatsapp
GET  /api/user/whatsapp/connect
POST /api/user/whatsapp/disconnect
```

#### **Compartilhamento**
```
GET  /api/share/image/{userId}
POST /api/share/generate
```

### 🧪 **Testing Strategy**

#### **Testes Unitários**
```php
// Feature Tests
test('user can create challenge')
test('user can join challenge')  
test('user can checkin task')
test('free user limited to 1 challenge')
test('pro user unlimited challenges')

// WhatsApp Tests  
test('webhook processes image checkin')
test('bot identifies correct hashtag')
test('unregistered user gets signup message')
```

### 🚀 **Deploy e Infraestrutura**

#### **Ambiente de Produção**
- **Servidor**: VPS + FrankenPHP (já configurado)
- **Database**: MySQL 8.0+
- **Cache**: Redis 6.0+
- **Queue**: Horizon + Redis
- **Storage**: Local → Cloudflare R2 (futuro)
- **SSL**: Cloudflare
- **Monitoring**: Sentry (já configurado)

---

## ✅ **Próximos Passos Imediatos**

1. **Configurar Socialite** (GitHub + Google)
2. **Criar Models** e Migrations
3. **Implementar Dashboard** mobile-first
4. **Sistema de Checkins** web
5. **Integrar WhatsApp** webhook

**Status**: ✅ Documentação completa - Pronto para desenvolvimento!

---

*Documentação técnica complementar gerada para início do desenvolvimento do DOPA Check*