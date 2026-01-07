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
— (em revisão)

---

**TL;DR**: *Web app de hábitos com foco em UX e consistência; WhatsApp/IA ficam para depois do core e da monetização.*