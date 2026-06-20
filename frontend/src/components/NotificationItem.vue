<template>
  <div class="card">
    <div class="space-between">
      <div>
        <h3>{{ notification.title }}</h3>
        <p>{{ notification.message }}</p>
      </div>
      <button v-if="!notification.is_read" class="btn" style="width: auto;" @click="$emit('read', notification.notification_id)">
        Read
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  notification: {
    type: Object,
    required: true
  }
})

defineEmits(['read'])
</script>
