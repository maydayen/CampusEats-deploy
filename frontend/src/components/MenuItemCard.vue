<template>
  <div class="card menu-item-card">
    <div class="menu-image-box">
      <img
        v-if="item.image_url && !imageError"
        :src="item.image_url"
        :alt="item.name"
        class="menu-item-image"
        @error="imageError = true"
      />

      <div v-else class="photo-thumb">
        {{ emoji }}
      </div>
    </div>

    <div class="menu-item-content">
      <div class="space-between menu-title-row">
        <h3>{{ item.name }}</h3>
        <strong>RM {{ Number(item.price).toFixed(2) }}</strong>
      </div>

      <p class="menu-description">
        {{ item.description }}
      </p>

      <div v-if="item.tags?.length" class="tag-list">
        <span v-for="tag in item.tags" :key="tag" class="tag">
          {{ tag }}
        </span>
      </div>

      <p v-if="!item.in_stock" class="out-stock">
        Out of stock
      </p>

      <button
        v-else-if="quantity === 0"
        class="btn secondary add-cart-btn"
        @click="$emit('add', item)"
      >
        + Add to cart
      </button>

      <div v-else class="qty-control">
        <button @click="cartStore.decreaseQuantity(item.menu_item_id)">
          −
        </button>
        <span>{{ quantity }}</span>
        <button @click="cartStore.increaseQuantity(item.menu_item_id)">
          +
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useCartStore } from '../stores/cartStore.js'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['add'])

const cartStore = useCartStore()
const imageError = ref(false)

const quantity = computed(() => {
  const cartItem = cartStore.items.find((i) => i.menu_item_id === props.item.menu_item_id)
  return cartItem ? cartItem.quantity : 0
})

const EMOJI_BY_CATEGORY = {
  Western: '🍗',
  Burger: '🍔',
  Pasta: '🍝',
  Drinks: '🥤',
  Mamak: '🫓',
  Rice: '🍛',
  Noodles: '🍜',
  Sandwich: '🥪',
  Wrap: '🌯',
  Snacks: '🧁',
  Pastry: '🥐',
  Coffee: '☕',
  Pastries: '🥐',
  Sandwiches: '🥪',
  Pizza: '🍕',
  Burgers: '🍔'
}

const emoji = computed(() => EMOJI_BY_CATEGORY[props.item.category] || '🍴')
</script>