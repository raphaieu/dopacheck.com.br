import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
  const page = usePage()
  
  const user = computed(() => page.props.auth?.user)
  const isAuthenticated = computed(() => !!user.value)
  const isPro = computed(() => user.value?.is_pro || false)
  
  return {
    user,
    isAuthenticated,
    isPro
  }
}
