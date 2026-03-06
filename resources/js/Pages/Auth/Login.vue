<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { useLocalStorage } from '@vueuse/core'
import { computed, inject, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import InputError from '@/components/InputError.vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import SocialLoginButton from '@/components/SocialLoginButton.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
  availableOauthProviders: Object,
})

import { Icon } from '@iconify/vue'

const page = usePage()
const route = inject('route')
const activeTab = useLocalStorage('login-active-tab', 'password')

// Form state
const passwordForm = useForm({
  email: '',
  password: '',
  remember: false,
})

const loginLinkForm = useForm({
  email: '',
})

// Computed
const hasOauthProviders = computed(() =>
  Object.keys(props.availableOauthProviders || {}).length > 0,
)

const isProcessing = computed(() =>
  passwordForm.processing || loginLinkForm.processing,
)

// Methods
function handlePasswordLogin() {
  passwordForm
    .transform(data => ({
      ...data,
      remember: data.remember ? 'on' : '',
    }))
    .post(route('login'), {
      onFinish: () => passwordForm.reset('password'),
    })
}

function handleLoginLink() {
  loginLinkForm.post(route('login-link.store'), {
    onSuccess: () => {
      loginLinkForm.reset()
    },
    onError: () => {
      // app.js handles the toast
    },
  })
}

// Lifecycle
onMounted(() => {
  // Global handler in app.js already processes initial flash messages
})

// SEO
useSeoMetaTags({
  title: 'Entrar',
})
</script>

