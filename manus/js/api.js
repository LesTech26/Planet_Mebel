// API client — единый интерфейс для всех запросов

const API_BASE = '/api';

// Флаг: использовать мок-данные или реальный API
const USE_MOCK = true;

// Мок-данные для разработки
const MOCK_PRODUCTS = {
  items: [
    { id: 1, name: 'Диван Модерн', article: 'PM-00001', category_id: 1, category_name: 'Диваны', brand: 'Лес Тех', price: 89900, old_price: 114900, rating: 4.8, review_count: 12, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&q=80', is_hit: true, is_new: false, stock: 15 },
    { id: 2, name: 'Кресло Лофт', article: 'PM-00002', category_id: 2, category_name: 'Кресла', brand: 'Лес Тех', price: 34500, old_price: null, rating: 4.5, review_count: 8, image: 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=600&q=80', is_hit: true, is_new: true, stock: 7 },
    { id: 3, name: 'Кровать Флоренция', article: 'PM-00003', category_id: 3, category_name: 'Кровати', brand: 'Премьер', price: 67800, old_price: 79000, rating: 4.9, review_count: 24, image: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600&q=80', is_hit: true, is_new: false, stock: 3 },
    { id: 4, name: 'Шкаф Классик', article: 'PM-00004', category_id: 4, category_name: 'Шкафы', brand: 'Лес Тех', price: 48200, old_price: null, rating: 4.3, review_count: 6, image: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80', is_hit: false, is_new: false, stock: 12 },
    { id: 5, name: 'Стол Дуб', article: 'PM-00005', category_id: 5, category_name: 'Столы', brand: 'Массив', price: 29900, old_price: 35000, rating: 4.6, review_count: 15, image: 'https://images.unsplash.com/photo-1530018352490-c6eef07fd7e0?w=600&q=80', is_hit: true, is_new: true, stock: 8 },
    { id: 6, name: 'Стул Тюльпан', article: 'PM-00006', category_id: 6, category_name: 'Стулья', brand: 'Массив', price: 12800, old_price: 15000, rating: 4.4, review_count: 22, image: 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=600&q=80', is_hit: false, is_new: false, stock: 20 },
    { id: 7, name: 'Диван Берген', article: 'PM-00007', category_id: 1, category_name: 'Диваны', brand: 'Скандинавия', price: 104000, old_price: null, rating: 4.9, review_count: 31, image: 'https://images.unsplash.com/photo-1567016432779-094069958ea5?w=600&q=80', is_hit: true, is_new: true, stock: 5 },
    { id: 8, name: 'Консоль Арт', article: 'PM-00008', category_id: 7, category_name: 'Прихожая', brand: 'Лес Тех', price: 21500, old_price: null, rating: 4.2, review_count: 4, image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80', is_hit: false, is_new: false, stock: 10 },
    { id: 9, name: 'Диван Мальмо', article: 'PM-00009', category_id: 1, category_name: 'Диваны', brand: 'Скандинавия', price: 118000, old_price: null, rating: 4.7, review_count: 18, image: 'https://images.unsplash.com/photo-1567016432779-094069958ea5?w=600&q=80', is_hit: false, is_new: true, stock: 2 },
    { id: 10, name: 'Стул Тюльпан белый', article: 'PM-00010', category_id: 6, category_name: 'Стулья', brand: 'Массив', price: 13400, old_price: 15800, rating: 4.5, review_count: 9, image: 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=600&q=80', is_hit: false, is_new: true, stock: 15 },
    { id: 11, name: 'Полка Вальс', article: 'PM-00011', category_id: 8, category_name: 'Стеллажи', brand: 'Лес Тех', price: 18400, old_price: null, rating: 4.3, review_count: 5, image: 'https://images.unsplash.com/photo-1558618047-f4e80c0d4783?w=600&q=80', is_hit: false, is_new: true, stock: 7 },
    { id: 12, name: 'Торшер Лайт', article: 'PM-00012', category_id: 9, category_name: 'Освещение', brand: 'АртЛайт', price: 9600, old_price: 11200, rating: 4.6, review_count: 14, image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80', is_hit: false, is_new: true, stock: 9 }
  ],
  total: 48,
  current_page: 1,
  last_page: 4,
  per_page: 12,
  filters: {
    price_min: 5000,
    price_max: 250000,
    categories: [
      { id: 1, name: 'Диваны', count: 12 },
      { id: 2, name: 'Кресла', count: 8 },
      { id: 3, name: 'Кровати', count: 6 },
      { id: 4, name: 'Шкафы', count: 5 },
      { id: 5, name: 'Столы', count: 7 },
      { id: 6, name: 'Стулья', count: 10 }
    ],
    brands: ['Лес Тех', 'Премьер', 'Массив', 'Скандинавия', 'АртЛайт'],
    materials: ['дерево', 'металл', 'ткань', 'комбинированный']
  }
};

// Мок-данные для корзины (через localStorage, но имитируем API)
function getCartFromStorage() {
  const cart = localStorage.getItem('pm_cart');
  return cart ? JSON.parse(cart) : [];
}

// Основной API-клиент
export const api = {
  // Получение товаров с фильтрацией
  async getProducts(params = {}) {
    if (USE_MOCK) {
      await delay(300);
      let items = [...MOCK_PRODUCTS.items];
      const { category, price_min, price_max, material, sort, page = 1, limit = 12 } = params;
      
      // Фильтрация
      if (category) {
        items = items.filter(p => p.category_id === parseInt(category));
      }
      if (price_min) {
        items = items.filter(p => p.price >= parseInt(price_min));
      }
      if (price_max) {
        items = items.filter(p => p.price <= parseInt(price_max));
      }
      if (material) {
        // Мок-фильтр по материалу
      }
      
      // Сортировка
      if (sort === 'price_asc') {
        items.sort((a, b) => a.price - b.price);
      } else if (sort === 'price_desc') {
        items.sort((a, b) => b.price - a.price);
      } else if (sort === 'new') {
        items.sort((a, b) => (b.is_new ? 1 : 0) - (a.is_new ? 1 : 0));
      } else if (sort === 'popular') {
        items.sort((a, b) => b.review_count - a.review_count);
      }
      
      const total = items.length;
      const start = (page - 1) * limit;
      const paginatedItems = items.slice(start, start + limit);
      
      return {
        items: paginatedItems,
        total,
        current_page: page,
        last_page: Math.ceil(total / limit),
        per_page: limit,
        filters: MOCK_PRODUCTS.filters
      };
    }
    
    const queryParams = new URLSearchParams(params);
    const response = await fetch(`${API_BASE}/products?${queryParams}`);
    if (!response.ok) throw new Error('Ошибка загрузки товаров');
    return response.json();
  },
  
  // Получение корзины
  async getCart() {
    if (USE_MOCK) {
      await delay(200);
      const cart = getCartFromStorage();
      const items = cart.map(item => ({
        id: item._key,
        product_id: item.productId,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        options: item.options,
        total: item.price * item.quantity
      }));
      const subtotal = items.reduce((sum, i) => sum + i.total, 0);
      const promo_discount = 0;
      const delivery_cost = subtotal >= 10000 ? 0 : 500;
      return {
        items,
        subtotal,
        promo_discount,
        delivery_cost,
        total: subtotal - promo_discount + delivery_cost
      };
    }
    
    const response = await fetch(`${API_BASE}/cart`);
    return response.json();
  },
  
  // Добавление в корзину
  async addToCart(productId, quantity = 1, options = {}) {
    if (USE_MOCK) {
      await delay(200);
      const cart = getCartFromStorage();
      const product = MOCK_PRODUCTS.items.find(p => p.id === productId);
      if (!product) throw new Error('Товар не найден');
      
      const key = `${productId}__${options.color || ''}__${options.material || ''}`;
      const existing = cart.find(item => item._key === key);
      
      if (existing) {
        existing.quantity += quantity;
      } else {
        cart.push({
          _key: key,
          productId,
          name: product.name,
          price: product.price,
          quantity,
          options
        });
      }
      
      localStorage.setItem('pm_cart', JSON.stringify(cart));
      return { success: true, cart: await api.getCart() };
    }
    
    const response = await fetch(`${API_BASE}/cart/add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, quantity, options })
    });
    return response.json();
  },
  
  // Обновление количества в корзине
  async updateCartItem(itemKey, quantity) {
    if (USE_MOCK) {
      await delay(200);
      const cart = getCartFromStorage();
      const item = cart.find(i => i._key === itemKey);
      if (item) {
        if (quantity <= 0) {
          const index = cart.indexOf(item);
          cart.splice(index, 1);
        } else {
          item.quantity = quantity;
        }
        localStorage.setItem('pm_cart', JSON.stringify(cart));
      }
      return { success: true, cart: await api.getCart() };
    }
    
    const response = await fetch(`${API_BASE}/cart/update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_key: itemKey, quantity })
    });
    return response.json();
  },
  
  // Удаление из корзины
  async removeCartItem(itemKey) {
    return api.updateCartItem(itemKey, 0);
  },
  
  // Очистка корзины
  async clearCart() {
    if (USE_MOCK) {
      await delay(200);
      localStorage.setItem('pm_cart', '[]');
      return { success: true, cart: await api.getCart() };
    }
    
    const response = await fetch(`${API_BASE}/cart/clear`, {
      method: 'POST'
    });
    return response.json();
  },
  
  // Применение промокода
  async applyPromo(code) {
    if (USE_MOCK) {
      await delay(400);
      // Мок-промокоды
      const promos = {
        'WELCOME5': { type: 'percent', value: 5, min_amount: 0 },
        'SAVE1000': { type: 'fixed', value: 1000, min_amount: 5000 },
        'FREESHIP': { type: 'percent', value: 0, min_amount: 10000, free_shipping: true }
      };
      
      const promo = promos[code.toUpperCase()];
      if (!promo) {
        throw new Error('Промокод не найден');
      }
      
      return {
        success: true,
        promo: { code: code.toUpperCase(), ...promo }
      };
    }
    
    const response = await fetch(`${API_BASE}/promo/apply`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code })
    });
    return response.json();
  },
  
  // Создание заказа
  async createOrder(orderData) {
    if (USE_MOCK) {
      await delay(800);
      const orderId = Math.floor(Math.random() * 100000);
      // Очищаем корзину после успешного заказа
      localStorage.setItem('pm_cart', '[]');
      return {
        success: true,
        order_id: orderId,
        message: 'Заказ успешно оформлен'
      };
    }
    
    const response = await fetch(`${API_BASE}/order/create`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(orderData)
    });
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Ошибка оформления заказа');
    }
    return response.json();
  },
  
  // Получение недоступных дат для доставки
  async getUnavailableDates() {
    if (USE_MOCK) {
      await delay(200);
      // Возвращаем ближайшие выходные как недоступные
      const unavailable = [];
      const today = new Date();
      for (let i = 1; i <= 14; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        if (date.getDay() === 0 || date.getDay() === 6) {
          unavailable.push(date.toISOString().split('T')[0]);
        }
      }
      return { unavailable_dates: unavailable };
    }
    
    const response = await fetch(`${API_BASE}/delivery/unavailable-dates`);
    return response.json();
  }
};

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}