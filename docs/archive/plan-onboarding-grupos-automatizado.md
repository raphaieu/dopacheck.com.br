# Planejamento: Novo time no fluxo de Criar Desafio

**Objetivo:** Permitir que o usuário crie um **novo time** (grupo) direto no fluxo de **Criar desafio**, sem depender do admin. Toda a dinâmica fica na criação do desafio; a tela "Novo time" é um passo intermediário que, ao finalizar, volta para a criação do desafio com o time recém-criado selecionado.

*(Futuro: em outra sprint, "Meus times" para gerenciar inserir/excluir/alterar times.)*

---

## 1. Cenário atual (resumido)


| Onde              | Hoje                                                                                                    |
| ----------------- | ------------------------------------------------------------------------------------------------------- |
| **Criar desafio** | Escopo: "Público global" ou (se fizer parte de um time) "Time X". Opção de desmarcar = desafio privado. |
| **Time**          | Só existe se admin criou no Filament (nome, slug, link grupo, JID, landing, etc.).                      |
| **Bot no grupo**  | Usuário adiciona o bot; admin preenche `whatsapp_group_jid` no Filament.                                |


**Problema:** Não há como o usuário criar um time sozinho no fluxo do produto; tudo passa pelo admin.

---

## 1.1 Premissa: criador do time = admin do grupo

O **usuário logado que cria o time** é tratado como **administrador do grupo** tanto no DOPA quanto no WhatsApp (ele é quem vai criar/add o grupo e adicionar o bot). Por isso:

- **Exigir o número de WhatsApp dele (vinculado no Redis)** garante que: (1) o número existe, (2) ele consegue adicionar o bot ao grupo.
- Quando o bot for adicionado ao grupo, podemos **correlacionar** e saber de qual **Team ID** se trata:
  - **Opção A:** O bot varre a lista de participantes do grupo (Evolution API) e verifica se o número do criador do time está entre eles; times com `whatsapp_group_jid` nulo cujo dono tem esse número podem ser candidatos.
  - **Opção B:** Se a Evolution API enviar no webhook **quem adicionou o bot** (ex.: `GROUP_PARTICIPANTS_UPDATE` com "added by"), correlacionamos esse número com o `user_id` dono do time (via `user.whatsapp_number`).
- Com essa correlação, o **próprio bot** (ou o backend ao processar o webhook) atualiza o `whatsapp_group_jid` do time correto. O ciclo fica fechado: criar time → usuário adiciona o bot no grupo → sistema detecta e preenche o JID.

*(No início o usuário pode informar o JID manualmente no formulário se já tiver; a automação via webhook/primeira mensagem pode vir em refinamento.)*

---

## 2. Fluxo desejado (simplificado)

### 2.1 Criar desafio – escopo de compartilhamento

- **Opções atuais:** Público global | (se tiver) Times (lista).
- **Nova opção:** **"Novo time"** (botão ou item no select).
- Ao escolher "Novo time": ir para a **tela de Cadastro de time** (nova página ou modal). Ao concluir, **voltar para Criar desafio** com o novo time já selecionado no escopo e formulário preservado.

### 2.2 Tela de Cadastro / Edição de time (self-service)

Equivalente aos campos do Filament para criar um Team, mas com **parte já fixa/default** para não complicar para o usuário final (fase inicial com suporte humano quando precisar).

- **Campos no formulário (espelho do Filament, com supressões/defaults):**
  - **Nome do time** (obrigatório)
  - **Slug** (obrigatório, para `/join/{slug}`)
  - **Link do grupo WhatsApp** (whatsapp_join_url)
  - **JID do grupo** (whatsapp_group_jid) — pode ficar vazio no cadastro e ser preenchido depois pelo bot quando houver correlação (webhook / quem adicionou)
  - **Nome do grupo** (whatsapp_group_name, opcional) — serve de double-check; não confiável pois pode mudar a qualquer momento
  - **Título do onboarding** (onboarding_title, opcional)
  - **Texto de apresentação** (onboarding_body, opcional) — ou usar default se vazio
- **Fixos/default (não expostos ou simplificados):**
  - **Landing:** layout **padrão** (default); ao enviar o formulário da landing → **criar usuário e vincular ao time**.
  - **Formulário da landing:** fixo **name**, **email**, **whatsapp_number** (todos obrigatórios). Sem página custom nem repeater de form_schema.
  - Sem RichEditor complexo no primeiro momento; texto de apresentação pode ser textarea simples ou default.
- **Aviso na tela:** "É uma página padrão; no máximo você pode selecionar cores e texto de apresentação. Se quiser algo mais personalizado (imagens, outras informações), entre em contato pelo WhatsApp [link para Raphael] para alinharmos."
- **Validação para poder submeter:** O **usuário logado** (criador do time = futuro admin do grupo) só pode enviar se tiver **celular cadastrado** e **vinculado no Redis** (sessão do bot), para permitir a correlação quando o bot for adicionado ao grupo.

### 2.3 Retorno ao Criar desafio

- Após criar o time com sucesso, redirecionar de volta para a página de **Criar desafio** (step 3 – Revisão final ou step onde está o escopo).
- Preencher/atualizar o escopo com o **time recém-criado** selecionado (e `team_id` no form).

