# 🐳 DOPA Check - Docker Setup (Core Web)

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🚀 Configuração Inicial

### 1. Clone o repositório
```bash
git clone <seu-repositorio>
cd dopacheck.com.br
```

### 2. Configure as variáveis de ambiente
```bash
# Copie o arquivo de exemplo (DOPA Check)
cp env.example.dopacheck .env

# Edite o arquivo .env com suas configurações
nano .env
```



## 🏃‍♂️ Executando a Aplicação

### Desenvolvimento

#### Opção 1: Docker Compose (Core Web)
```bash
# Sobe o core do produto (Web) com MySQL + Redis + Horizon
docker compose up -d

# Ver logs em tempo real
docker compose logs -f app

# Parar todos os serviços
docker compose down
```

#### Opção 2: Produção Local
```bash
# Usa configuração de produção (sem override)
docker compose -f docker-compose.yml up -d

# Ver logs
docker compose logs -f
```

### Produção
```bash
# Build das imagens
docker compose build

# Subir em background
docker compose up -d

# Verificar status
docker compose ps
```

### Com ferramentas adicionais (phpMyAdmin)
```bash
# Subir incluindo phpMyAdmin
docker compose --profile tools up -d
```

## 🌐 Acessos

### Desenvolvimento local (`docker compose up`)

O `docker-compose.override.yml` expõe portas no host (configuráveis via `.env`):

- **Aplicação Laravel**: http://localhost:${APP_PORT:-8000}
- **phpMyAdmin**: http://localhost:${FORWARD_PHPMYADMIN_PORT:-8082} (com `--profile tools`)
- **MySQL**: localhost:${FORWARD_DB_PORT:-3306}
- **Redis**: localhost:${FORWARD_REDIS_PORT:-6379}

Se a porta 8000 estiver ocupada, defina no `.env`: `APP_PORT=8001` (ou outra livre).

### Produção / Coolify

Nenhuma porta é publicada no host — o Traefik do Coolify roteia para o serviço `app` na rede interna (porta 8000 do container). MySQL e Redis ficam acessíveis apenas entre containers.

## 🔄 Comparação dos Modos de Desenvolvimento

| Modo | Comando | Vantagens | Desvantagens | Uso Recomendado |
|------|---------|-----------|--------------|-----------------|
| **Docker (Core Web)** | `docker compose up -d` | Ambiente consistente com MySQL + Redis + Horizon; portas via override | Mais pesado que rodar local | Desenvolvimento geral |
| **Docker + tools** | `docker compose --profile tools up -d` | Inclui phpMyAdmin | Mais serviços | Debug/inspeção |
| **Produção Local** | `docker compose -f docker-compose.yml up -d` | Sem bind de portas no host (como Coolify) | Sem acesso direto localhost | Testes finais |

## 🔧 Comandos Úteis

### Laravel
```bash
# Executar migrations
docker compose exec app php artisan migrate

# Executar seeders
docker compose exec app php artisan db:seed

# Gerar chave da aplicação
docker compose exec app php artisan key:generate

# Limpar cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Ver logs do Laravel
docker compose exec app php artisan pail
```

### Horizon (Queue)
```bash
# Ver status do Horizon
docker compose exec horizon php artisan horizon:status

# Pausar Horizon
docker compose exec horizon php artisan horizon:pause

# Continuar Horizon
docker compose exec horizon php artisan horizon:continue

# Terminar Horizon
docker compose exec horizon php artisan horizon:terminate
```

### Evolution API (WhatsApp) - opcional / fora do MVP atual
```bash
# A integração WhatsApp foi isolada em um compose separado (Sprint WhatsApp).
# Suba assim (recomendado: subir o core web primeiro para criar a network dopacheck-net):
docker compose up -d
docker compose -f docker-compose.whatsapp.yml up -d

# Ver logs:
docker compose -f docker-compose.whatsapp.yml logs -f evolution-api
```

Notas:
- O EvolutionAPI expõe por padrão `http://localhost:8080`.
- O webhook do DOPA está em `POST /webhook/whatsapp` (ver `routes/web.php`). Hoje esse endpoint **bufferiza eventos** e agenda processamento; o fluxo completo (check-in automático) ainda não é o foco do MVP.

### Banco de Dados
```bash
# Acessar MySQL
docker compose exec mysql mysql -u${DB_USERNAME:-dopacheck_user} -p${DB_PASSWORD:-dopacheck_pass} ${DB_DATABASE:-dopacheck}
```

### Redis
```bash
# Acessar Redis CLI
docker compose exec redis redis-cli

# Monitorar Redis
docker compose exec redis redis-cli monitor
```

## 🔍 Troubleshooting

### Problemas comuns

1. **Porta já em uso**
   ```bash
   # Verificar portas em uso
   netstat -tulpn | grep :8000
   
   # Parar processo que está usando a porta
   sudo kill -9 <PID>
   ```

2. **Permissões de arquivo**
   ```bash
   # Corrigir permissões
   sudo chown -R $USER:$USER .
   chmod -R 755 storage bootstrap/cache
   ```

3. **Volumes não criados**
   ```bash
   # Remover volumes e recriar
   docker compose down -v
   docker compose up -d
   ```

4. **Health checks falhando**
   ```bash
   # Verificar logs do core web (docker-compose.yml)
   docker compose logs mysql
   docker compose logs redis
   docker compose logs app
   docker compose logs horizon

   # Se estiver usando WhatsApp (docker-compose.whatsapp.yml)
   docker compose -f docker-compose.whatsapp.yml logs evolution-postgres
   docker compose -f docker-compose.whatsapp.yml logs evolution-api
   ```

