# 📁 Estrutura da Aplicação - dopacheck.com.br

## 🏠 **Diretório Raiz**
```
dopacheck.com.br/
├── 📄 README.md (15KB, 445 linhas)
├── 📄 LICENSE.md (1.0KB, 22 linhas)
├── 📄 .gitignore (436B, 38 linhas)
├── 📄 .gitattributes (186B, 12 linhas)
├── 📄 .editorconfig (258B, 19 linhas)
├── 📄 .phpstorm.meta.php (343KB, 4339 linhas)
├── 📄 _ide_helper.php (1.0MB)
├── 📄 artisan (350B, 16 linhas)
├── 📄 frankenphp (65MB)
├── 📄 Dockerfile (2.3KB, 79 linhas)
├── 📄 docker-compose.yml (6.5KB, 246 linhas)
├── 📄 docker-compose.override.yml (428B, 22 linhas)
├── 📄 rector.php (1.2KB, 40 linhas)
├── 📄 phpstan.neon (527B, 24 linhas)
├── 📄 phpunit.xml (1.2KB, 37 linhas)
├── 📄 pint.json (2.4KB, 100 linhas)
├── 📄 components.json (455B, 21 linhas)
├── 📄 tsconfig.json (12KB, 129 linhas)
├── 📄 vite.config.ts (651B, 30 linhas)
├── 📄 postcss.config.js (69B, 6 linhas)
├── 📄 eslint.config.js (282B, 13 linhas)
├── 📄 package.json (1.7KB, 63 linhas)
├── 📄 bun.lock (189KB, 1715 linhas)
├── 📄 composer.json (3.4KB, 121 linhas)
├── 📄 composer.lock (521KB, 14451 linhas)
```

## 🔧 **Diretórios de Configuração e Desenvolvimento**
```
├── 📁 .github/ (GitHub Actions/Workflows)
├── 📁 .git/ (Controle de versão)
├── 📁 .scribe/ (Documentação da API)
├── 📁 node_modules/ (Dependências Node.js)
├── 📁 vendor/ (Dependências PHP/Composer)
├── 📁 bootstrap/ (Arquivos de inicialização Laravel)
├── 📁 storage/ (Arquivos de armazenamento)
├── 📁 tests/ (Testes automatizados)
├── 📁 docs/ (Documentação)
```

## ⚙️ **Configurações**
```
├── 📁 config/
│   ├── 📄 app.php (4.2KB, 129 linhas)
│   ├── 📄 auth.php (4.0KB, 119 linhas)
│   ├── 📄 database.php (6.1KB, 176 linhas)
│   ├── 📄 cache.php (3.4KB, 111 linhas)
│   ├── 📄 session.php (7.7KB, 220 linhas)
│   ├── 📄 queue.php (3.8KB, 115 linhas)
│   ├── 📄 mail.php (3.5KB, 118 linhas)
│   ├── 📄 logging.php (4.2KB, 135 linhas)
│   ├── 📄 filesystems.php (2.4KB, 80 linhas)
│   ├── 📄 sanctum.php (3.0KB, 89 linhas)
│   ├── 📄 fortify.php (5.3KB, 163 linhas)
│   ├── 📄 jetstream.php (2.6KB, 83 linhas)
│   ├── 📄 filament.php (2.9KB, 92 linhas)
│   ├── 📄 cashier.php (4.4KB, 130 linhas)
│   ├── 📄 subscriptions.php (1.1KB, 37 linhas)
│   ├── 📄 oauth.php (1.0KB, 39 linhas)
│   ├── 📄 services.php (1.7KB, 60 linhas)
│   ├── 📄 scribe.php (12KB, 274 linhas)
│   ├── 📄 telescope.php (6.7KB, 210 linhas)
│   ├── 📄 sentry.php (6.0KB, 132 linhas)
│   ├── 📄 sitemap.php (1.5KB, 60 linhas)
│   ├── 📄 octane.php (6.8KB, 223 linhas)
│   ├── 📄 prism.php (1.4KB, 42 linhas)
│   ├── 📄 ide-helper.php (9.9KB, 319 linhas)
│   └── 📄 blasp.php (34KB, 1456 linhas)
```

