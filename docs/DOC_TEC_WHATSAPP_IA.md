# DOPA Check - Integração WhatsApp + IA

## Visão Geral
Este documento descreve o fluxo completo de processamento de mensagens recebidas via WhatsApp, utilizando IA para análise de texto e imagem, enriquecimento de dados e interação inteligente com o usuário.

---

## 0. Arquivos/Fonte da Feature

- **Controller:**
  - `app/Http/Controllers/WhatsAppController.php`
- **Services:**
  - `app/Services/WhatsappSessionService.php` (gerenciamento de sessão no Redis)
  - `app/Services/WhatsappBufferService.php` (bufferização de mensagens e agendamento de jobs)
- **Jobs:**
  - `app/Jobs/ProcessWhatsappBufferJob.php` (processamento do buffer de mensagens)
- **Configuração:**
  - `config/database.php` (configuração de Redis)
  - `.env` (variáveis de conexão Redis, número do bot)
- **Documentação:**
  - `docs/DOC_TEC_WHATSAPP_IA.md` (este arquivo)

---

## 1. Fluxo Geral da Feature

1. Usuário envia mensagens (texto, mídia, etc) para o bot do DOPA Check.
2. O backend bufferiza todas as mensagens recebidas em uma janela de tempo (ex: 10 segundos) no Redis.
3. Após o timeout, dispara um job para processar o "pacote" de mensagens.
4. O job processa o pacote:
   - Se houver mídia, envia para o agente de IA de imagens e extrai dados.
   - Se houver texto, envia para o agente de IA de texto/sentimento.
   - Salva dados extraídos e interage com o usuário de forma personalizada.
5. Todos os dados são salvos para histórico, relatórios e evolução do usuário.

---

## 2. Diagrama do Fluxo

```mermaid
graph TD
    A[Usuário envia mensagens (texto/mídia)] --> B[Bufferização no Redis]
    B --> C[Timeout (ex: 10s) sem novas mensagens]
    C --> D[Disparo do Job de Processamento]
    D --> E{Contém mídia?}
    E -- Sim --> F[Envia para IA de Imagem]
    F --> G[Extrai dados da imagem]
    G --> H[Salva dados estruturados]
    E -- Não --> I
    H --> I[Envia texto + dados para IA de Texto]
    I --> J[IA de Texto interpreta, responde e interage]
    J --> K[Salva histórico, relatórios, evolução]
    K --> L[Bot responde ao usuário]
```

---

## 3. Estrutura dos Dados no Redis

### Buffer de Mensagens
Chave: `whatsapp_buffer:{numero}`
Valor (JSON):
```json
[
  {"type": "text", "content": "Hoje fiz meu treino", "timestamp": "2024-07-04T10:00:00Z"},
  {"type": "image", "content": "file_id_123.jpg", "timestamp": "2024-07-04T10:00:05Z"}
]
```
TTL: 2 minutos

### Lock/Timer para Job
Chave: `whatsapp_buffer_lock:{numero}`
Valor: `true`
TTL: 10 segundos

---

## 4. Estrutura dos Jobs

### a) Job: ProcessWhatsappBufferJob
- Recupera o buffer do Redis
- Se houver mídia, envia para IA de imagem
- Se houver texto, envia para IA de texto
- Salva dados extraídos
- Dispara jobs subsequentes se necessário (ex: análise de sentimento, atualização de histórico)
- Responde ao usuário

### b) Job: AnalyzeImageJob
- Recebe imagem
- Envia para IA de visão computacional/OCR
- Retorna dados extraídos (ex: distância, tempo, batimentos, etc)

### c) Job: AnalyzeTextJob
- Recebe texto (e dados da imagem, se houver)
- Envia para IA de texto/sentimento
- Gera resposta personalizada

---

## 5. Exemplo de Fluxo Completo

1. Usuário envia:
   - Texto: "Hoje corri 5km"
   - Imagem: print do app de corrida
2. Buffer no Redis:
```json
[
  {"type": "text", "content": "Hoje corri 5km", "timestamp": "..."},
  {"type": "image", "content": "file_id_abc.jpg", "timestamp": "..."}
]
```
3. Após 10s, dispara o job `ProcessWhatsappBufferJob`.
4. O job envia a imagem para a IA, extrai: `{ "distancia": 5, "tempo": "28:00", "pace": "5:36" }`
5. O job envia o texto + dados extraídos para a IA de texto, que responde:
   - "Parabéns! Seu pace foi excelente hoje. Quer registrar esse treino como check-in?"
6. O bot responde ao usuário e salva tudo no histórico.

---

## 6. Boas Práticas
- Use TTL curto para buffers e locks no Redis.
- Armazene cada mensagem com tipo, conteúdo e timestamp.
- Trate erros de IA com respostas amigáveis.
- Use jobs assíncronos para cada etapa para garantir escalabilidade.
- Salve todos os dados para relatórios e evolução do usuário.

---

## 7. Possíveis Extensões Futuras
- Análise de áudio (voz para texto)
- Detecção de comandos especiais
- Gamificação baseada em evolução automática
- Personalização de respostas com base no histórico 