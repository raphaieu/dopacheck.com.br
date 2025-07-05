export function validateEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}

export function validatePhoneNumber(phone: string): boolean {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
    return phoneRegex.test(phone.replace(/\D/g, ''))
}

export function validateImageFile(file: File): { valid: boolean; error?: string } {
    // Verificar tipo
    if (!file.type.startsWith('image/')) {
        return { valid: false, error: 'Arquivo deve ser uma imagem' }
    }

    // Verificar tamanho (5MB)
    if (file.size > 5 * 1024 * 1024) {
        return { valid: false, error: 'Arquivo deve ter no máximo 5MB' }
    }

    return { valid: true }
}

export function validateUsername(username: string): { valid: boolean; error?: string } {
    if (username.length < 3) {
        return { valid: false, error: 'Username deve ter pelo menos 3 caracteres' }
    }

    if (username.length > 20) {
        return { valid: false, error: 'Username deve ter no máximo 20 caracteres' }
    }

    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        return { valid: false, error: 'Username deve conter apenas letras, números e underscore' }
    }

    return { valid: true }
}
