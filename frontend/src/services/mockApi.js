const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

const fileToEndpoint = {
  'vendors.json': '/vendors',
  'menuItems.json': '/menu-items',
  'menu.json': '/menu-items',
  'promotions.json': '/promotions',
  'notifications.json': '/notifications',
  'orders.json': '/orders',
  'orderItems.json': '/order-items',
  'reviews.json': '/reviews'
}

export async function getMockData(fileName) {
  const endpoint = fileToEndpoint[fileName]

  if (!endpoint) {
    const response = await fetch(`/data/${fileName}`)

    if (!response.ok) {
      throw new Error(`Unable to load ${fileName}`)
    }

    return response.json()
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`)

  if (!response.ok) {
    throw new Error(`Unable to load ${fileName} from API`)
  }

  return response.json()
}

export async function markNotificationAsRead(notificationId) {
  const response = await fetch(
    `${API_BASE_URL}/notifications/${notificationId}/read`,
    {
      method: 'PUT'
    }
  )

  if (!response.ok) {
    throw new Error('Unable to mark notification as read')
  }

  return response.json()
}

export async function createOrderApi(orderData) {
  const response = await fetch(`${API_BASE_URL}/orders`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })

  if (!response.ok) {
    throw new Error('Unable to create order')
  }

  return response.json()
}

export async function updateOrderStatusApi(orderId, status) {
  const response = await fetch(`${API_BASE_URL}/orders/${orderId}/status`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ status })
  })

  if (!response.ok) {
    throw new Error('Unable to update order status')
  }

  return response.json()
}

export async function createReviewApi(reviewData) {
  const response = await fetch(`${API_BASE_URL}/reviews`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(reviewData)
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.error || 'Unable to submit review')
  }

  return response.json()
}

export async function registerUserApi(userData) {
  const response = await fetch(`${API_BASE_URL}/register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(userData)
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.error || 'Unable to register account')
  }

  return response.json()
}

export async function loginUserApi(email, password) {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.error || 'Invalid email or password')
  }

  return response.json()
}