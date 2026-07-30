<script setup>
defineProps({
  icon: { type: [Object, Function], required: true },
  title: { type: String, required: true },
  value: { type: [String, Number], default: '—' },
  tone: { type: String, default: 'primary' },
})
</script>

<template>
  <article class="tn-card rounded-2xl bg-white/90 p-4 transition-transform duration-200 hover:-translate-y-0.5 sm:p-5">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">{{ title }}</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-ink">{{ value }}</p>
      </div>
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="{
        'bg-primary-50 text-primary-700': tone === 'primary',
        'bg-amber-50 text-amber-600': tone === 'warning',
        'bg-emerald-50 text-emerald-600': tone === 'success',
        'bg-red-50 text-red-600': tone === 'danger',
      }">
        <component :is="icon" class="h-5 w-5" aria-hidden="true" />
      </span>
    </div>
  </article>
</template>
