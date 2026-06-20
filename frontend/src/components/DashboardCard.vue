<template>
  <div class="card">
    <small>{{ label }}</small>
    <h2>{{ value }}</h2>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number]
})
</script>
