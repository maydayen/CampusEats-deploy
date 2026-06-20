<template>
  <div class="card">
    <strong>{{ promotion.discount_text }}</strong>
    <h3>{{ promotion.title }}</h3>
    <p>{{ promotion.description }}</p>
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
