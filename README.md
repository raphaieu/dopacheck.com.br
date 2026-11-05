# 🧠 DOPA Check

![DOPA Check - Open Graph](public/images/og.webp)

<div align="center">

**Transforme seu smartphone em um tracker de hábitos inteligente**

*Plataforma web (mobile-first) para tracking de hábitos e desafios — com check-in automático via WhatsApp integrado (fase 2 no ar; veja [docs/STATUS.md](docs/STATUS.md))*

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)

[🚀 Demo Live](https://dopacheck.com.br) • [📖 Documentação](#como-funciona) • [🛠️ Instalação](#instalação) • [🎯 Roadmap](#roadmap)

</div>

---

# 🧠 DOPA Check

Infraestrutura de disciplina.

## O que é

O DOPA Check é uma plataforma web de tracking de hábitos que transforma grupos de WhatsApp em ambientes estruturados de disciplina — check-in rápido pelo app ou automático no grupo.

Você já posta foto.
Você já usa hashtag.
Agora isso vira progresso real.

Web: check-in no app a 1 clique
WhatsApp: 📸 + #hashtag = Check-in automático (com reação ✅ do bot)


## Como funciona

1. Crie um time
2. Compartilhe o link de onboarding
3. Adicione o bot ao grupo
4. Crie um desafio
5. Grupo faz o resto

---

## Diferencial

- Zero fricção
- Não precisa abrir app
- Progresso visível
- Streak automático
- Comunidade como motor

---

## Modelo

Free:
- 1 desafio ativo
- Check-in manual

PRO:
- Desafios ilimitados
- Check-in via WhatsApp
- Relatórios avançados

---

## Status

Beta funcional.
Lançamento comunitário em andamento.

---

## Desenvolvimento local com Docker

Pré-requisitos: Docker Engine com o plugin Docker Compose.

```bash
cp .env.example .env
docker compose build app
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

O ambiente de desenvolvimento usa o código do host com Vite em modo watch. Não é
necessário instalar PHP, Composer, Node ou MySQL diretamente na máquina.

- Aplicação: http://localhost:8010
- Vite/HMR: http://localhost:5173
- Mailpit: http://localhost:8025
- MySQL no host: `127.0.0.1:3307`
- Redis no host: `127.0.0.1:6380`
- phpMyAdmin opcional: `docker compose --profile tools up -d phpmyadmin`
  (http://localhost:8083)

Os volumes do MySQL, Redis, `storage`, `vendor` e `node_modules` persistem entre
reinícios. `docker compose down` não apaga esses dados.

Se alterar `VITE_PORT`, ajuste também `VITE_ORIGIN` no `.env` para a URL que o
navegador usa para acessar o Vite.

### Importar uma cópia do banco de produção

Antes da primeira importação, suba ao menos o MySQL:

```bash
docker compose up -d mysql
```

Para um dump SQL descompactado:

```bash
docker compose exec -T mysql mysql \
  -udopacheck_user -pdopacheck_pass dopacheck < /caminho/para/dopacheck.sql
```

Para um dump compactado com gzip:

```bash
gzip -dc /caminho/para/dopacheck.sql.gz | docker compose exec -T mysql mysql \
  -udopacheck_user -pdopacheck_pass dopacheck
```

Esses comandos escrevem no banco local. Confirme que o `.env` mantém `DB_HOST=mysql`
antes de importar. Depois da importação:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan optimize:clear
```

Credenciais e integrações externas que não devam atuar de verdade em desenvolvimento
(Stripe, Evolution/WhatsApp, e-mail transacional e webhooks) devem permanecer vazias
ou em modo sandbox/log. O Mailpit captura localmente os e-mails SMTP.

### Comandos úteis

```bash
docker compose ps
docker compose logs -f app vite horizon
docker compose exec app php artisan test
docker compose down
```

---

## Stack

Laravel 12  
Vue 3  
MySQL  
Redis  
Stripe  

---

Produto privado.
Todos os direitos reservados.
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
