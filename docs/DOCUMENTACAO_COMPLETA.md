# 📋 Documentação Técnica Completa - DOPA Check

## 🎯 **Status Atual - Beta Funcional**

**Versão**: 1.0-beta  
**Última Atualização**: 09/01/2026  
**Status**: ✅ **Core Web Funcional** (MVP **sem WhatsApp**) + ✅ **Google OAuth** + ✅ **Assinatura PRO (Stripe + Cashier)**

---

## 🆕 Atualizações recentes (Jan/2026)

- **Infra “core web” padronizada**: ambiente oficial com **MySQL + Redis** (WhatsApp isolado/adiado no MVP).
- **Home pós-login**: destino padrão após autenticação é **`/dopa`** (Fortify). A rota **`/dashboard`** existe por compatibilidade e **redireciona para `/dopa`**.
- **Páginas legais**: **Termos de Uso** e **Política de Privacidade** revisados em **pt-BR** e alinhados à marca **DOPA Check**.
- **SEO/Brand**: defaults globais de SEO (título, description, keywords, Open Graph e Twitter) padronizados para **DOPA Check** e `og.webp` em `public/images/og.webp`.
- **OAuth (Google)**: provider **ativo** e fluxo end-to-end com tratamento de conta existente/erros em pt-BR.
- **Monetização (Stripe + Cashier)**: planos **PRO mensal/anual** configurados; ponte de sincronização de status pós-checkout.
- **DX/UX**:
  - **Toasts globais** para feedback (sucesso/erro/info) durante navegação.
  - **GA4** com pageview para SPA (Inertia) via `VITE_GA_MEASUREMENT_ID`.
  - **Correções CSRF/XSRF**: melhoria de estabilidade contra erros 419/TokenMismatch.
  - **PWA/Service Worker**: melhorias de cache e proteções para não cachear HTML/navegação.

## ✅ Entregas concluídas — MVP “sem WhatsApp” (Jan/2026)

- [x] Padronização do projeto para **produção/beta** com DB oficial **MySQL** e cache/queue via **Redis**
- [x] Correções de **legal/branding** (marca DOPA Check + pt-BR) e limpeza de placeholders
- [x] **Login Social (Google)** (Socialite) end-to-end com regras de conta existente
- [x] **Assinatura PRO** via **Stripe + Cashier** (mensal/anual) + sincronização do status do usuário
- [x] **Docs sincronizadas** com o estado real (setup, DB, URLs/ports, roadmap)
- [x] Melhorias de **experiência**: toasts globais, tracking GA4, endurecimento CSRF/XSRF, melhorias no PWA

## 🏗️ **Arquitetura da Aplicação**

### **Stack Principal**
```
Frontend: Vue 3 + TypeScript + TailwindCSS + Inertia.js
Backend:  Laravel 12 + PHP 8.3 + MySQL + Redis
Queue:    Laravel Horizon + Redis
Server:   FrankenPHP (produção)
Build:    Vite + Bun
```

### **Estrutura de Diretórios**
```
dopacheck.com.br/
├── app/
│   ├── Http/Controllers/     # Controladores principais
│   ├── Models/              # Models Eloquent
│   ├── Jobs/                # Jobs assíncronos
│   ├── Services/            # Lógica de negócio
│   └── Console/Commands/    # Comandos Artisan
├── database/
│   ├── migrations/          # Estrutura do banco
│   ├── seeders/            # Dados iniciais
│   └── factories/          # Factories para testes
├── resources/
│   ├── js/
│   │   ├── Pages/          # Páginas Inertia.js
│   │   ├── Components/     # Componentes Vue
│   │   └── composables/    # Lógica reutilizável
│   └── views/              # Views Blade
└── routes/
    ├── web.php             # Rotas web
    └── api.php             # APIs
```

---

## 🗄️ **Banco de Dados**

