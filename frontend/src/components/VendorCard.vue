<template>
  <div class="card" @click="$emit('select', vendor)">
    <div class="space-between">
      <div>
        <h3>{{ vendor.name }}</h3>
        <p>{{ vendor.location }}</p>
        <small>{{ vendor.prep_time }} · ⭐ {{ vendor.rating }}</small>
      </div>
      <strong>Order</strong>
    </div>
  </div>
</template>

<script setup>
defineProps({
  vendor: {
    type: Object,
    required: true
  }
})

defineEmits(['select'])
</script>
