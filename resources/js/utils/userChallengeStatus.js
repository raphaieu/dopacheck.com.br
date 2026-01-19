// Centraliza a formatação de status do UserChallenge (participação do usuário no desafio)
// Status conhecidos (backend): active, completed, paused, abandoned, expired

export const USER_CHALLENGE_STATUS_LABELS = Object.freeze({
  active: 'Ativo',
  completed: 'Completo',
  paused: 'Pausado',
  abandoned: 'Abandonado',
  expired: 'Encerrado',
})

export function formatUserChallengeStatus(status) {
  if (!status) return ''
  return USER_CHALLENGE_STATUS_LABELS[status] || status
}