### **Schema Principal**
```sql
-- Usuários (extendido)
users (
    id, name, email, password, avatar, phone, 
    plan ENUM('free', 'pro'), whatsapp_number,
    subscription_ends_at, created_at, updated_at
)

-- Desafios
challenges (
    id, title, description, duration_days,
    is_template BOOLEAN, is_public BOOLEAN, is_featured BOOLEAN,
    created_by, participant_count, category, difficulty,
    created_at, updated_at
)

-- Tasks do Desafio  
challenge_tasks (
    id, challenge_id, name, hashtag UNIQUE,
    description, order, is_required BOOLEAN,
    icon, color, created_at, updated_at
)

-- Participação do Usuário
user_challenges (
    id, user_id, challenge_id,
    status ENUM('active', 'completed', 'paused', 'abandoned'),
    started_at, completed_at, current_day,
    total_checkins, streak_days, completion_rate,
    created_at, updated_at
)

-- Check-ins
checkins (
    id, user_challenge_id, task_id,
    image_path, image_url, message,
    source ENUM('web', 'whatsapp'),
    status ENUM('pending', 'approved', 'rejected'),
    ai_analysis JSON, confidence_score,
    challenge_day, checked_at, deleted_at,
    created_at, updated_at
)

-- Sessões WhatsApp
whatsapp_sessions (
    id, user_id, phone_number, bot_number,
    session_id, is_active BOOLEAN,
    last_activity, message_count, checkin_count,
    created_at, updated_at
)
```

### **Relacionamentos Principais**
```php
User hasMany UserChallenges
User hasOne WhatsAppSession
Challenge hasMany ChallengeTasks
Challenge hasMany UserChallenges  
UserChallenge hasMany Checkins
Checkin belongsTo ChallengeTask
```

### **Índices Otimizados**
```sql
-- Performance crítica
INDEX(user_id, status) ON user_challenges
INDEX(hashtag) ON challenge_tasks  
INDEX(user_challenge_id, checked_at) ON checkins
INDEX(created_at, deleted_at) ON checkins -- TTL cleanup
INDEX(is_template, is_public, is_featured) ON challenges
UNIQUE(user_id, challenge_id) ON user_challenges
UNIQUE(user_challenge_id, task_id, challenge_day) ON checkins
```

---

## 🎮 **Controllers e Rotas**

### **Controladores Implementados**

#### **DopaController** - Dashboard Principal
```php
GET  /dopa                    # Dashboard com desafio atual
GET  /api/today-tasks         # Tasks do dia (AJAX)
GET  /api/quick-stats         # Estatísticas (AJAX)
```

#### **ChallengeController** - Gestão de Desafios
```php
GET  /challenges              # Lista com filtros
GET  /challenges/{id}         # Detalhes do desafio
GET  /challenges/create       # Criar novo desafio
POST /challenges              # Salvar desafio
POST /challenges/{id}/join    # Participar
POST /challenges/{id}/leave   # Sair do desafio
```

#### **CheckinController** - Sistema de Check-ins
```php
GET  /checkins               # Lista de check-ins
POST /checkins               # Criar check-in (com upload)
POST /api/quick-checkin      # Check-in rápido (AJAX)
DELETE /checkins/{id}        # Remover check-in
```

#### **WhatsAppController** - Integração WhatsApp
```php
GET  /whatsapp/connect       # Página de conexão
POST /whatsapp/connect       # Criar sessão
GET  /whatsapp/status        # Status da conexão
DELETE /whatsapp/disconnect  # Desconectar
GET  /api/whatsapp-status    # Status (AJAX)
POST /webhook/whatsapp       # Webhook EvolutionAPI
```

---

## 🎨 **Frontend - Vue 3 + Composition API**

### **Componentes Principais**

#### **Dashboard/Index.vue** - Tela Principal
- **Estado vazio**: Quando não há desafio ativo
- **Desafio ativo**: Tasks do dia, progresso, stats
- **Celebração**: Quando completa todas as tasks
- **WhatsApp**: Status de conexão e instruções
- **Auto-refresh**: Atualiza tasks a cada minuto

#### **Components/TaskCard.vue** - Card de Task
- **Estados visuais**: Pendente/concluído com cores
- **Check-in modal**: Upload de imagem + mensagem
- **Check-in rápido**: Sem imagem via AJAX
- **Info detalhada**: Horário, fonte, análise IA
- **Ações**: Marcar/desmarcar, remover

