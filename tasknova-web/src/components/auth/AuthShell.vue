<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  compact: { type: Boolean, default: false },
})
</script>

<template>
  <div class="tn-auth-page flex min-h-screen items-center justify-center p-4 sm:p-6">
    <main class="w-full" :class="compact ? 'max-w-md' : 'max-w-lg'">
      <section class="tn-auth-card rounded-3xl p-6 sm:p-8">
        <header class="mb-7 text-center sm:mb-8">
          <img src="/brand/tasknova-logo.png" alt="TaskNova" class="tn-logo mx-auto mb-3 h-24 w-48" />
          <h1 class="font-display text-3xl font-bold text-brand-ink">{{ title }}</h1>
          <p class="mt-2 text-sm leading-6 text-brand-muted">{{ subtitle }}</p>
        </header>
        <slot />
      </section>
    </main>
  </div>
</template>
