# 🧠 DOPA Check - Resumo Executivo

## 🎯 **O Que É**
O **DOPA Check** é uma plataforma **web (mobile-first)** de tracking de hábitos e desafios, com check-ins rápidos (com ou sem imagem) e um dashboard visual de progresso. A integração com WhatsApp (EvolutionAPI) existe como **linha de roadmap**, mas não é o foco do MVP atual.

## 🔥 **Problema que Resolve**
- **Fragmentação de apps** (Strava, Notion, planilhas separadas)
- **Fricção no check-in** (abrir app → navegar → registrar)
- **Falta de consistência** nos hábitos por complexidade
- **Baixo engajamento** em desafios comunitários

## ⚡ **Solução Core**
```
Check-in rápido no web app = Progresso atualizado + Dashboard visual
```

## 🎮 **Como Funciona (30 segundos)**
1. **Login** → Escolhe/entra em um desafio (ex.: 21 dias leitura)
2. **Faz check-in** (com ou sem imagem) pelo web app
3. **Dashboard atualiza** → Progresso visual + streak
4. **Compartilha conquista** → link público + card gerado

## 💰 **Modelo de Negócio**
- **Freemium**: 1 desafio, check-in manual, 90 dias de storage
- **PRO (preço a definir)**: múltiplos desafios + recursos avançados (pagamento via Stripe/Cashier)

## 🚀 **Diferencial**
- **Zero apps extras** - usa WhatsApp que já tem
- **Check-in por imagem** - mais natural que texto
- **Social proof** - vê quantas pessoas estão no mesmo desafio
- **Compartilhamento viral** - perfil público + imagens para stories

## 📊 **Target & Validação**
- **Público**: Comunidade Reservatório de Dopamina (grupos WhatsApp de hábitos)
- **MVP**: 30 pessoas do SalvadoPamina testando "21 dias de leitura"
- **Métricas**: Taxa de conclusão 68% vs. 23% média de apps tradicionais

## 🛠️ **Stack (FullStack Solo)**
- **Backend**: Laravel 12 + MySQL + Redis (Horizon)
- **Frontend**: Vue 3 + Tailwind + ShadCN
- **Integrações**: Google OAuth (Socialite), Stripe (Cashier) e WhatsApp (EvolutionAPI, futuro)
- **Deploy**: VPS + Cloudflare R2 (storage)

## 🎯 **Objetivo Profissional**
Demonstrar capacidade **FullStack completa**:
- ✅ **Identificação de problema real**
- ✅ **Desenvolvimento solo** (VibeCoding)
- ✅ **Arquitetura escalável**
- ✅ **Produto que funciona**
- ✅ **Open-source** para comunidade

## 📈 **Timeline**
- (em revisão)

---

**TL;DR**: *Web app de hábitos com foco em UX e consistência; WhatsApp/IA ficam para depois do core e da monetização.*

A sociedade contemporânea enfrenta uma crescente dificuldade na sustentação de hábitos saudáveis e consistentes, especialmente em um contexto de hiperestimulação digital. A exposição contínua a estímulos de recompensa imediata (redes sociais, notificações constantes e scroll infinito) tem reduzido níveis de foco, disciplina e adesão a rotinas relacionadas à saúde física, mental e desenvolvimento pessoal.

Comunidades organizadas — grupos de prática esportiva, desenvolvimento pessoal, igrejas, coletivos educacionais e redes de apoio — buscam constantemente formas de estimular engajamento e consistência entre seus membros. Entretanto, enfrentam um problema recorrente: baixa adesão sustentável, ausência de mecanismos estruturados de mensuração e dificuldade em acompanhar o progresso individual e coletivo de forma organizada.

Embora existam aplicativos individuais de hábitos e bem-estar, a maioria apresenta alta fricção de uso, baixa integração social estruturada e elevada taxa de abandono. Falta ao mercado uma solução que una engajamento comunitário, mensuração objetiva e redução de barreiras tecnológicas.

Nesse contexto, surge a oportunidade de desenvolver uma infraestrutura digital híbrida para mudança comportamental em comunidades, integrando mensageria instantânea (WhatsApp) como canal de engajamento e check-in automatizado em grupos, com um WebApp complementar para gestão de desafios públicos e privados, consolidação de dados, geração de relatórios e acompanhamento longitudinal de métricas comportamentais.

Ao combinar dinâmica social, validação pública e mensuração estruturada, a proposta cria um ambiente de reforço positivo e competição saudável, aumentando significativamente a probabilidade de adesão e consistência em mudanças comportamentais.

A solução posiciona-se na interseção entre saúde digital, educação comportamental e tecnologia da informação, com potencial de expansão para famílias, escolas e programas institucionais voltados à promoção de saúde e bem-estar.