#### **Components/CheckinModal.vue** - Upload de Imagem
- **Drag & Drop**: Interface amigável
- **Preview**: Visualização antes do upload
- **Validação**: Tipo, tamanho máx 5MB
- **Progress**: Indicador de upload
- **Estados**: Loading, success, error

### **Composables (Lógica Reutilizável)**
```typescript
// useAuth.ts - Autenticação
const { user, isPro, isAuthenticated } = useAuth()

// useChallenges.ts - Gestão de desafios  
const { joining, joinChallenge, leaveChallenge } = useChallenges()

// useCheckins.ts - Sistema de check-ins
const { submitting, quickCheckin, uploadCheckin } = useCheckins()

// useApi.ts - HTTP Client
const { loading, get, post, delete } = useApi()
```

### **Estados Visuais Implementados**
- ✅ **Loading states** com spinners e skeleton
- ✅ **Empty states** com CTAs claros
- ✅ **Error states** com mensagens amigáveis
- ✅ **Success states** com celebrações
- ✅ **Mobile responsive** com breakpoints otimizados

---

## 🤖 **Integração WhatsApp (adiada no MVP atual)**

A integração com WhatsApp **não faz parte do escopo do MVP em produção/beta**. Ela existe como trilha de evolução e pode estar parcialmente presente no código/infra para experimentos, mas **não é considerada “feature entregue”** neste momento.

- **Documentação específica**: veja `docs/DOC_TEC_WHATSAPP_IA.md`

---

## 🔄 **Jobs e Background Processing**

### **Laravel Horizon - Queue Management**
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['whatsapp', 'images', 'default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
        ],
    ],
]
```

### **Jobs Implementados**
```php
// Core Jobs
ProcessWhatsAppMessage::class     // Webhook processing
GenerateShareCard::class          // Card para compartilhamento
CleanupExpiredImages::class       // TTL para Free users
UpdateChallengeStats::class       // Contadores de participantes

// Planejados
AnalyzeCheckinWithAI::class      // OpenAI Vision (PRO)
SendDailyReminder::class         // Notificações push
ProcessStripeWebhook::class      // Payments
```

---

## 📊 **Sistema de Planos**

### **Regras de Negócio**
```php
// Free Plan Limits
if ($user->plan === 'free') {
    // Máximo 1 desafio ativo
    if ($user->activeChallenges()->count() >= 1) {
        throw new PlanLimitException();
    }
    
    // TTL de 90 dias para imagens
    if ($checkin->created_at->diffInDays() > 90) {
        $checkin->update(['deleted_at' => now()]);
    }
}

// PRO Plan Benefits  
if ($user->plan === 'pro') {
    // Desafios ilimitados
    // IA automática
    // Storage permanente
    // Bot WhatsApp
}
```

### **Verificações de Permissão**
```php
// Middleware: CheckPlanLimits
class CheckPlanLimits {
    public function handle($request, $next, $feature) {
        $user = $request->user();
        
        if ($feature === 'multiple_challenges' && $user->plan === 'free') {
            return redirect()->back()->with('error', 'Upgrade para PRO');
        }
        
        return $next($request);
    }
}
```

---

## 🎨 **Geração de Cards para Compartilhamento**

### **Feature em Desenvolvimento**
```php
POST /api/share-card
{
  "challenge_id": 123,
  "day": 3,
  "total_days": 21,
  "title": "21 Dias de Leitura",
  "tasks": [
    {"name": "Ler 30min", "completed": true},
    {"name": "Beber 2L água", "completed": false}
  ]
}

// Response: PNG image download
Content-Type: image/png
Content-Disposition: attachment; filename="dopacheck-day-3.png"
```

### **Template Visual**
- **Header**: Logo DOPA Check + título do desafio
- **Progress**: Dia atual/total com barra visual
- **Tasks**: Lista com ícones ✅/❌ por status
- **Footer**: @dopacheck + URL da app
- **Branding**: Cores e fonts consistentes

---

## ⚡ **Performance e Otimização**

### **Cache Strategy**
```php
// User data cache (1 hora)
Cache::remember("user_active_challenge_{$userId}", 3600, function() {
    return UserChallenge::with('challenge.tasks')->active()->first();
});

