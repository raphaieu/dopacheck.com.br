## Jornada Infra: Evolution → Webhook → Filas → Storage (R2/S3) → Performance

Este documento descreve, em etapas, como evoluir a infra do DOPACheck para suportar picos de eventos no webhook do WhatsApp (Evolution API), reduzir risco operacional e preparar armazenamento de imagens em object storage (Cloudflare R2 / S3) com otimização (WebP) e expiração para usuários free.

---

## Objetivos

- **Estabilidade em pico**: suportar 600–800 eventos em poucos minutos sem travar o webhook.
- **Desacoplamento real**: webhook “fast-ack” e processamento pesado em fila.
- **Armazenamento escalável**: imagens públicas no R2/S3, expiração de free (30–60 dias).
- **Observabilidade**: detectar atraso de fila, falhas e degradação antes do usuário perceber.
- **Iterativo**: mudanças pequenas, com rollback possível.

---

## Estado atual (baseline)

- App em produção roda com **nginx + php-fpm** (sem Octane/FrankenPHP ativo).
- Webhook do WhatsApp existe em `POST/GET /webhook/whatsapp` (Laravel).
- Há Jobs para processamento (`ProcessWhatsappCheckinJob`, `ProcessWhatsappBufferJob`).
- Worker `php artisan queue:work` está rodando (lembrar de rodar `php artisan queue:restart` após deploy para recarregar código).
- Evolution agora roda em **VPS dedicada** e está integrada com **MinIO (S3)** para upload automático de mídia.
- Projeto já tem `docker-compose.whatsapp.yml` para subir Evolution + Postgres + Redis.

---

## Status / Progresso (até 2026-02-25)

### Concluído

- **Evolution em VPS**: rodando em produção com domínio (ex.: `whats.dopacheck.com.br`) e upload automático de mídia no MinIO.
- **MinIO/S3 com URL pública**: mídia disponível via `https://files.dopacheck.com.br/...`.
- **Webhook WhatsApp (Laravel) usando `mediaUrl`**:
  - Parser captura `mediaUrl` (incluindo formato `message.mediaUrl`) e passa ao Job.
  - Quando `mediaUrl` existe, **não salvamos nada em `storage/app/public`**.
  - O Job cria o check-in usando `**image_url` público** (strip da querystring) e mantém fallback legado.
- **Otimização de fast-ack**: quando `mediaUrl` existe, pulamos a query pesada `shouldSkipStorageBecauseAlreadyCheckedIn()` no webhook.

### Em progresso / pendente

- **Desligar base64 em produção** (Evolution): ideal manter `webhookBase64=false` para reduzir payload e risco de gargalo.
- **Garantir instância única de produção**: remover/desativar instâncias antigas (ex.: local) para não gerar ruído/duplicidade no webhook.
- **Reações em grupo**: validar reactions em `@g.us` com `participant_jid` (DM está OK).

---

## Princípios (importantes)

1. **Webhook sempre rápido**: ideal < 200ms p95 e com pouco trabalho síncrono.
2. **Fila é obrigatória**: sem fila isolada, pico vira queda.
3. **Idempotência**: reentregas e reenvios não podem gerar duplicidade.
4. **Separação de ambientes**: Dev/test com tunnel, Prod com domínio e infraestrutura própria.
5. **Mudanças com checkpoints**: cada etapa termina com validação simples.

---

## Arquitetura alvo (visão geral)

### Produção (recomendado)

- `dopacheck.com.br` (Laravel)
  - expõe `/webhook/whatsapp`
  - enfileira Jobs
  - consome filas com workers/Horizon
  - salva imagens em R2/S3 e grava `image_url` no banco
- `whats.dopacheck.com.br` (Evolution API)
  - roda em VPS (docker-compose)
  - aponta o webhook para `https://dopacheck.com.br/webhook/whatsapp`
  - **privado por firewall allowlist** (ideal), mesmo com DNS público

### Local (dev/test)

- Evolution local + webhook apontando para tunnel:
  - `WEBHOOK_GLOBAL_URL=https://<seu-tunnel>/webhook/whatsapp`
- Isso permite testar sem mexer na infraestrutura de produção.

---

## Etapa 1 — Subir Evolution em VPS (produção)

### 1.1. Provisionamento

- Criar uma VPS dedicada (recomendado) para Evolution:
  - 2–4 vCPU, 4–8GB RAM (depende do volume e concorrência)
  - disco suficiente (instâncias/logs)
