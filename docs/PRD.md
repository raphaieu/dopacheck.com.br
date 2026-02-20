# PRD — DOPA Check
## Infraestrutura de Disciplina via WhatsApp

## 1. Visão do Produto

O DOPA Check é uma infraestrutura plugável para transformar grupos de WhatsApp em ambientes estruturados de disciplina e consistência.

Ele permite que desafios sejam criados e acompanhados automaticamente via:

📸 Foto + #hashtag → Check-in automático → Progresso atualizado

Sem necessidade de abrir app.

---

## 2. Problema

Comunidades já fazem desafios.

Mas enfrentam:

- Onboarding manual (Google Forms + validação humana)
- Falta de registro estruturado
- Histórico perdido no grupo
- Dificuldade de visualizar progresso coletivo
- Fricção para registrar tarefas

---

## 3. Público-Alvo Inicial

- Comunidades de hábitos
- Grupos regionais
- Grupos fitness
- Igrejas
- Times de empresa
- Grupos familiares

Perfil predominante:
- 18–30 anos
- Alto uso de WhatsApp
- Já participam de desafios

---

## 4. Proposta de Valor

### Diferencial Principal

Zero fricção real.

Usuário não precisa abrir aplicativo.
Já está no grupo.
Já está postando foto.

O sistema só estrutura e registra.

---

## 5. Funcionalidades MVP (Core Web)

- Login (Google OAuth)
- Sistema de times (Teams)
- Onboarding público por link (/join/{slug})
- Aprovação manual de membros
- Criação de desafios
- Tasks com hashtags únicas por escopo
- Check-in via web (imagem opcional)
- Dashboard com progresso
- Sistema Freemium + PRO (Stripe)

---

## 6. Funcionalidade Estratégica

### Check-in via WhatsApp

Fluxo:

1. Bot está no grupo
2. Grupo vinculado a um Team
3. Usuário cadastrado envia:
   - Foto
   - #hashtag
4. Sistema:
   - Identifica grupo
   - Identifica hashtag
   - Identifica usuário
   - Cria check-in
   - Atualiza streak
   - Confirma no grupo ou privado

---

## 7. Modelo de Monetização

### Free
- 1 desafio ativo
- Check-in manual
- Histórico limitado

### PRO
- Desafios ilimitados
- Check-in via WhatsApp
- Relatórios avançados
- Storage permanente
- Recursos futuros de IA

---

## 8. Métricas de Sucesso

- Taxa de conclusão de desafios
- Streak médio
- Ativação de bot por grupo
- % check-ins via WhatsApp
- Retenção 7 / 30 dias

---

## 9. Estratégia de Go-To-Market

1. Lançamento beta em comunidade existente
2. Divulgação no Circle
3. Divulgação via Instagram
4. Beta gratuito
5. Coleta ativa de feedback
6. Iteração rápida

---

## 10. Riscos

- Dependência inicial de uma comunidade
- Complexidade técnica do WhatsApp
- Escalabilidade de webhook

Mitigação:
Produto independente.
Infra plugável.
Marca própria.