// Challenge stats cache (30 min)
Cache::remember("challenge_stats_{$challengeId}", 1800, function() {
    return Challenge::with('participants')->find($challengeId);
});
```

### **Database Optimization**
```php
// Eager loading para N+1 queries
User::with(['activeChallenges.challenge.tasks', 'todayCheckins'])
    ->find($userId);

// Índices para queries frequentes
Schema::table('checkins', function (Blueprint $table) {
    $table->index(['user_challenge_id', 'checked_at']);
    $table->index(['created_at', 'deleted_at']); // TTL cleanup
});
```

### **Frontend Performance**
```javascript
// Auto-refresh inteligente (só quando necessário)
const refreshTasks = () => {
  if (currentChallenge.value && document.visibilityState === 'visible') {
    fetch('/api/today-tasks')
  }
}
setInterval(refreshTasks, 60000) // 1 minuto

// Optimistic updates para UX fluida
const markCompleted = async (taskId) => {
  // Update UI primeiro
  task.value.is_completed = true
  
  // Depois API
  await quickCheckin(taskId)
}
```

---

## 🧪 **Dados de Teste e Seeders**

### **Usuários Padrão**
```php
// Free user para testes
Email: free@test.com
Password: password
Plan: free
WhatsApp: 5511999998888

// PRO user completo
Email: rapha@raphael-martins.com  
Password: password
Plan: pro (até 2025)
WhatsApp: 5511948863848
```

### **Desafios Templates**
```php
ChallengeSeeder::class // 6 desafios populares
- 📚 21 Dias de Leitura (847 participantes)
- 🏃 30 Dias de Movimento (623 participantes)
- 🧘 14 Dias de Mindfulness (412 participantes)
- 📱 7 Dias Detox Digital (289 participantes)
- 🙏 21 Dias de Gratidão (334 participantes)
- ⏰ 14 Dias de Foco Total (456 participantes)
```

### **Setup Automático**
```bash
# Comando personalizado para setup completo
php artisan dopa:setup

# Executa:
# - Migrations
# - Seeders com dados realistas
# - Cache clearing
# - Optimization
# - Resumo visual
```

---

## 🔐 **Segurança**

### **Autenticação**
```php
// Laravel Fortify + Jetstream
- Login com email/senha
- Rate limiting (5 tentativas/min)
- Session management
- CSRF protection
```

### **Validação de Uploads**
```php
$request->validate([
    'image' => [
        'required',
        'image',
        'max:5120', // 5MB
        'mimes:jpeg,png,jpg,webp'
    ],
    'task_id' => [
        'required',
        'exists:challenge_tasks,id'
    ]
]);
```

### **Rate Limiting APIs**
```php
// WhatsApp webhook
RateLimiter::for('whatsapp', function (Request $request) {
    return Limit::perMinute(60)->by($request->input('data.key.remoteJid'));
});

