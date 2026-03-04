# Landing pages customizadas

Páginas aqui são usadas quando, no Filament (Team), você escolhe **Layout = Padrão** e seleciona uma **Página custom** pelo nome do arquivo (sem `.vue`).

- **Nome do arquivo** = nome que aparece no select (ex.: `MinhaLanding.vue` → selecione `MinhaLanding`).
- Use apenas letras, números, `_` e `-` no nome do arquivo.
- Cada página recebe a prop **`team`** (mesmo payload da landing padrão: `name`, `slug`, `form_schema`, `theme`, `onboarding_behavior`, etc.).
- Para enviar o formulário, faça POST para `route('teams.join.store', team.slug)` com os campos definidos no `form_schema` do time (e `terms_accepted: true` se o comportamento for "Criar usuário").
- Veja `Example.vue` como referência mínima.
