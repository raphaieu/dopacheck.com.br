# 🧠 DOPA Check

<div align="center">

**Transforme seu smartphone em um tracker de hábitos inteligente**

*Plataforma web (mobile-first) para tracking de hábitos e desafios — com integração WhatsApp planejada para depois do core web*

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)

[🚀 Demo Live](https://dopacheck.com.br) • [📖 Documentação](#como-funciona) • [🛠️ Instalação](#instalação) • [🎯 Roadmap](#roadmap)

</div>

---

## 🧩 O Problema que Resolve

> **"Tentei vários apps (Strava, Notion, planilhas), mas nenhum centralizava meu progresso real de hábitos de forma simples. O DOPA Check nasceu para resolver isso com check-ins rápidos e um dashboard que dá vontade de voltar."**

**DOPA Check** nasceu da comunidade [Reservatório de Dopamina](https://t.me/reservatoriodedopamina), onde desafios como *"21 dias de leitura"* e *"30 dias sem açúcar"* são comuns, mas o acompanhamento era fragmentado entre múltiplos apps.

### 💡 A Solução
- ✅ **Zero fricção** - Check-ins rápidos pelo navegador (com ou sem imagem)
- ✅ **Check-in visual** - Uma foto vale mais que mil planilhas
- ✅ **Dashboard centralizado** - Veja tudo em um lugar
- ✅ **Compartilhamento automático** - Cards gerados para stories
- ✅ **Desafios comunitários** - Participe com outras pessoas

---

## 🚀 Status Atual - Beta Funcional

### ✅ **Já Funcionando**
- **🔐 Autenticação** completa (login/senha)
- **🔐 Login Social (Google OAuth)** end-to-end
- **📱 Dashboard mobile-first** responsivo e moderno
- **🏆 Sistema de desafios** completo (criar, participar, filtrar)
- **✅ Check-ins web** com ou sem imagem
- **👥 Participantes** e detalhes de desafios
- **🎯 Progresso visual** com anéis e estatísticas
- **📊 Estados dinâmicos** (ativo, concluído, pausado)
- **🎨 Interface polida** seguindo design system
- **💳 Assinatura PRO (Stripe + Cashier)** com planos mensal/anual configurados
- **🖼️ Geração de cards** para compartilhamento (download/uso em redes)

### 🚧 **Em Desenvolvimento**
- **👤 Páginas de perfil** e configurações
- **📈 Relatórios detalhados** com métricas
- **🎨 Compartilhamento nativo** mobile
- **🤖 Integração WhatsApp (EvolutionAPI)** (adiada; fora do escopo do MVP atual)

---

## 🎯 Como Funciona

### 🚀 **Fluxo Principal (3 cliques)**

```mermaid
graph TD
    A[📱 Acessa dopacheck.com.br] --> B[🔐 Login rápido]
    B --> C[🎯 Escolhe/Cria Desafio]
    C --> D[📋 Define Tasks Diárias]
    D --> E[🏠 Dashboard com Tasks]
    E --> F[📸 Check-in Web (com ou sem imagem)]
    F --> G[🎉 Progresso Atualizado!]
    G --> H[🎨 Compartilha Card Gerado]
```

### 📱 **Interface Atual**

#### **1. Dashboard Principal**
- **Progresso visual** com anel de completude
- **Tasks do dia** com status e check-ins
- **Estatísticas rápidas** (sequência, dias restantes)
- **Estado de celebração** quando completa o dia
- **Conexão WhatsApp** (planejada; pode existir UI/estrutura no código, mas não é o foco do MVP atual)

#### **2. Sistema de Desafios**
- **Catálogo completo** com filtros e categorias
- **Criação personalizada** com tasks customizadas
- **Detalhes ricos** (participantes, progresso, dificuldade)
- **Templates populares** (21 dias leitura, 30 dias exercício)

#### **3. Check-ins Inteligentes**
- **Upload de imagem** com drag & drop
- **Check-in rápido** sem imagem
- **Validação automática** (um por task/dia)
- **Fonte rastreada** (web/whatsapp)

---

## 🆓 Modelo Freemium

### **Versão Gratuita**
- ✅ **1 desafio ativo** simultâneo
- ✅ **Check-ins manuais** via web
- ✅ **Dashboard completo** com progresso
- ✅ **Participação** em desafios públicos
- ✅ **Compartilhamento básico** de progresso
- ✅ **Storage 90 dias** para imagens

### 🤖 **DOPA Check PRO** (Em breve)
- 🚀 **Desafios ilimitados** simultâneos
- 💳 **Assinatura PRO** via Stripe (Cashier)
- 🧠 **Recursos PRO** (IA/WhatsApp) entram após a base de pagamentos estar sólida
- 📊 **Relatórios avançados** com insights
- 💾 **Storage permanente** de todas as imagens
- 🎨 **Templates personalizados** de compartilhamento
- 📈 **Integração Strava/Nike** (futuro)

---

## 🛠️ Instalação e Setup

### Pré-requisitos
- PHP 8.3+
- Node.js 18+
- MySQL 8.0+
- Redis 6.0+
- Composer 2.0+
- Bun ou npm

### 🚀 Setup Rápido (Local, sem Docker)

```bash
# 1. Clone o repositório
git clone https://github.com/raphaieu/dopacheck.com.br.git
cd dopacheck.com.br

# 2. Instale dependências
composer install
bun install

# 3. Configure ambiente
cp env.example.dopacheck .env
php artisan key:generate

# 4. Configure banco de dados (.env)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dopacheck
DB_USERNAME=root
DB_PASSWORD=

# 5. Configure Redis (.env)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# 6. Execute migrations e seeders
php artisan migrate --seed

# 7. Build assets
bun run build

# 8. Inicie servidores (dev)
# Opção A (recomendado): tudo junto (serve + queue + logs + vite)
composer run dev
#
# Opção B (manual):
# php artisan serve        # Backend (http://localhost:8000)
# bun run dev              # Vite (http://localhost:5173)
# php artisan horizon      # Queue worker
```

### 🐳 Setup com Docker (recomendado para ambiente consistente)

```bash
# 1. Subir core web (app + mysql + redis + horizon)
docker compose up -d

# 2. Rodar migrations/seed dentro do container
docker compose exec app php artisan migrate --seed

# 3. Acessar
# App: http://localhost:8000
# phpMyAdmin (opcional): docker compose --profile tools up -d  -> http://localhost:8082
```

## 🧭 Rotas principais (Web)

- **Dashboard principal (pós-login)**: `/dopa`
- **Compatibilidade**: `/dashboard` existe apenas por legado e **redireciona para `/dopa`**
- **Desafios**: `/challenges`
- **Perfil público**: `/u/{username}`
- **Páginas legais**: Termos de Uso e Política de Privacidade (Jetstream) — `route('terms.show')` e `route('policy.show')`

### ⚙️ Configuração WhatsApp (Opcional)

```env
# Número do bot (formato: 5511999998888)
WHATSAPP_BOT_NUMBER=5511999998888
```

> Nota: existe um `docker-compose.whatsapp.yml` (EvolutionAPI + Postgres) para testes/experimentos. O webhook do DOPA fica em `POST /webhook/whatsapp` e hoje **apenas bufferiza eventos** (a criação automática de check-ins ainda não está fechada no MVP).

---

## 🧪 Dados de Teste

Após executar `php artisan migrate --seed`, você terá:

### **👤 Usuários de Teste**
```
🆓 Free User
Email: free@test.com
Senha: password

💎 PRO User  
Email: rapha@raphael-martins.com
Senha: password
```

### **🏆 Desafios Templates**
- 📚 **21 Dias de Leitura** (847 participantes)
- 🏃 **30 Dias de Movimento** (623 participantes)  
- 🧘 **14 Dias de Mindfulness** (412 participantes)
- 📱 **7 Dias Detox Digital** (289 participantes)
- 🙏 **21 Dias de Gratidão** (334 participantes)

---

## 📊 Stack Tecnológico

### **Backend**
- **Framework**: Laravel 12 (PHP 8.3+)
- **Database**: MySQL 8.0 com Redis para cache
- **Queue**: Laravel Horizon + Redis
- **Authentication**: Laravel Fortify + Jetstream
- **Storage**: Local (futuro: Cloudflare R2)

### **Frontend**
- **Framework**: Vue 3 + Composition API + TypeScript
- **Build**: Vite + Bun
- **Styling**: TailwindCSS + ShadCN components
- **Routing**: Inertia.js (SSR + SPA)
- **State**: Composables pattern

### **Integrações**
- **OAuth**: Socialite (Google)
- **Payments**: Stripe (Cashier) — em andamento
- **WhatsApp**: EvolutionAPI — adiado (infra/estrutura existe, mas sem fluxo end-to-end no MVP)
- **Analytics**: Implementação própria

---

## 📱 Features Técnicas

### **Performance**
- ⚡ **SSR + SPA** com Inertia.js
- 🔄 **Auto-refresh** inteligente das tasks
- 💾 **Cache estratégico** (Redis + Laravel)
- 📱 **Mobile-first** responsivo
- 🎯 **Optimistic updates** para UX fluida

### **Segurança**
- 🔐 **Autenticação robusta** com rate limiting
- 🛡️ **Validação completa** de uploads e dados
- 🔑 **CSRF protection** em todas as requests
- 📸 **Upload seguro** com validação de tipo/tamanho

### **Arquitetura**
- 🏗️ **Clean Architecture** com Services e Jobs
- 🔄 **Queue processing** para operações pesadas
- 📊 **Event-driven** para atualizações automáticas
- 🧩 **Modular** com composables e componentes

---

## 🤝 Como Contribuir

### 🛠️ **Áreas que Precisam de Ajuda**
- 🎨 **UI/UX**: Melhorias na interface mobile
- 🔐 **Login Social (Google)**: ajustes de UX e regras de vínculo de conta
- 💳 **Pagamentos (Stripe + Cashier)**: fluxo de upgrade/portal e sincronização de status
- 📊 **Analytics**: Dashboard de métricas
- 🧪 **Testing**: Testes automatizados
- 📖 **Documentação**: Exemplos e tutoriais

### 📋 **Process de Contribuição**
1. Fork o repositório
2. Crie uma branch: `git checkout -b feature/nova-feature`
3. Faça suas mudanças e commit: `git commit -m 'Add: nova feature'`
4. Push para a branch: `git push origin feature/nova-feature`
5. Abra um Pull Request

---

## 📞 Contato & Suporte

<div align="center">

### 👨‍💻 **Criado por Raphael Martins**

*FullStack Developer • Laravel Expert • Open Source Advocate*

[![GitHub](https://img.shields.io/badge/GitHub-raphaieu-000?logo=github)](https://github.com/raphaieu)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-raphaelmartins-0A66C2?logo=linkedin)](https://linkedin.com/in/raphaelmartins)
[![Twitter](https://img.shields.io/badge/Twitter-@raphaieu-1DA1F2?logo=twitter)](https://twitter.com/raphaieu)

**📧 Email**: [rapha@raphael-martins.com](mailto:rapha@raphael-martins.com)  
**🌐 Portfolio**: [raphai.eu](https://raphai.eu)

</div>

### 💼 **Desenvolvimento FullStack**
Disponível para projetos de **MVP**, **arquitetura de sistemas** e **integração de IA**.

---

## 📄 Licença

Este projeto está sob a licença **MIT**. Veja o arquivo [LICENSE](LICENSE) para detalhes.

**TL;DR**: Use livremente, mas mantenha os créditos! 😉

---

<div align="center">

**⭐ Se este projeto te inspirou, deixe uma estrela!**

*Desenvolvido em Salvador, BA 🇧🇷*

**#FullStack #Laravel #Vue #OpenSource #HabitTracker**

</div>