// Upload de imagens  
RateLimiter::for('upload', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

---

## 🚀 **Roadmap de Desenvolvimento**

### ✅ **SPRINT 1 - Core Web (COMPLETO)**
- [x] Autenticação com login/senha
- [x] Models e migrations completos
- [x] Dashboard responsivo mobile-first
- [x] Sistema de desafios (CRUD completo)
- [x] Check-ins web com upload
- [x] Interface polida com TailwindCSS
- [x] Estados visuais (loading, empty, error)

### 🚧 **SPRINT 2 - Finalização Web (EM ANDAMENTO)**
- [x] Geração de cards para compartilhamento
- [ ] Páginas de perfil público (/u/username)
- [ ] Configurações de usuário
- [ ] Relatórios detalhados com métricas
- [ ] Compartilhamento nativo mobile
- [ ] PWA (Service Worker)

### 🎯 **PRÓXIMO (MVP sem WhatsApp por enquanto)**
- [x] **Login Social (Google)** end-to-end
- [x] **Assinatura PRO (Stripe + Cashier)** end-to-end
- [x] Higiene/consistência de docs (DB/URLs/roadmap)

### 🕒 **FUTURO (após Google + Stripe)**
- [ ] Integração WhatsApp (EvolutionAPI): do webhook/buffer até check-in automático
- [ ] IA Analysis (OpenAI Vision) como feature PRO

### 🌟 **FUTURO - Expansão (2-3 MESES)**
- [ ] App mobile nativo (React Native)
- [ ] Comunidades e grupos
- [ ] Gamificação (badges, rankings)
- [ ] Marketplace de desafios
- [ ] API pública para desenvolvedores
- [ ] Integração Google Fit / Apple Health

---

## 🎯 **Objetivos de Produto**

### **Métricas de Sucesso**
```javascript
// Engajamento
- Daily Active Users (DAU)
- Task completion rate > 70%
- Retention D7 > 60%
- Session time > 5min

// Conversão
- Free to PRO conversion > 15%
- Share card usage > 40%
- Challenge completion rate > 65%

// Performance
- Page load < 2s
- API response < 300ms
- Image upload < 10s
```

### **Validação de Mercado**
- **MVP**: 30 usuários do SalvadoPamina
- **Beta**: 100 usuários ativos diários
- **Launch**: 500 usuários e 50 PRO
- **Scale**: 1000+ usuários, ROI positivo

---

## 🛠️ **Comandos Úteis**

### **Desenvolvimento**
```bash
# Setup inicial
php artisan dopa:setup --fresh

# Desenvolvimento
php artisan serve
bun run dev
php artisan horizon

# Cache e otimização
php artisan optimize
php artisan view:cache
php artisan config:cache

# Queue management
php artisan queue:work
php artisan horizon:status
php artisan queue:failed
```

### **Deploy e Produção**
```bash
# Build para produção
bun run build
php artisan optimize

# Database
php artisan migrate --force
php artisan db:seed --force

# Cache refresh
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### **Debugging e Logs**
```bash
# Logs em tempo real
tail -f storage/logs/laravel.log

# Queue debugging
php artisan horizon:status
php artisan queue:monitor

# Cache inspection
php artisan cache:table
redis-cli KEYS "*"
```

---

## 🔧 **Configuração de Ambiente**

### **Variáveis Críticas (.env)**
```env
# App
APP_NAME="DOPA Check"
APP_URL=https://dopacheck.com.br
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=dopacheck
DB_USERNAME=root
DB_PASSWORD=

# Redis (essencial)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# OAuth (Google)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/callback/google"

# Stripe (Cashier)
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

> WhatsApp (opcional/futuro): `WHATSAPP_BOT_NUMBER` é usado pelo app para gerar link wa.me. As variáveis `EVOLUTION_*` são usadas principalmente no `docker-compose.whatsapp.yml` (EvolutionAPI).

### **Redis Configuration**
```php
// config/database.php
'redis' => [
    'client' => 'phpredis',
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 0, // Cache geral
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 1, // Cache específico
    ],
    'queue' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 2, // Jobs/Filas
    ],
]
```

---

## 🧩 **Principais Desafios Técnicos**

### **1. Integração WhatsApp**
**Problema**: EvolutionAPI webhook precisa ser robusto e confiável
**Solução**: Buffer de mensagens + jobs assíncronos + retry logic

### **2. Performance com Crescimento**
**Problema**: Queries N+1 e cache invalidation
**Solução**: Eager loading + cache estratégico + índices otimizados

### **3. UX Mobile-First**
**Problema**: Navegação complexa em tela pequena
**Solução**: Bottom navigation + gestures + estados visuais claros

### **4. Freemium Conversion**
**Problema**: Mostrar valor PRO sem ser invasivo
**Solução**: Limitations contextuais + benefits no momento certo

---

## 📚 **Recursos e Referências**

### **Documentação Técnica**
- [Laravel 12 Docs](https://laravel.com/docs)
- [Vue 3 Composition API](https://vuejs.org/api/composition-api-setup.html)
- [Inertia.js Guide](https://inertiajs.com/)
- [TailwindCSS Docs](https://tailwindcss.com/docs)
- [EvolutionAPI Docs](https://doc.evolution-api.com/)

### **Ferramentas de Desenvolvimento**
- **IDE**: PHPStorm ou VSCode
- **Database**: TablePlus ou Sequel Pro
- **API Testing**: Postman ou Insomnia
- **Design**: Figma
- **Monitoring**: Laravel Telescope + Sentry

### **Padrões e Boas Práticas**
- **PSR-12** para código PHP
- **Vue Style Guide** para componentes
- **Conventional Commits** para mensagens
- **Laravel Conventions** para nomenclatura
- **Mobile-First** para CSS

---

## 🎯 **Próximos Passos Imediatos**

### **Esta Semana (Sprint 2)**
1. ✅ **Finalizar geração de cards** compartilháveis
2. 🚧 **Implementar páginas de perfil** (/u/username)
3. 🚧 **Criar tela de configurações** do usuário
4. 🚧 **Desenvolver relatórios básicos** com métricas
5. 🚧 **Testar compartilhamento nativo** mobile

### **Próximas semanas**
1. 🎯 **Login Social (Google)** (UI + regras de conta existente)
2. 💳 **Stripe + Cashier** (plano PRO mensal + webhook + bridge de status)
3. 🧹 **Higiene de docs e consistência** (DB/URLs/nomes)

---

## 📊 **Métricas e Monitoring**

### **KPIs de Desenvolvimento**
```php
// Performance
- Response time < 200ms (95th percentile)
- Database queries < 10 per request
- Memory usage < 128MB per request
- Queue processing < 30s

// Qualidade
- Test coverage > 80%
- Code quality score > 8.5/10
- Zero critical security issues
- Uptime > 99.5%
```

### **Analytics de Produto**
```javascript
// Comportamento
track('challenge_joined', { challenge_id, source })
track('checkin_completed', { task_id, has_image, source })
track('share_card_generated', { challenge_id, day })
track('whatsapp_connected', { user_plan, success })

// Conversão
track('plan_upgraded', { from_plan, to_plan, price })
track('payment_completed', { amount, plan, trial_days })
track('feature_limited', { feature, plan_required })
```

---

## 🔄 **Fluxo de Deploy**

### **CI/CD Pipeline** (Futuro)
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      - name: Build assets
        run: bun install && bun run build
      - name: Deploy to server
        run: rsync -avz --delete . user@server:/var/www/dopacheck/
      - name: Run migrations
        run: ssh user@server "cd /var/www/dopacheck && php artisan migrate --force"
```

### **Deploy Manual**
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
bun install && bun run build

# 3. Update database
php artisan migrate --force

# 4. Clear caches
php artisan optimize
php artisan horizon:terminate

# 5. Restart services
sudo supervisorctl restart horizon
sudo systemctl reload php8.3-fpm
```

---

## 🎉 **Conclusão**

O **DOPA Check** representa uma solução inovadora para tracking de hábitos, combinando a familiaridade do WhatsApp com a robustez de uma aplicação web moderna. A arquitetura atual suporta:

### **✅ Já Funcional**
- ✅ **Web app completa** com UX polida
- ✅ **Sistema de desafios** robusto
- ✅ **Check-ins com upload** de imagem
- ✅ **Dashboard responsivo** mobile-first
- ✅ **Performance otimizada** com cache Redis
- ✅ **Arquitetura escalável** com jobs assíncronos

### **🚀 Próximos Marcos**
- 💎 **Versão PRO (Stripe)**: após fechar plano, checkout e sincronização
- 🎯 **WhatsApp**: após estabilizar Google + Stripe
- 📱 **100+ usuários ativos** em 2 meses
- 💰 **ROI positivo** em 3 meses

### **🎯 Objetivos de Negócio**
- **Validar PMF** (Product-Market Fit) na comunidade
- **Demonstrar skills FullStack** completas
- **Criar produto sustentável** financeiramente
- **Base para próximos projetos** e oportunidades

---

**📋 Documentação Técnica Completa - DOPA Check v1.0-beta**  
*Atualizada em: 07/01/2026*  
*Status: Core Web Funcional (WhatsApp adiado no MVP)*