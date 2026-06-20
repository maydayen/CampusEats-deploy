<template>
  <div class="card">
    <div class="space-between">
      <div>
        <h3>Order #{{ order.order_id }}</h3>
        <p>Status: {{ order.status }}</p>
        <small>Pickup: {{ order.pickup_at }}</small>
      </div>
      <strong>RM {{ Number(order.total).toFixed(2) }}</strong>
    </div>
  </div>
</template>

<script setup>
defineProps({
  order: {
    type: Object,
    required: true
  }
})
</script>
