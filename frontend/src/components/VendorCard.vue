<template>
  <div class="card" @click="$emit('select', vendor)" style="cursor: pointer;">
    <div class="photo-banner">{{ emoji }}</div>

    <div class="space-between">
      <h3 style="margin: 0;">{{ vendor.name }}</h3>
      <span class="badge">★ {{ vendor.rating || 'New' }}</span>
    </div>
    <p style="margin: 4px 0; color: var(--muted); font-size: 13px;">
      {{ vendor.prep_time }} · 📍 {{ vendor.location }}
    </p>
    <span v-if="vendor.status !== 'approved'" class="badge" :class="`status-${vendor.status}`">
      {{ vendor.status }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  vendor: {
    type: Object,
    required: true
  }
})

defineEmits(['select'])

// No real photo assets yet (image_url files don't exist), so show a
// food emoji per vendor as a lightweight placeholder until real vendor
// photos are uploaded in PR3.
const EMOJI_BY_NAME = {
  'The Daily Grind': '☕',
  'The Commons Hall': '🍽️',
  'Library Chill Zone': '🥤'
}

const emoji = computed(() => EMOJI_BY_NAME[props.vendor.name] || '🍴')
</script>
