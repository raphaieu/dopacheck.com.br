# 🧠 DOPA Check

<div align="center">

**Transforme seu smartphone em um tracker de hábitos inteligente**

*O primeiro sistema que unifica WhatsApp + Web para tracking de hábitos sem fricção*

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)
[![WhatsApp](https://img.shields.io/badge/WhatsApp-Bot-25D366?logo=whatsapp)](https://whatsapp.com)

[🚀 Demo Live](https://dopacheck.com.br) • [📖 Documentação](#como-funciona) • [🛠️ Instalação](#instalação) • [🎯 Roadmap](#roadmap)

</div>

---

## 🧩 O Problema que Resolve

> **"Tentei vários apps (Strava, Notion, planilhas), mas nenhum centralizava meu progresso real de hábitos de forma simples. O DOPA Check nasceu para resolver isso: uma foto via WhatsApp e pronto!"**

**DOPA Check** nasceu da comunidade [Reservatório de Dopamina](https://t.me/reservatoriodedopamina), onde desafios como *"21 dias de leitura"* e *"30 dias sem açúcar"* são comuns, mas o acompanhamento era fragmentado entre múltiplos apps.

### 💡 A Solução
- ✅ **Zero apps extras** - Use seu WhatsApp + navegador
- ✅ **Check-in visual** - Uma foto vale mais que mil planilhas
- ✅ **Dashboard centralizado** - Veja tudo em um lugar
- ✅ **Compartilhamento automático** - Cards gerados para stories
- ✅ **IA inteligente** - Analisa suas fotos automaticamente (PRO)
- ✅ **Desafios comunitários** - Participe com outras pessoas

---

## 🚀 Status Atual - Beta Funcional

### ✅ **Já Funcionando**
- **🔐 Autenticação** completa (login/senha)
- **📱 Dashboard mobile-first** responsivo e moderno
- **🏆 Sistema de desafios** completo (criar, participar, filtrar)
- **✅ Check-ins web** com ou sem imagem
- **👥 Participantes** e detalhes de desafios
- **🎯 Progresso visual** com anéis e estatísticas
- **📊 Estados dinâmicos** (ativo, concluído, pausado)
- **🎨 Interface polida** seguindo design system

### 🚧 **Em Desenvolvimento**
- **🤖 Bot WhatsApp** (integração EvolutionAPI pronta)
- **🖼️ Geração de cards** para compartilhamento
- **👤 Páginas de perfil** e configurações
- **📈 Relatórios detalhados** com métricas
- **🎨 Compartilhamento nativo** mobile

---

## 🎯 Como Funciona

### 🚀 **Fluxo Principal (3 cliques)**

```mermaid
graph TD
    A[📱 Acessa dopacheck.com.br] --> B[🔐 Login rápido]
    B --> C[🎯 Escolhe/Cria Desafio]
    C --> D[📋 Define Tasks Diárias]
    D --> E[🏠 Dashboard com Tasks]
    E --> F[📸 Check-in Web ou WhatsApp]
    F --> G[🎉 Progresso Atualizado!]
    G --> H[🎨 Compartilha Card Gerado]
```

### 📱 **Interface Atual**

#### **1. Dashboard Principal**
- **Progresso visual** com anel de completude
- **Tasks do dia** com status e check-ins
- **Estatísticas rápidas** (sequência, dias restantes)
- **Estado de celebração** quando completa o dia
- **Conexão WhatsApp** (pronto para integração)

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
- 🤖 **Bot WhatsApp** com IA automática
- 🧠 **Análise IA** de imagens e dados
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

### 🚀 Setup Rápido

```bash
# 1. Clone o repositório
git clone https://github.com/raphaieu/dopacheck.com.br.git
cd dopacheck.com.br

# 2. Instale dependências
composer install
bun install

# 3. Configure ambiente
cp .env.example .env
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

# 8. Inicie servidores
php artisan serve          # Backend (port 8000)
php artisan horizon:start  # Queue worker
```

### ⚙️ Configuração WhatsApp (Opcional)

```env
# EvolutionAPI para WhatsApp Bot
EVOLUTION_BASE_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua_api_key

# Número do bot (formato: 5511999998888)
WHATSAPP_BOT_NUMBER=5511999998888
```

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
- **WhatsApp**: EvolutionAPI (webhook pronto)
- **IA**: OpenAI Vision API (PRO)
- **Payments**: Stripe (futuro)
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
- 🤖 **Integração WhatsApp**: Testes do webhook
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
**💬 WhatsApp**: [(11) 94886-3848](https://wa.me/5511948863848)  
**🌐 Portfolio**: [raphai.eu](https://raphai.eu)

</div>

### 💼 **Desenvolvimento FullStack**
Disponível para projetos de **MVP**, **arquitetura de sistemas**, **integração de IA** e **aplicações WhatsApp**.

---

## 📄 Licença

Este projeto está sob a licença **MIT**. Veja o arquivo [LICENSE](LICENSE) para detalhes.

**TL;DR**: Use livremente, mas mantenha os créditos! 😉

---

<div align="center">

**⭐ Se este projeto te inspirou, deixe uma estrela!**

*Desenvolvido com ❤️ em Salvador, BA 🇧🇷*

**#FullStack #Laravel #Vue #WhatsApp #OpenSource #HabitTracker**

</div>