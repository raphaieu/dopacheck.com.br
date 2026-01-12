<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  status: { type: Number, default: 404 },
  message: { type: String, default: 'Página não encontrada' },
})
</script>

<template>
  <Head title="Página não encontrada" />

  <main class="error404 relative min-h-screen overflow-hidden text-white">
    <!-- Bubbles -->
    <div class="bubble" aria-hidden="true" />
    <div class="bubble" aria-hidden="true" />
    <div class="bubble" aria-hidden="true" />
    <div class="bubble" aria-hidden="true" />
    <div class="bubble" aria-hidden="true" />

    <div class="main relative z-10 px-6 text-center">
      <h1 class="text-[95px] font-extrabold leading-none drop-shadow-[0_30px_10px_rgba(0,0,0,0.15)]">
        404
      </h1>
      <p class="mt-2 text-[18px] drop-shadow-[0_30px_10px_rgba(0,0,0,0.15)]">
        Parece que você está perdido...
        <br>
        Que tal se encontrar?
      </p>

      <Link
        href="/"
        class="mt-6 inline-flex items-center justify-center rounded-[20px] bg-gradient-to-b from-[#EC5DC1] to-[#D61A6F] px-4 py-2.5 text-sm font-semibold tracking-wide uppercase shadow-[0_30px_15px_rgba(0,0,0,0.15)] outline-none"
      >
        Ir pra Home
      </Link>
    </div>
  </main>
</template>

<style scoped>
/* Base gradients */
.error404 {
  background: linear-gradient(#203075, #24357f);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Varela Round', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial,
    'Apple Color Emoji', 'Segoe UI Emoji';
  text-shadow: 0 30px 10px rgba(0, 0, 0, 0.15);
}

/* Big circular gradient layers (ported from html/body pseudo elements) */
.error404::before,
.error404::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
}

/* 60vmax */
.error404::before {
  height: 60vmax;
  width: 60vmax;
  z-index: 0;
  background: linear-gradient(#203075, #24357f);
}

/* 40vmax inverted */
.error404::after {
  height: 40vmax;
  width: 40vmax;
  z-index: 0;
  background: linear-gradient(#24357f, #203075);
}

/* Extra outer circles as real elements to mimic html::before/html::after */
.error404 > .main::before,
.error404 > .main::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  pointer-events: none;
}

.error404 > .main::before {
  height: 105vmax;
  width: 105vmax;
  z-index: -4;
  background: linear-gradient(#24357f, #203075);
}

.error404 > .main::after {
  height: 80vmax;
  width: 80vmax;
  z-index: -3;
  background: linear-gradient(#203075, #24357f);
}

.main {
  position: relative;
  z-index: 5;
  text-align: center;
}

/* Bubbles */
.bubble {
  background: linear-gradient(#ec5dc1, #d61a6f);
  border-radius: 50%;
  box-shadow: 0 30px 15px rgba(0, 0, 0, 0.15);
  position: absolute;
}

.bubble::before {
  content: '';
  background: linear-gradient(#ec5dc1, #d61a6f);
  border-radius: 50%;
  box-shadow: 0 30px 15px rgba(0, 0, 0, 0.15);
  position: absolute;
}

.bubble:nth-of-type(1) {
  top: 15vh;
  left: 15vw;
  height: 22vmin;
  width: 22vmin;
}
.bubble:nth-of-type(1)::before {
  width: 13vmin;
  height: 13vmin;
  bottom: -25vh;
  right: -10vmin;
}

.bubble:nth-of-type(2) {
  top: 20vh;
  left: 38vw;
  height: 10vmin;
  width: 10vmin;
}
.bubble:nth-of-type(2)::before {
  width: 5vmin;
  height: 5vmin;
  bottom: -10vh;
  left: -8vmin;
}

.bubble:nth-of-type(3) {
  top: 12vh;
  right: 30vw;
  height: 13vmin;
  width: 13vmin;
}
.bubble:nth-of-type(3)::before {
  width: 3vmin;
  height: 3vmin;
  bottom: -15vh;
  left: -18vmin;
  z-index: 6;
}

.bubble:nth-of-type(4) {
  top: 25vh;
  right: 18vw;
  height: 18vmin;
  width: 18vmin;
}
.bubble:nth-of-type(4)::before {
  width: 7vmin;
  height: 7vmin;
  bottom: -10vmin;
  left: -15vmin;
}

.bubble:nth-of-type(5) {
  top: 60vh;
  right: 18vw;
  height: 28vmin;
  width: 28vmin;
}
.bubble:nth-of-type(5)::before {
  width: 10vmin;
  height: 10vmin;
  bottom: 5vmin;
  left: -25vmin;
}
</style>


