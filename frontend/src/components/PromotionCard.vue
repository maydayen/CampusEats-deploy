<template>
  <div class="card" style="border-color: var(--primary);">
    <span class="badge">{{ promotion.discount_text }}</span>
    <h3 style="margin: 8px 0 4px;">{{ promotion.title }}</h3>
    <p style="margin: 0; color: var(--muted); font-size: 13px;">{{ promotion.description }}</p>
  </div>
</template>

<script setup>
defineProps({
  promotion: {
    type: Object,
    required: true
  }
})
</script>