<template>
  <div class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
    <!-- Decorative Blurs -->
    <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
    <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
    <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s"></div>

    <Card class="relative mx-auto w-[95%] max-w-[440px] shadow-2xl shadow-slate-200/50 border border-white/80 bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-4 transition-all duration-500 hover:shadow-blue-500/5 overflow-hidden z-10">
      <!-- Glow effect inside card -->
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Header -->
      <CardHeader class="space-y-6 pb-8 pt-6">
        <CardTitle class="flex flex-col items-center gap-4">
          <div class="size-12 rounded-2xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 flex items-center justify-center shadow-xl shadow-blue-600/20 transform hover:rotate-6 transition-transform duration-300">
             <span class="text-white text-3xl select-none">🧠</span>
          </div>
          <div class="text-center">
            <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">DOPA Check</h1>
          </div>
        </CardTitle>
      </CardHeader>

      <CardContent class="px-6 pb-8">
        <!-- Status Message -->
        <div v-if="status" class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 p-4 text-sm font-black text-emerald-600 uppercase tracking-tight text-center">
          {{ status }}
        </div>

        <!-- Login Tabs -->
        <Tabs v-model="activeTab" class="w-full">
          <TabsList class="grid w-full grid-cols-2 rounded-2xl p-1.5 bg-slate-100/50 backdrop-blur-sm mb-8">
            <TabsTrigger value="password" class="rounded-xl cursor-pointer data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-black uppercase tracking-widest text-[10px] py-3 transition-all">
              Senha
            </TabsTrigger>
            <TabsTrigger value="login-link" class="rounded-xl cursor-pointer data-[state=active]:bg-white data-[state=active]:text-blue-600 data-[state=active]:shadow-sm font-black uppercase tracking-widest text-[10px] py-3 transition-all">
              Link Mágico
            </TabsTrigger>
          </TabsList>

          <div class="mt-2">
            <!-- Password Login -->
            <TabsContent value="password" class="space-y-6">
              <form @submit.prevent="handlePasswordLogin">
                <div class="grid gap-6">
                  <!-- Email -->
                  <div class="grid gap-2.5">
                    <Label for="email" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">E-mail</Label>
                    <Input
                      id="email"
                      v-model="passwordForm.email"
                      type="email"
                      placeholder="seu@email.com"
                      required
                      autofocus
                      autocomplete="username"
                      class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                    />
                    <InputError :message="passwordForm.errors.email" />
                  </div>

                  <!-- Password -->
                  <div class="grid gap-2.5">
                    <div class="flex items-center justify-between ml-1">
                      <Label for="password" class="text-slate-900 font-black uppercase tracking-widest text-[10px]">Senha</Label>
                      <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest"
                      >
                        Esqueceu a senha?
                      </Link>
                    </div>
                    <Input
                      id="password"
                      v-model="passwordForm.password"
                      type="password"
                      required
                      autocomplete="current-password"
                      class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                    />
                    <InputError :message="passwordForm.errors.password" />
                  </div>

                  <!-- Remember Me -->
                  <div class="flex items-center space-x-3 ml-1">
                    <Checkbox
                      id="remember"
                      v-model:checked="passwordForm.remember"
                      name="remember"
                      class="rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20"
                    />
                    <label for="remember" class="text-[11px] text-slate-500 font-black uppercase tracking-widest cursor-pointer">
                      Lembrar-me
                    </label>
                  </div>

                  <Button
                    type="submit"
                    class="cursor-pointer h-14 w-full bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-[0.2em] text-sm rounded-2xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98] disabled:opacity-50"
                    :disabled="isProcessing"
                  >
                    {{ passwordForm.processing ? 'Entrando...' : 'Entrar' }}
                  </Button>
                </div>
              </form>
            </TabsContent>

            <!-- Login Link -->
            <TabsContent value="login-link" class="space-y-6">
              <div class="flex items-start gap-3 bg-blue-50/40 backdrop-blur-sm border border-blue-100/50 rounded-2xl p-4 mb-6">
                <Icon icon="lucide:mail" class="size-4 text-blue-600 mt-0.5" />
                <p class="text-[10px] text-blue-700 font-black uppercase tracking-widest leading-relaxed">
                  Enviaremos um link mágico para seu e-mail para entrar sem senha.
                </p>
              </div>
              <form @submit.prevent="handleLoginLink">
                <div class="grid gap-6">
                  <div class="grid gap-2.5">
                    <Label for="login-link-email" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">E-mail</Label>
                    <Input
                      id="login-link-email"
                      v-model="loginLinkForm.email"
                      type="email"
                      required
                      placeholder="seu@email.com"
                      class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                    />
                    <InputError :message="loginLinkForm.errors.email" />
                  </div>

                  <Button
                    type="submit"
                    class="cursor-pointer h-14 w-full bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-[0.2em] text-sm rounded-2xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98] disabled:opacity-50"
                    :disabled="isProcessing"
                  >
                    {{ loginLinkForm.processing ? 'Enviando...' : 'Enviar Link' }}
                  </Button>
                </div>
              </form>
            </TabsContent>
          </div>
        </Tabs>

        <!-- OAuth Section -->
        <div v-if="hasOauthProviders" class="mt-10">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <span class="w-full border-t border-slate-200" />
            </div>
            <div class="relative flex justify-center text-[10px] font-black uppercase tracking-[0.2em]">
              <span class="bg-white/70 backdrop-blur-xl px-4 text-slate-400">
                Ou continue com
              </span>
            </div>
          </div>

          <div class="mt-8 grid gap-3">
            <SocialLoginButton
              v-for="provider in availableOauthProviders"
              :key="provider.slug"
              :provider="provider"
              :disabled="isProcessing"
              class="h-12 rounded-xl"
            />
          </div>
        </div>

        <!-- Sign Up Link -->
        <div class="mt-10 text-center text-[11px] font-black text-slate-500 uppercase tracking-widest">
          Não tem uma conta?
          <Link
            :href="route('register')"
            class="text-blue-600 hover:text-blue-700 underline decoration-blue-200 underline-offset-4 decoration-2 transition-all"
          >
            Cadastre-se
          </Link>
        </div>
      </CardContent>
    </Card>

    <!-- Footer Credit -->
    <p class="mt-12 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] select-none">
       DOPA Check · Premium Edition
    </p>
  </div>
</template>
