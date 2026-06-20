<template>
  <div class="card">
    <div class="space-between">
      <div>
        <h3>{{ item.name }}</h3>
        <p>{{ item.description }}</p>
        <strong>RM {{ item.price.toFixed(2) }}</strong>
      </div>
      <button class="btn" style="width: auto;" @click="$emit('add', item)">+</button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['add'])
</script>
