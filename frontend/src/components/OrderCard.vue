<template>
  <div class="card order-card">
    <div class="order-card-header">
      <div>
        <h3>Order #{{ order.order_id }}</h3>
        <p class="muted">{{ formattedDate }}</p>
      </div>

      <span class="status-badge" :class="statusClass">
        {{ statusText }}
      </span>
    </div>

    <div class="order-card-body">
      <div class="order-info-row">
        <span>Pickup Time</span>
        <strong>{{ order.pickup_at }}</strong>
      </div>

      <div class="order-info-row">
        <span>Subtotal</span>
        <strong>RM {{ Number(order.subtotal || 0).toFixed(2) }}</strong>
      </div>

      <div class="order-info-row">
        <span>Service Fee</span>
        <strong>RM {{ Number(order.service_fee || 0).toFixed(2) }}</strong>
      </div>

      <div class="order-info-row total-row">
        <span>Total</span>
        <strong>RM {{ Number(order.total || 0).toFixed(2) }}</strong>
      </div>
    </div>

    <div class="order-progress">
      <div
        v-for="step in statusSteps"
        :key="step"
        class="progress-step"
        :class="{ active: isStepActive(step) }"
      >
        <span></span>
        <small>{{ step }}</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

const statusSteps = ['placed', 'preparing', 'ready', 'collected']

const statusText = computed(() => {
  const status = props.order.status || 'placed'
  return status.charAt(0).toUpperCase() + status.slice(1)
})

const statusClass = computed(() => {
  return `status-${props.order.status || 'placed'}`
})

const formattedDate = computed(() => {
  if (!props.order.created_at) {
    return 'Recently placed'
  }

  const date = new Date(props.order.created_at)

  if (Number.isNaN(date.getTime())) {
    return props.order.created_at
  }

  return date.toLocaleString()
})

function isStepActive(step) {
  const currentIndex = statusSteps.indexOf(props.order.status)
  const stepIndex = statusSteps.indexOf(step)

  return stepIndex <= currentIndex
}
</script>