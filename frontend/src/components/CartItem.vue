<template>
  <div class="card">
    <div class="space-between">
      <div>
        <h3>{{ item.name }}</h3>
        <p>RM {{ item.price.toFixed(2) }}</p>
      </div>
      <div class="row">
        <button @click="$emit('decrease', item.menu_item_id)">-</button>
        <span>{{ item.quantity }}</span>
        <button @click="$emit('increase', item.menu_item_id)">+</button>
      </div>
    </div>
    <button class="btn secondary" @click="$emit('remove', item.menu_item_id)">Remove</button>
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
