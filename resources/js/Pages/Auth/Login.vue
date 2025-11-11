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
import Sonner from '@/components/ui/sonner/Sonner.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
  availableOauthProviders: Object,
})

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
      if (page.props.flash.success) {
        toast.success(page.props.flash.success)
      }
    },
    onError: () => {
      if (page.props.flash.error) {
        toast.error(page.props.flash.error)
      }
    },
  })
}

// Lifecycle
onMounted(() => {
  if (page.props.flash.error) {
    toast.error(page.props.flash.error)
  }

  if (page.props.flash.success) {
    toast.success(page.props.flash.success)
  }
})

// SEO
useSeoMetaTags({
  title: 'Entrar',
})
</script>

<template>
  <Sonner position="top-center" />

  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <Card class="mx-auto w-full max-w-[420px] shadow-xl border-0 transition-all duration-300 hover:shadow-2xl">
      <!-- Header -->
      <CardHeader class="space-y-4 pb-6">
        <CardTitle class="flex flex-col items-center gap-3">
          <AuthenticationCardLogo />
          <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">DOPA Check</h1>
          </div>
        </CardTitle>
        <CardDescription class="text-center text-lg font-medium text-gray-600">
          Bem-vindo de volta!
        </CardDescription>
      </CardHeader>

      <CardContent>
        <!-- Status Message -->
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
          {{ status }}
        </div>

        <!-- Login Tabs -->
        <Tabs v-model="activeTab" class="w-full">
          <TabsList class="grid w-full grid-cols-2 rounded-lg p-1 bg-gray-100">
            <TabsTrigger value="password" class="data-[state=active]:bg-white data-[state=active]:text-blue-600">
              Senha
            </TabsTrigger>
            <TabsTrigger value="login-link" class="data-[state=active]:bg-white data-[state=active]:text-blue-600">
              Link Mágico
            </TabsTrigger>
          </TabsList>

          <div class="mt-6">
            <!-- Password Login -->
            <TabsContent value="password" class="space-y-4">
              <form @submit.prevent="handlePasswordLogin">
                <div class="grid gap-4">
                  <!-- Email -->
                  <div class="grid gap-2">
                    <Label for="email" class="text-gray-700 font-medium">E-mail</Label>
                    <Input
                      id="email"
                      v-model="passwordForm.email"
                      type="email"
                      placeholder="seu@email.com"
                      required
                      autofocus
                      autocomplete="username"
                      class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                    <InputError :message="passwordForm.errors.email" />
                  </div>

                  <!-- Password -->
                  <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                      <Label for="password" class="text-gray-700 font-medium">Senha</Label>
                      <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-blue-600 hover:text-blue-700 hover:underline underline-offset-4"
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
                      class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                    <InputError :message="passwordForm.errors.password" />
                  </div>

                  <!-- Remember Me -->
                  <div class="flex items-center space-x-2">
                    <Checkbox
                      id="remember"
                      v-model:checked="passwordForm.remember"
                      name="remember"
                    />
                    <label for="remember" class="text-sm text-gray-600 cursor-pointer">
                      Lembrar-me
                    </label>
                  </div>

                  <Button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium"
                    :class="{ 'opacity-75': passwordForm.processing }"
                    :disabled="isProcessing"
                  >
                    {{ passwordForm.processing ? 'Entrando...' : 'Entrar' }}
                  </Button>
                </div>
              </form>
            </TabsContent>

            <!-- Login Link -->
            <TabsContent value="login-link" class="space-y-4">
              <div class="text-sm text-gray-600 bg-blue-50 border border-blue-100 rounded-lg p-3">
                📧 Enviaremos um link mágico para seu e-mail para entrar sem senha.
              </div>
              <form @submit.prevent="handleLoginLink">
                <div class="grid gap-4">
                  <div class="grid gap-2">
                    <Label for="login-link-email" class="text-gray-700 font-medium">E-mail</Label>
                    <Input
                      id="login-link-email"
                      v-model="loginLinkForm.email"
                      type="email"
                      required
                      placeholder="seu@email.com"
                      class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                    <InputError :message="loginLinkForm.errors.email" />
                  </div>

                  <Button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium"
                    :class="{ 'opacity-75': loginLinkForm.processing }"
                    :disabled="isProcessing"
                  >
                    {{ loginLinkForm.processing ? 'Enviando...' : 'Enviar Link Mágico' }}
                  </Button>
                </div>
              </form>
            </TabsContent>
          </div>
        </Tabs>

        <!-- OAuth Section -->
        <div v-if="hasOauthProviders" class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <span class="w-full border-t border-gray-200" />
            </div>
            <div class="relative flex justify-center text-xs uppercase">
              <span class="bg-white px-2 text-gray-500">
                Ou continue com
              </span>
            </div>
          </div>

          <div class="mt-6 grid gap-2">
            <SocialLoginButton
              v-for="provider in availableOauthProviders"
              :key="provider.slug"
              :provider="provider"
              :disabled="isProcessing"
            />
          </div>
        </div>

        <!-- Sign Up Link -->
        <div class="mt-6 text-center text-sm text-gray-600">
          Não tem uma conta?
          <Link
            :href="route('register')"
            class="font-medium text-blue-600 hover:text-blue-700 hover:underline underline-offset-4"
          >
            Cadastre-se
          </Link>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
