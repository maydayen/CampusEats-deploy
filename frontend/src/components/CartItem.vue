<template>
  <div class="card">
    <div class="space-between">
      <div>
        <h3 style="margin: 0 0 2px;">{{ item.name }}</h3>
        <p style="margin: 0; color: var(--muted); font-size: 13px;">RM {{ item.price.toFixed(2) }} each</p>
      </div>
      <div class="qty-control">
        <button @click="$emit('decrease', item.menu_item_id)">−</button>
        <span>{{ item.quantity }}</span>
        <button @click="$emit('increase', item.menu_item_id)">+</button>
      </div>
    </div>
    <button class="btn secondary" style="margin-top: 10px;" @click="$emit('remove', item.menu_item_id)">
      Remove
    </button>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['increase', 'decrease', 'remove'])
</script>