### Logs detalhados
```bash
# Ver logs de todos os serviços
docker compose logs

# Ver logs de um serviço específico
docker compose logs app
docker compose logs redis
docker compose logs horizon
```

## 📊 Monitoramento

### Health Checks
Todos os serviços possuem health checks configurados:
- **App**: Verifica se a aplicação está respondendo em `/health`
- **MySQL**: Verifica se o banco está pronto
- **Redis**: Verifica se o Redis está respondendo
- **Horizon**: Verifica se o queue worker está funcionando

### Métricas
```bash
# Ver uso de recursos
docker stats

# Ver informações dos containers
docker compose ps
```

## 🔒 Segurança

### Variáveis sensíveis
- Altere todas as senhas padrão no `.env`
- Use senhas fortes para `DB_PASSWORD` (MySQL) e `EVOLUTION_API_KEY` (WhatsApp)
- Configure `REDIS_PASSWORD` em produção

### Firewall / portas

Em produção (Coolify), **não mapeie portas no compose** — o proxy (Traefik) cuida do roteamento. No dev local, use `APP_PORT`, `FORWARD_DB_PORT`, etc. no `.env` se alguma porta padrão estiver ocupada.

## 🚀 Deploy em Produção

### Coolify (recomendado)

Use o arquivo `docker-compose.coolify.yml`, otimizado para VPS com Coolify + Traefik:

| Serviço | Função |
|---------|--------|
| **app** | FrankenPHP + Octane (porta 8000 interna) |
| **horizon** | Processamento de filas |
| **scheduler** | Cron do Laravel (`schedule:work`) |
| **mysql** | Banco de dados (sem porta exposta) |
| **redis** | Cache, sessão e filas |

#### Passo a passo no Coolify

1. **Novo recurso** → Docker Compose → conecte o repositório Git
2. **Compose file**: `docker-compose.coolify.yml`
3. **Domínio**: configure `dopacheck.com.br` apontando para o serviço `app`, porta `8000`
4. **Variáveis de ambiente**: cole o `.env` de produção (mínimo abaixo)
5. **Deploy**

#### Variáveis obrigatórias no Coolify

```env
APP_KEY=base64:...
APP_URL=https://dopacheck.com.br

DB_DATABASE=dopacheck
DB_USERNAME=dopacheck_user
DB_PASSWORD=<senha-forte>
MYSQL_ROOT_PASSWORD=<senha-root-forte>

REDIS_PASSWORD=          # opcional, mas recomendado

STRIPE_KEY=...
STRIPE_SECRET=...
STRIPE_WEBHOOK_SECRET=...

SENTRY_LARAVEL_DSN=...
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
```

> **DB_HOST**, **REDIS_HOST** e drivers (`CACHE_STORE`, `QUEUE_CONNECTION`, etc.) já estão definidos no compose — não precisa sobrescrever.

#### Storage e symlink (`public/storage`)

O entrypoint (`docker/entrypoint.sh`) roda automaticamente no deploy:

- Cria diretórios em `storage/` e `bootstrap/cache`
- Ajusta permissões como usuário **appuser** (uid 1000, equivalente ao `www-data`)
- Executa `php artisan storage:link` sem root
- Roda migrations (`RUN_MIGRATIONS=true` por padrão)
- Gera caches de config/rota/view em produção

Volumes persistentes: `storage_data` e `bootstrap_cache`.

#### MySQL: um container por app ou compartilhado?

**Não é obrigatório** ter um MySQL separado para cada aplicação. São duas abordagens válidas:

| Abordagem | Quando usar |
|-----------|-------------|
| **MySQL por app** (padrão Coolify) | Isolamento, backup independente, deploy simples. **Recomendado** para produção. |
| **MySQL compartilhado** | VPS com pouca RAM (ex.: Oracle A1). Um único container MySQL com **bancos separados** (`dopacheck`, `outro_app`, etc.). |

No seu servidor já existem vários MySQL (`mysql-nisdi...`, `mysql-pzkd4...`, `db-j10h0...`). Cada um consome ~300–500 MB de RAM. Se a memória apertar, considere:

1. Criar um serviço MySQL standalone no Coolify
2. Remover o serviço `mysql` do `docker-compose.coolify.yml`
3. Apontar `DB_HOST` para o hostname do MySQL compartilhado na rede Docker

Para o DOPA Check, manter MySQL dedicado no compose é a opção mais segura e simples.

#### WhatsApp (Evolution API)

Continua em servidor separado — **não inclua** `docker-compose.whatsapp.yml` neste deploy. Apenas configure no `.env`:

```env
WHATSAPP_API_URL=https://seu-evolution.exemplo.com
WHATSAPP_API_KEY=...
```

---

### Produção local (sem Coolify)

```bash
docker compose -f docker-compose.yml up -d --build
```

### 1. Configurar variáveis de produção
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dopacheck.com.br
```

### 2. Configurar SSL
```bash
# Usar Nginx como reverse proxy
# Configurar certificados SSL
# Configurar headers de segurança
```

### 3. Backup automático
```bash
# Configurar backup do MySQL
# Configurar backup do Redis
# Configurar backup dos volumes
```

## 📝 Notas Importantes

- O Evolution API (WhatsApp) é opcional e está em `docker-compose.whatsapp.yml`
- Configure webhooks no Evolution API para receber mensagens quando chegar na Sprint WhatsApp
- O Horizon processa as filas do Laravel
- phpMyAdmin é opcional e só é carregado com `--profile tools`
- Todos os dados são persistidos em volumes Docker 