- Instalar Docker + docker compose plugin
- Ajustar timezone, swap (opcional), limites de arquivo (ulimits) se necessário.

### 1.2. DNS / TLS

- Criar `A record`:
  - `whats.dopacheck.com.br` → IP da VPS Evolution
- Reverse proxy:
  - Nginx/Caddy na frente para TLS e rate-limit
  - Ideal: expor só 80/443 para o mundo

### 1.3. Segurança mínima (recomendado)

- Objetivo: Evolution “parece público” (tem domínio), mas **só o backend acessa**.

Firewall (conceito):

- Permitir **80/443** de qualquer lugar (apenas proxy).
- Permitir acesso à API interna (porta do container) **somente** do IP da VPS do Laravel, ou bindar em `127.0.0.1` e só proxy expõe.

Checklist:

- Redis/Postgres não expostos publicamente
- API da Evolution não fica aberta sem controle (ideal: allowlist)
- API key forte e guardada em secret/env (não versionar)

### 1.4. Subir containers (compose)

Usar como base `docker-compose.whatsapp.yml`.

Parâmetros importantes:

- `EVOLUTION_API_URL` (base URL usada pela app)
- `EVOLUTION_API_KEY` (auth)
- `WEBHOOK_GLOBAL_URL` apontando para produção:
  - `https://dopacheck.com.br/webhook/whatsapp`

Validação:

- Healthcheck ok (`/` da Evolution responde)
- endpoint de webhook dispara requisições pro Laravel
- logs sem loops de erro (retries constantes)

---

## Etapa 2 — Estratégia “prod privado + dev via tunnel” (boa prática)

### Produção

- Evolution em VPS (com domínio `evo.dopacheck.com.br`).
- Webhook configurado para `https://dopacheck.com.br/webhook/whatsapp`.
- Acesso à API da Evolution:
  - Preferir allowlist do IP do backend.

### Desenvolvimento/Testes

- Evolution local (docker) apontando o webhook para o tunnel:
  - `WEBHOOK_GLOBAL_URL=https://<tunnel>/webhook/whatsapp`
- Manter instâncias separadas:
  - `EVOLUTION_INSTANCE` diferente em dev e prod.
- Manter API keys diferentes por ambiente.

Benefícios:

- Prod não depende do seu PC
- Dev continua rápido

---

## Etapa 3 — Fila e Workers (pré-requisito do pico)

### 3.1. Escolha do backend de fila

- Recomendado: **Redis + Horizon**
- Evitar: `database` queue para alto throughput (pode virar gargalo no MySQL)

### 3.2. Horizon

- Instalar/configurar (já está no composer).
- Definir supervisores:
  - filas separadas (futuro): `whatsapp_ingest`, `whatsapp_media`, `default`
  - aumentar `maxProcesses` em produção conforme carga

Validação:

- Horizon acessível (protegido) e mostrando jobs
- workers sob supervisão (systemd/supervisor)
- retries/timeouts adequados (especialmente media download/convert)

Alertas mínimos:

- oldest job age > X s
- falhas/min > Y
- backlog crescendo por N minutos

---

## Etapa 4 — Webhook “fast-ack” e robustez

### 4.1. Meta

- Webhook deve:
  - validar + extrair dados mínimos
  - enfileirar
  - responder 200 OK rapidamente
- Nada de:
  - conversão de imagem
  - gravação pesada em disco
  - múltiplas queries custosas no request

Status atual:

- ✅ Quando `mediaUrl` existe, o webhook não grava nada em disco e evita query pesada.
- ⏳ Próximo: autenticar o webhook e reduzir duplicidade de enfileiramento (dedupe).

### 4.2. Idempotência (já existe parte)

- Garantir dedupe por `message_id` (já existe coluna `whatsapp_message_id`)
- Garantir constraint de “1 check-in por task/dia” (já existe unique)
- Melhorar locks/buffer:
  - evitar `Cache::get` + `Cache::put` concorrente
  - usar lock atômico/estrutura atômica no Redis para buffer

### 4.3. Segurança do webhook

- Adicionar secret/token de webhook
- Rate-limit com burst (não matar os 300 check-ins das 20h)
- Filtrar eventos: aceitar somente eventos esperados

Recomendação prática (próximo passo):

- Adicionar `WHATSAPP_WEBHOOK_SECRET` no Laravel e validar no início do webhook (header ou token no URL).
- Bloquear/permitir por firewall/IP (opcional, mas recomendado) para aceitar apenas a VPS da Evolution.

