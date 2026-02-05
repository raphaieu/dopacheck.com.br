<template>
  <div v-if="!isInstalled && (isInstallable || isIOS)" class="pwa-install-container">
    <button
      v-if="isInstallable"
      @click="handleInstall"
      class="pwa-install-button"
      :disabled="isInstalling"
    >
      <svg
        v-if="!isInstalling"
        xmlns="http://www.w3.org/2000/svg"
        class="pwa-install-icon"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 4v16m8-8H4"
        />
      </svg>
      <span v-if="isInstalling" class="pwa-install-spinner"></span>
      {{ isInstalling ? 'Instalando...' : 'Instalar App' }}
    </button>

    <button
      v-else-if="isIOS"
      @click="showIOSInstructions"
      class="pwa-install-button pwa-install-button-ios"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="pwa-install-icon"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 4v16m8-8H4"
        />
      </svg>
      Adicionar à Tela Inicial
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { usePWAInstall } from '@/composables/usePWAInstall'
import { toast } from 'vue-sonner'

const { isInstallable, isInstalled, isIOS, install, showIOSInstructions: showIOSInstructionsFromComposable } = usePWAInstall()
const isInstalling = ref(false)

const handleInstall = async () => {
  if (!isInstallable.value) return

  isInstalling.value = true
  try {
    const success = await install()
    if (success) {
      toast.success('App instalado com sucesso!')
    } else {
      toast.info('Instalação cancelada')
    }
  } catch (error) {
    console.error('Erro ao instalar:', error)
    toast.error('Erro ao instalar o app')
  } finally {
    isInstalling.value = false
  }
}

const showIOSInstructions = () => {
  // Cria um modal mais bonito com as instruções
  const modal = document.createElement('div')
  modal.className = 'pwa-install-modal'
  modal.innerHTML = `
    <div class="pwa-install-modal-overlay" onclick="this.parentElement.remove()"></div>
    <div class="pwa-install-modal-content">
      <button class="pwa-install-modal-close" onclick="this.closest('.pwa-install-modal').remove()">×</button>
      <h3>Instalar DOPA Check no iPhone/iPad</h3>
      <div class="pwa-install-steps">
        <div class="pwa-install-step">
          <div class="pwa-install-step-number">1</div>
          <div class="pwa-install-step-content">
            <p>Toque no botão <strong>Compartilhar</strong> <span style="font-size: 24px;">📤</span></p>
            <p class="pwa-install-step-hint">Na parte inferior da tela do Safari</p>
          </div>
        </div>
        <div class="pwa-install-step">
          <div class="pwa-install-step-number">2</div>
          <div class="pwa-install-step-content">
            <p>Role para baixo e toque em <strong>"Adicionar à Tela de Início"</strong> <span style="font-size: 24px;">➕</span></p>
            <p class="pwa-install-step-hint">Na lista de opções de compartilhamento</p>
          </div>
        </div>
        <div class="pwa-install-step">
          <div class="pwa-install-step-number">3</div>
          <div class="pwa-install-step-content">
            <p>Toque em <strong>"Adicionar"</strong></p>
            <p class="pwa-install-step-hint">No canto superior direito</p>
          </div>
        </div>
      </div>
      <p class="pwa-install-modal-footer">
        O ícone do DOPA Check aparecerá na sua tela inicial! 🎉
      </p>
    </div>
  `
  document.body.appendChild(modal)
  
  // Remove o modal ao clicar no overlay
  modal.querySelector('.pwa-install-modal-overlay')?.addEventListener('click', () => {
    modal.remove()
  })
}
</script>

<style scoped>
.pwa-install-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 50;
}

.pwa-install-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  transition: all 0.2s ease;
}

.pwa-install-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.pwa-install-button:active:not(:disabled) {
  transform: translateY(0);
}

.pwa-install-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.pwa-install-button-ios {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.pwa-install-button-ios:hover {
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.pwa-install-icon {
  width: 20px;
  height: 20px;
}

.pwa-install-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Modal de instruções iOS */
:global(.pwa-install-modal) {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

:global(.pwa-install-modal-overlay) {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

:global(.pwa-install-modal-content) {
  position: relative;
  background: white;
  border-radius: 16px;
  padding: 24px;
  max-width: 420px;
  width: 100%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

:global(.pwa-install-modal-close) {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 32px;
  height: 32px;
  border: none;
  background: #f3f4f6;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s;
}

:global(.pwa-install-modal-close:hover) {
  background: #e5e7eb;
  color: #374151;
}

:global(.pwa-install-modal-content h3) {
  margin: 0 0 20px 0;
  font-size: 20px;
  font-weight: 700;
  color: #111827;
}

:global(.pwa-install-steps) {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
}

:global(.pwa-install-step) {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

:global(.pwa-install-step-number) {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

:global(.pwa-install-step-content) {
  flex: 1;
  padding-top: 4px;
}

:global(.pwa-install-step-content p) {
  margin: 0;
  line-height: 1.6;
  color: #374151;
}

:global(.pwa-install-step-hint) {
  font-size: 13px;
  color: #6b7280;
  margin-top: 4px !important;
}

:global(.pwa-install-modal-footer) {
  margin: 0;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
  font-size: 14px;
  color: #6b7280;
  text-align: center;
}
</style>
