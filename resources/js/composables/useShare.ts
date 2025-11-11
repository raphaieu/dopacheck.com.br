/**
 * Composable para compartilhamento nativo usando Web Share API
 */
export function useShare() {
  const isSupported = () => {
    return typeof navigator !== 'undefined' && 'share' in navigator
  }

  const share = async (data: {
    title?: string
    text?: string
    url?: string
    files?: File[]
  }): Promise<boolean> => {
    if (!isSupported()) {
      // Fallback: copiar para clipboard
      if (data.url) {
        try {
          await navigator.clipboard.writeText(data.url)
          return true
        } catch (error) {
          console.error('Erro ao copiar para clipboard:', error)
          return false
        }
      }
      return false
    }

    try {
      const shareData: ShareData = {}
      
      if (data.title) shareData.title = data.title
      if (data.text) shareData.text = data.text
      if (data.url) shareData.url = data.url
      if (data.files && 'canShare' in navigator && navigator.canShare({ files: data.files })) {
        shareData.files = data.files
      }

      await navigator.share(shareData)
      return true
    } catch (error: any) {
      // Usuário cancelou ou erro
      if (error.name === 'AbortError') {
        return false
      }
      console.error('Erro ao compartilhar:', error)
      return false
    }
  }

  const shareImage = async (imageUrl: string, title: string = 'DOPA Check') => {
    try {
      // Converter URL da imagem para File
      const response = await fetch(imageUrl)
      const blob = await response.blob()
      const file = new File([blob], 'dopacheck-card.png', { type: 'image/png' })

      if (isSupported() && 'canShare' in navigator && navigator.canShare({ files: [file] })) {
        await navigator.share({
          title,
          files: [file],
        })
        return true
      } else {
        // Fallback: abrir URL em nova aba
        window.open(imageUrl, '_blank')
        return true
      }
    } catch (error) {
      console.error('Erro ao compartilhar imagem:', error)
      return false
    }
  }

  const shareText = async (text: string, url?: string) => {
    return await share({
      text,
      url,
    })
  }

  const shareUrl = async (url: string, title?: string, text?: string) => {
    return await share({
      title,
      text,
      url,
    })
  }

  return {
    isSupported,
    share,
    shareImage,
    shareText,
    shareUrl,
  }
}

