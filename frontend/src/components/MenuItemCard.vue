<template>
  <div class="card row" style="align-items: flex-start;">
    <div class="photo-thumb">{{ emoji }}</div>

    <div style="flex: 1; margin-left: 4px;">
      <div class="space-between">
        <h3 style="margin: 0;">{{ item.name }}</h3>
        <strong>RM {{ item.price.toFixed(2) }}</strong>
      </div>
      <p style="margin: 4px 0; color: var(--muted); font-size: 13px;">{{ item.description }}</p>

      <div v-if="item.tags?.length" style="margin-bottom: 8px;">
        <span v-for="tag in item.tags" :key="tag" class="tag">{{ tag }}</span>
      </div>

      <p v-if="!item.in_stock" style="color: var(--danger); font-size: 13px; margin: 0;">
        Out of stock
      </p>

      <button v-else-if="quantity === 0" class="btn secondary" style="width: auto;" @click="$emit('add', item)">
        + Add to cart
      </button>

      <div v-else class="qty-control">
        <button @click="cartStore.decreaseQuantity(item.menu_item_id)">−</button>
        <span>{{ quantity }}</span>
        <button @click="cartStore.increaseQuantity(item.menu_item_id)">+</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '../stores/cartStore.js'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['add'])

const cartStore = useCartStore()

const quantity = computed(() => {
  const cartItem = cartStore.items.find((i) => i.menu_item_id === props.item.menu_item_id)
  return cartItem ? cartItem.quantity : 0
})

// Lightweight emoji placeholder per category until real menu photos exist.
const EMOJI_BY_CATEGORY = {
  Coffee: '☕',
  Pastries: '🥐',
  Sandwiches: '🥪',
  Pizza: '🍕',
  Burgers: '🍔'
}

const emoji = computed(() => EMOJI_BY_CATEGORY[props.item.category] || '🍴')
</script>