Validação:

- 800 req em poucos minutos sem degradar p95
- reenvios não geram duplicidade
- falhas são visíveis no Sentry/logs

---

## Etapa 5 — Storage (Cloudflare R2 / S3) + WebP + Expiração

### 5.1. Decisão

- Imagens serão **públicas** (OK).
- Free expira em 30–60 dias (por prefixo).
- Recomendado: **Cloudflare R2** (custo/egress), mas S3 também serve.

Status atual:

- ✅ Implementado MinIO (S3) acoplado à Evolution, gerando `mediaUrl` público em `files.dopacheck.com.br`.
- ⏳ Próximo: decidir se **uploads via Web** (formulário de check-in) também vão para S3/MinIO (recomendado).

### 5.2. Bucket e URL pública

- Criar bucket: `dopacheck-checkins`
- Domínio: `img.dopacheck.com.br` (CNAME para R2/S3/CDN)
- Organização de paths:
  - `free/checkins/whatsapp/YYYY-MM-DD/{uuid}.webp`
  - `pro/checkins/whatsapp/YYYY-MM-DD/{uuid}.webp` (se quiser retenção maior)

### 5.3. Lifecycle (expiração)

- Regra: `free/` expira após 60 dias (ou 30).
- (Opcional) mover `pro/` para cold storage depois de X dias.

### 5.4. Pipeline de imagem (assíncrono)

- Converter para WebP + resize + compress no Job:
  - max lado: 1280px (exemplo)
  - qualidade: 75–82 (exemplo)
- Gravar no R2/S3 e salvar `image_url` no banco.
- (Opcional) deletar original/local após sucesso.

Observação:

- Hoje a Evolution está salvando como JPEG/PNG no MinIO. A conversão para WebP pode ser feita depois (ou via regra/pipeline separado) sem bloquear o lançamento.

Validação:

- imagem típica cai de tamanho significativamente
- URL abre rápido e estável
- expiração do prefixo free funciona

---

## Etapa 6 — Teste de carga (antes do desafio público)

### Cenário

- 300 check-ins às 20h (imagens com hashtag)
- 200 enviam imagem
- 100 erram hashtag e reenviam
- total 600–800 eventos em minutos

### Métricas de sucesso

- Webhook p95 < 200ms (ajustável)
- backlog da fila zera em X minutos
- taxa de erro baixa e alertada
- 0 duplicidade no check-in
- storage cresce dentro do esperado

Ferramentas possíveis

- k6 / artillery / vegeta (HTTP)
- scripts simples para replay de payloads

---

## Etapa 7 — CI/CD e runtime (só depois)

- Melhorar deploy manual para “release + symlink” (rollback rápido).
- Quando estabilizar:
  - GitHub Actions
  - Dockerização do app (opcional)
- Octane/FrankenPHP:
  - só considerar após estabilidade do webhook+fila
  - pode melhorar latência, mas adiciona complexidade operacional

---

## Checkpoints de Go/No-Go

1. Evolution em VPS + webhook prod ok por 48h
2. Filas e workers estáveis + Horizon monitorando
3. Webhook fast-ack (sem trabalho pesado)
4. Storage R2/S3 com lifecycle e WebP
5. Teste de carga aprovado
6. Abrir desafio público

---

## Notas operacionais (curtas)

- Não expor Postgres/Redis publicamente.
- Sempre separar secrets por ambiente.
- Sentry: habilitar DSN e alertas (pelo menos errors + queue failures).

---

## Próximos passos (prioridade sugerida)

1. **Segurança do webhook (alta prioridade)**  
  - Secret/token no webhook + (ideal) firewall allowlist para IP da VPS da Evolution.
2. **Reduzir ruído e duplicidade (média/alta)**  
  - Dedupe em cache por `message_id` (60–120s) no controller para não enfileirar repetido.
  - Garantir apenas uma instância/servidor Evolution enviando para produção.
3. **Observabilidade (alta)**  
  - Sentry/alertas + monitor de fila (se for Redis/Horizon) + logs com `request()->ip()` no webhook.
4. **Filas/Horizon (média)**  
  - Migrar para Redis + Horizon para throughput e dashboards (pré-pico).
5. **Uploads Web → S3/MinIO (média)**  
  - Migrar `CheckinController` (upload via web) para usar disk `s3` e gravar `image_url` estável.

---

