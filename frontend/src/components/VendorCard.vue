<template>
  <div class="card" @click="$emit('select', vendor)" style="cursor: pointer;">
    <div class="photo-banner">
      <img
        v-if="vendor.image_url"
        :src="vendor.image_url"
        :alt="vendor.name"
        class="vendor-photo"
      />
      <span v-else>{{ emoji }}</span>
    </div>

    <div class="space-between">
      <h3 style="margin: 0;">{{ vendor.name }}</h3>
      <span class="badge">★ {{ vendor.rating || 'New' }}</span>
    </div>

    <p style="margin: 4px 0; color: var(--muted); font-size: 13px;">
      <span v-if="vendor.prep_time">{{ vendor.prep_time }} · </span>📍 {{ vendor.location }}
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

const EMOJI_BY_NAME = {
  'Lemon Tree': '🍋',
  'Nasi Kandar Osman': '🍛',
  'Meranti Brew Cafe': '☕'
}

const emoji = computed(() => EMOJI_BY_NAME[props.vendor.name] || '🍴')
</script>

<style scoped>
.photo-banner {
  width: 100%;
  height: 140px;
  border-radius: 14px;
  overflow: hidden;
  background: linear-gradient(135deg, #f97316, #c2410c);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
  margin-bottom: 14px;
}

.vendor-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
</style>