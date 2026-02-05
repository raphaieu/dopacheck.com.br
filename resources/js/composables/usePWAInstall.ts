import { ref, onMounted, onUnmounted } from 'vue'

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>
}

export function usePWAInstall() {
  const isInstallable = ref(false)
  const isInstalled = ref(false)
  const isIOS = ref(false)
  const isStandalone = ref(false)
  const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null)

  // Detecta se está rodando como PWA instalado
  const checkIfInstalled = () => {
    // @ts-ignore - window.navigator.standalone é específico do iOS
    const isIOSStandalone = window.navigator.standalone === true
    const isAndroidStandalone = window.matchMedia('(display-mode: standalone)').matches
    const isStandaloneMode = isIOSStandalone || isAndroidStandalone

    isStandalone.value = isStandaloneMode
    isInstalled.value = isStandaloneMode

    // Detecta iOS
    const userAgent = window.navigator.userAgent.toLowerCase()
    const isIOSDevice = /iphone|ipad|ipod/.test(userAgent)
    const isIOSSafari = isIOSDevice && !window.MSStream && !(window as any).chrome
    isIOS.value = isIOSSafari

    return isStandaloneMode
  }

  // Handler para o evento beforeinstallprompt (Android/Chrome)
  const handleBeforeInstallPrompt = (e: Event) => {
    // Previne o prompt padrão do browser
    e.preventDefault()
    
    // Salva o evento para usar depois
    deferredPrompt.value = e as BeforeInstallPromptEvent
    isInstallable.value = true
  }

  // Instala o PWA (Android/Chrome)
  const install = async (): Promise<boolean> => {
    if (!deferredPrompt.value) {
      return false
    }

    try {
      // Mostra o prompt de instalação
      await deferredPrompt.value.prompt()

      // Espera a escolha do usuário
      const choiceResult = await deferredPrompt.value.userChoice

      if (choiceResult.outcome === 'accepted') {
        isInstallable.value = false
        deferredPrompt.value = null
        isInstalled.value = true
        return true
      }

      return false
    } catch (error) {
      console.error('Erro ao instalar PWA:', error)
      return false
    }
  }

  // Mostra instruções para iOS (será sobrescrito no componente com modal bonito)
  const showIOSInstructions = () => {
    // Esta função será sobrescrita no componente com um modal mais bonito
    // Por enquanto, apenas retorna true para indicar que deve mostrar o modal
    return true
  }

  onMounted(() => {
    // Verifica se já está instalado
    checkIfInstalled()

    // Se já estiver instalado, não precisa mostrar o botão
    if (isInstalled.value) {
      return
    }

    // Listener para o evento beforeinstallprompt (Android/Chrome)
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)

    // Verifica periodicamente se foi instalado (para casos onde o usuário instala sem usar nosso botão)
    const checkInterval = setInterval(() => {
      if (checkIfInstalled()) {
        clearInterval(checkInterval)
      }
    }, 1000)
  })

  onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  })

  return {
    isInstallable,
    isInstalled,
    isIOS,
    isStandalone,
    install,
    showIOSInstructions,
  }
}
