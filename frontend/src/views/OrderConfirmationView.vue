<template>
  <main class="page">
    <h1 class="page-title">Order Placed</h1>

    <div class="card" v-if="orderStore.latestOrder">
      <h2>Thank you!</h2>
      <p>Your order has been placed successfully.</p>
      <p>Order ID: #{{ orderStore.latestOrder.order_id }}</p>
      <p>Pickup: {{ orderStore.latestOrder.pickup_at }}</p>
      <p>Total: RM {{ Number(orderStore.latestOrder.total).toFixed(2) }}</p>
    </div>

    <button class="btn" @click="$router.push('/orders')">View Order History</button>
  </main>
</template>

<script setup>
import { useOrderStore } from '../stores/orderStore'

const orderStore = useOrderStore()
</script>