## 🗄️ **Banco de Dados**
```
├── 📁 database/
│   ├── 📄 database.sqlite (0.0B, 0 linhas)  # artefato/legado (o banco oficial é MySQL)
│   ├── 📄 .gitignore (10B, 2 linhas)
│   ├── 📁 factories/ (Factories para testes)
│   ├── 📁 migrations/ (Migrações do banco)
│   └── 📁 seeders/ (Seeders para dados iniciais)
```

## 🛣️ **Rotas**
```
├── 📁 routes/
│   ├── 📄 web.php (1.8KB, 50 linhas)
│   ├── 📄 api.php (198B, 9 linhas)
│   └── 📄 console.php (191B, 12 linhas)
```

## 🎨 **Frontend e Recursos**
```
├── 📁 public/ (Arquivos públicos)
├── 📁 resources/
│   ├── 📁 css/ (Estilos CSS)
│   ├── 📁 js/
│   │   ├── 📄 app.js (950B, 34 linhas)
│   │   ├── 📄 bootstrap.js (125B, 6 linhas)
│   │   ├── 📁 Pages/ (Páginas Inertia.js)
│   │   ├── 📁 components/ (Componentes React/Vue)
│   │   ├── 📁 layouts/ (Layouts)
│   │   ├── 📁 lib/ (Bibliotecas)
│   │   └── 📁 composables/ (Composables)
│   ├── 📁 views/ (Views Blade)
│   └── 📁 markdown/ (Arquivos Markdown)
```

## 🏗️ **Aplicação Principal**
```
├── 📁 app/
│   ├── 📁 Actions/ (Actions/Use Cases)
│   ├── 📁 Console/ (Comandos Artisan)
│   ├── 📁 Enums/ (Enumerações)
│   ├── 📁 Exceptions/ (Exceções customizadas)
│   ├── 📁 Filament/ (Painel administrativo)
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/ (Controladores)
│   │   └── 📁 Middleware/ (Middleware)
│   ├── 📁 Jobs/ (Jobs em fila)
│   ├── 📁 Models/ (Modelos Eloquent)
│   │   ├── 📄 User.php (6.6KB, 186 linhas)
│   │   ├── 📄 Team.php (2.7KB, 92 linhas)
│   │   ├── 📄 TeamInvitation.php (1.7KB, 54 linhas)
│   │   ├── 📄 Membership.php (1.3KB, 39 linhas)
│   │   ├── 📄 OauthConnection.php (2.7KB, 72 linhas)
│   │   └── 📄 LoginLink.php (2.5KB, 78 linhas)
│   ├── 📁 Notifications/ (Notificações)
│   ├── 📁 Policies/ (Políticas de autorização)
│   ├── 📁 Providers/ (Service Providers)
│   └── 📁 Traits/ (Traits PHP)
```

## 📊 **Resumo da Aplicação**

Esta é uma aplicação **Laravel** moderna com as seguintes características:

### 🛠️ **Stack Tecnológico**
- **Backend**: Laravel com PHP
- **Frontend**: Inertia.js + Vue 3
- **Autenticação**: Laravel Fortify + Jetstream
- **Painel Admin**: Filament
- **Pagamentos**: Laravel Cashier
- **Documentação API**: Scribe
- **Monitoramento**: Sentry
- **Containerização**: Docker + Docker Compose
- **Servidor**: FrankenPHP
- **Build Tools**: Vite + Bun
- **Qualidade de Código**: PHPStan + Pint + ESLint
- **Testes**: PHPUnit
- **Banco**: MySQL (padrão) + Redis (cache/fila/sessão)

### 🎯 **Funcionalidades Identificadas**
- Sistema de autenticação robusto
- Gestão de equipes e convites
- Sistema de assinaturas/pagamentos
- Integração OAuth
- Painel administrativo completo
- API documentada
- Sistema de notificações
- Jobs em fila
- Monitoramento e logs

### 📁 **Arquivos Principais**
- **README.md**: Documentação principal (15KB)
- **composer.json**: Dependências PHP
- **package.json**: Dependências Node.js
- **docker-compose.yml**: Configuração Docker
- **artisan**: CLI do Laravel
- **frankenphp**: Servidor web

### 🔍 **Observações**
- Aplicação bem estruturada seguindo padrões Laravel
- Uso de ferramentas modernas de desenvolvimento
- Configuração completa para produção
- Sistema de equipes implementado
- Integração com serviços externos (OAuth, pagamentos)

---

*Documento gerado automaticamente - Estrutura da aplicação dopacheck.com.br* 