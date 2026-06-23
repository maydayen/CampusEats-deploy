<template>
  <main class="page">
    <h1 class="page-title">Order Confirmed</h1>

    <div class="card" v-if="orderStore.latestOrder">
      <div class="empty-state">
        <span class="emoji">✅</span>
        <h2>Order placed successfully!</h2>
        <p>Your vendor has received the order.</p>
      </div>

      <hr />

      <p><strong>Order ID:</strong> #{{ orderStore.latestOrder.order_id }}</p>
      <p><strong>Status:</strong> {{ orderStore.latestOrder.status }}</p>
      <p><strong>Pickup:</strong> {{ orderStore.latestOrder.pickup_at }}</p>
      <p><strong>Total:</strong> RM {{ Number(orderStore.latestOrder.total).toFixed(2) }}</p>
    </div>

    <div v-else class="card">
      <p>No latest order found.</p>
    </div>

    <button class="btn" @click="$router.push('/orders')">
      View Order History
    </button>

    <button class="btn secondary" style="margin-top: 10px;" @click="$router.push('/home')">
      Back to Home
    </button>
  </main>
</template>

<script setup>
import { useOrderStore } from '../stores/orderStore.js'

const orderStore = useOrderStore()
</script>