---

## 3. Resumo das regras


| Item                   | Regra                                                                                                                                                                                                        |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Onde entra "Novo time" | Select/opção de "Escopo de compartilhamento" na criação do desafio.                                                                                                                                          |
| Tela de time           | Nome, slug, link do grupo, JID, **nome do grupo** (opcional, double-check), **título do onboarding**, texto de apresentação; landing padrão; form fixo name/email/whatsapp_number; criar usuário e vincular. |
| Personalização         | Apenas cores + texto por enquanto; aviso + link WhatsApp (Raphael) para algo além.                                                                                                                           |
| Quem pode submeter     | Só se o usuário logado tiver celular cadastrado **e** vinculado (Redis / sessão do bot).                                                                                                                     |
| Depois de criar o time | Voltar para Criar desafio e selecionar o novo time.                                                                                                                                                          |


---

## 4. Tarefas implementáveis


| #     | Tarefa                                            | Detalhe                                                                                                                                                                                                                                                                                                                           |
| ----- | ------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **1** | **Opção "Novo time" no escopo (Criar desafio)**   | No select de "Escopo de compartilhamento" (Create.vue), adicionar opção "Novo time". Ao selecionar, navegar para rota de criação de time (ex.: `/teams/create-from-challenge` ou `/challenges/create?new_team=1`) preservando estado do form (ou reenviando via query/state).                                                     |
| **2** | **Rota + controller: criar time (self-service)**  | GET + POST para usuário logado. Validar: nome, slug (único), link grupo (url), JID (formato `...@g.us` ou vazio), whatsapp_group_name e onboarding_title/body opcionais. Time com `user_id` = current user, `landing_template` = default, `onboarding_behavior` = create_user, `form_schema` fixo (name, email, whatsapp_number). |
| **3** | **Validação WhatsApp vinculado**                  | Só permitir abrir/submeter o form se o usuário logado tiver `whatsapp_number` e sessão no Redis (`WhatsappSessionService::get`). Mensagem: "Para criar um time para grupo, você precisa ter seu WhatsApp vinculado. Envie uma mensagem no privado para o bot para vincular." + link para conectar.                                |
| **4** | **Página Vue: Cadastro de time**                  | Formulário: nome, slug, link grupo, JID, nome do grupo (opcional), título do onboarding, texto de apresentação (e tema/cores se fizer sentido). Aviso página padrão + link "Falar com Raphael". Submit → POST; sucesso → redirect para Criar desafio com `?team_id={id}`.                                                         |
| **5** | **Retorno ao Criar desafio com time selecionado** | Na rota de Criar desafio (GET), se vier `team_id` (query ou session), carregar a lista de times do usuário (incluindo o recém-criado), setar `shareScope` = esse team_id e `shareEnabled` = true, e preencher `form.team_id` / `form.visibility` para "team".                                                                     |
| **6** | **Botão "Falar com Raphael"**                     | Na tela de cadastro de time e, se desejado, na landing padrão do join: link `https://wa.me/5511948863848` com texto pré-preenchido.                                                                                                                                                                                               |
| **7** | **(Refinamento) Bot/backend atualiza JID**        | Ao processar webhook (bot adicionado ao grupo ou primeira mensagem), correlacionar número do criador/admin com time sem JID e atualizar `whatsapp_group_jid` (e opcionalmente `whatsapp_group_name`).                                                                                                                             |


---

## 5. Dados do time (form_schema fixo – layout padrão)

Para o fluxo "Novo time" com landing padrão e "criar usuário e vincular":

- **form_schema** (fixo, não editável nesta fase):  
`[ { key: 'name', type: 'text', label: 'Nome', required: true }, { key: 'email', type: 'email', label: 'E-mail', required: true }, { key: 'whatsapp_number', type: 'tel', label: 'WhatsApp', required: true } ]`
- **onboarding_behavior:** `create_user`
- **landing_template:** `default` (ou o que for o layout padrão da `/join/{slug}`).

---

## 6. Atualização do JID pelo bot (ciclo fechado)

Quando o criador do time (admin do grupo) adiciona o bot ao grupo:

- **Webhook** (ex.: `GROUP_PARTICIPANTS_UPDATE` com “quem adicionou”, ou primeira mensagem no grupo com `remote_jid`): backend identifica o número de quem adicionou (ou participantes) e correlaciona com o **dono de um time** que ainda não tem `whatsapp_group_jid`.
- Backend atualiza `Team.whatsapp_group_jid` (e opcionalmente `whatsapp_group_name`) para esse time. O **bot** não precisa “escrever no banco” diretamente; o nosso backend que processa o webhook faz a atualização.

*(Implementação desse passo pode vir em refinamento após a tela de cadastro e o fluxo Criar desafio → Novo time → voltar.)*

---

## 7. Riscos / decisões

- **Slug único:** Validar unicidade na criação (como no Filament).
- **JID:** Formato `...@g.us` quando preenchido; pode ser vazio no cadastro e preenchido depois pela correlação (bot adicionado).
- **Nome do grupo:** Apenas double-check/visual; não usar como chave (pode mudar).
- **Futuro:** Sprint "Meus times" (listar, editar, excluir); refinamentos de UX e automação do JID no decorrer do desenvolvimento.

