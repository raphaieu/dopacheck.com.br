# 🐳 DOPA Check - Docker Setup

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
# Copie o arquivo de exemplo
cp .env.example .env

# Edite o arquivo .env com suas configurações
nano .env
```



## 🏃‍♂️ Executando a Aplicação

### Desenvolvimento

#### Opção 1: Octane com Watch (Recomendado)
```bash
# Usa o mesmo servidor da produção com hot reload
docker-compose up -d

# Ver logs em tempo real
docker-compose logs -f app

# Parar todos os serviços
docker-compose down
```

#### Opção 2: Composer Dev (Larasonic padrão)
```bash
# Inclui Vite, queues, logs simultâneos
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Ver logs em tempo real
docker-compose -f docker-compose.yml -f docker-compose.dev.yml logs -f

# Parar todos os serviços
docker-compose -f docker-compose.yml -f docker-compose.dev.yml down
```

#### Opção 3: Produção Local
```bash
# Usa configuração de produção (sem override)
docker-compose -f docker-compose.yml up -d

# Ver logs
docker-compose logs -f
```

### Produção
```bash
# Build das imagens
docker-compose build

# Subir em background
docker-compose up -d

# Verificar status
docker-compose ps
```

### Com ferramentas adicionais (pgAdmin)
```bash
# Subir incluindo pgAdmin
docker-compose --profile tools up -d
```

## 🌐 Acessos

- **Aplicação Laravel**: http://localhost:8000
- **Evolution API**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5173 (apenas com composer dev)
- **pgAdmin**: http://localhost:8082 (se usar --profile tools)
- **PostgreSQL**: localhost:5432
- **Redis**: localhost:6379

## 🔄 Comparação dos Modos de Desenvolvimento

| Modo | Comando | Vantagens | Desvantagens | Uso Recomendado |
|------|---------|-----------|--------------|-----------------|
| **Octane Watch** | `php artisan octane:start --watch` | Mesmo servidor da produção, rápido, hot reload | Pode ser complexo para debug | Desenvolvimento geral |
| **Composer Dev** | `composer run dev` | Vite + queues + logs simultâneos, Larasonic padrão | Mais recursos, overkill | Frontend pesado |
| **Produção Local** | `php artisan octane:start` | Ambiente idêntico à produção | Sem hot reload | Testes finais |

## 🔧 Comandos Úteis

### Laravel
```bash
# Executar migrations
docker-compose exec app php artisan migrate

# Executar seeders
docker-compose exec app php artisan db:seed

# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Limpar cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Ver logs do Laravel
docker-compose exec app php artisan pail
```

### Horizon (Queue)
```bash
# Ver status do Horizon
docker-compose exec horizon php artisan horizon:status

# Pausar Horizon
docker-compose exec horizon php artisan horizon:pause

# Continuar Horizon
docker-compose exec horizon php artisan horizon:continue

# Terminar Horizon
docker-compose exec horizon php artisan horizon:terminate
```

### Evolution API
```bash
# Ver logs da Evolution API
docker-compose logs evolution-api

# Verificar health check
curl http://localhost:8080/health
```

### Banco de Dados
```bash
# Acessar PostgreSQL
docker-compose exec postgres psql -U dopacheck_user -d dopacheck

# Backup do banco
docker-compose exec postgres pg_dump -U dopacheck_user dopacheck > backup.sql

# Restaurar backup
docker-compose exec -T postgres psql -U dopacheck_user -d dopacheck < backup.sql
```

### Redis
```bash
# Acessar Redis CLI
docker-compose exec redis redis-cli

# Monitorar Redis
docker-compose exec redis redis-cli monitor
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
   docker-compose down -v
   docker-compose up -d
   ```

4. **Health checks falhando**
   ```bash
   # Verificar logs dos serviços
   docker-compose logs postgres
   docker-compose logs redis
   docker-compose logs evolution-api
   ```

### Logs detalhados
```bash
# Ver logs de todos os serviços
docker-compose logs

# Ver logs de um serviço específico
docker-compose logs app
docker-compose logs postgres
docker-compose logs redis
docker-compose logs evolution-api
docker-compose logs horizon
```

## 📊 Monitoramento

### Health Checks
Todos os serviços possuem health checks configurados:
- **App**: Verifica se a aplicação está respondendo em `/health`
- **PostgreSQL**: Verifica se o banco está pronto
- **Redis**: Verifica se o Redis está respondendo
- **Evolution API**: Verifica se a API está saudável
- **Horizon**: Verifica se o queue worker está funcionando

### Métricas
```bash
# Ver uso de recursos
docker stats

# Ver informações dos containers
docker-compose ps
```

## 🔒 Segurança

### Variáveis sensíveis
- Altere todas as senhas padrão no `.env`
- Use senhas fortes para `DB_PASSWORD` e `EVOLUTION_API_KEY`
- Configure `REDIS_PASSWORD` em produção

### Firewall
Em produção, considere:
- Expor apenas as portas necessárias
- Usar reverse proxy (Nginx/Traefik)
- Configurar SSL/TLS
- Implementar rate limiting

## 🚀 Deploy em Produção

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
# Configurar backup do PostgreSQL
# Configurar backup do Redis
# Configurar backup dos volumes
```

## 📝 Notas Importantes

- O Evolution API precisa ser configurado com um número de WhatsApp
- Configure webhooks no Evolution API para receber mensagens
- O Horizon processa as filas do Laravel
- pgAdmin é opcional e só é carregado com `--profile tools`
- Todos os dados são persistidos em volumes Docker 