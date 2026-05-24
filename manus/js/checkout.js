// Checkout page logic

import { api } from './api.js';
import { showToast, formatPrice, showSpinner, hideSpinner } from './utils.js';

let cartData = null;
let unavailableDates = [];

// DOM elements
let submitBtn;

export async function initCheckout() {
  submitBtn = document.getElementById('submitOrder');
  
  // Load cart data
  await loadCart();
  
  // Init flatpickr
  await initDatePicker();
  
  // Bind delivery options
  bindDeliveryOptions();
  
  // Bind payment options
  bindPaymentOptions();
  
  // Bind form validation
  bindFormValidation();
  
  // Bind submit
  if (submitBtn) {
    submitBtn.addEventListener('click', submitOrder);
  }
  
  // Auto-fill from localStorage if user is logged in
  autoFillUserData();
}

async function loadCart() {
  try {
    cartData = await api.getCart();
    renderOrderSummary();
  } catch (error) {
    console.error('Error loading cart:', error);
    showToast('Ошибка загрузки корзины', false);
  }
}

function renderOrderSummary() {
  const summaryContainer = document.getElementById('orderItemsList');
  const subtotalSpan = document.getElementById('summarySubtotal');
  const discountSpan = document.getElementById('summaryDiscount');
  const deliverySpan = document.getElementById('summaryDelivery');
  const totalSpan = document.getElementById('summaryTotal');
  
  if (!cartData || !cartData.items || cartData.items.length === 0) {
    if (summaryContainer) {
      summaryContainer.innerHTML = '<div style="text-align:center;padding:20px">Корзина пуста</div>';
    }
    if (submitBtn) submitBtn.disabled = true;
    return;
  }
  
  if (submitBtn) submitBtn.disabled = false;
  
  // Render items
  if (summaryContainer) {
    summaryContainer.innerHTML = cartData.items.map(item => `
      <div class="order-item">
        <span class="order-item-name">${escapeHtml(item.name)} × ${item.quantity}</span>
        <span class="order-item-price">${formatPrice(item.total)}</span>
      </div>
    `).join('');
  }
  
  // Update totals
  const subtotal = cartData.subtotal || 0;
  const discount = cartData.promo_discount || 0;
  const delivery = cartData.delivery_cost || 0;
  const total = cartData.total || subtotal - discount + delivery;
  
  if (subtotalSpan) subtotalSpan.textContent = formatPrice(subtotal);
  if (discountSpan) {
    if (discount > 0) {
      discountSpan.textContent = `−${formatPrice(discount)}`;
      discountSpan.style.color = 'var(--green)';
    } else {
      discountSpan.textContent = formatPrice(0);
    }
  }
  if (deliverySpan) {
    deliverySpan.textContent = delivery === 0 ? 'Бесплатно' : formatPrice(delivery);
  }
  if (totalSpan) totalSpan.textContent = formatPrice(total);
}

async function initDatePicker() {
  // Load unavailable dates from API
  try {
    const data = await api.getUnavailableDates();
    unavailableDates = data.unavailable_dates || [];
  } catch (error) {
    console.error('Error loading unavailable dates:', error);
  }
  
  // Flatpickr will be initialized when needed
  // For now, we'll use a simple date input with min date
  const dateInput = document.getElementById('deliveryDate');
  if (dateInput) {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    dateInput.min = tomorrow.toISOString().split('T')[0];
    
    // Disable weekends and unavailable dates
    dateInput.addEventListener('change', () => {
      const selected = dateInput.value;
      const day = new Date(selected).getDay();
      if (day === 0 || day === 6) {
        showToast('Доставка в выходные недоступна', false);
        dateInput.value = '';
      }
      if (unavailableDates.includes(selected)) {
        showToast('Эта дата недоступна для доставки', false);
        dateInput.value = '';
      }
    });
  }
}

function bindDeliveryOptions() {
  const options = document.querySelectorAll('input[name="delivery"]');
  const addressField = document.getElementById('addressField');
  
  options.forEach(opt => {
    opt.addEventListener('change', () => {
      // Update selected style
      document.querySelectorAll('.delivery-option').forEach(el => {
        el.classList.remove('selected');
      });
      opt.closest('.delivery-option')?.classList.add('selected');
      
      // Show/hide address field
      if (addressField) {
        if (opt.value === 'courier') {
          addressField.classList.add('show');
        } else {
          addressField.classList.remove('show');
        }
      }
      
      // Update delivery cost in summary
      updateDeliveryCost();
    });
  });
}

function bindPaymentOptions() {
  const options = document.querySelectorAll('input[name="payment"]');
  
  options.forEach(opt => {
    opt.addEventListener('change', () => {
      document.querySelectorAll('.payment-option').forEach(el => {
        el.classList.remove('selected');
      });
      opt.closest('.payment-option')?.classList.add('selected');
    });
  });
}

function updateDeliveryCost() {
  const selectedDelivery = document.querySelector('input[name="delivery"]:checked');
  let deliveryCost = 0;
  
  if (selectedDelivery && selectedDelivery.value === 'courier') {
    const subtotal = cartData?.subtotal || 0;
    deliveryCost = subtotal >= 10000 ? 0 : 500;
  }
  
  // Update summary
  const deliverySpan = document.getElementById('summaryDelivery');
  const totalSpan = document.getElementById('summaryTotal');
  
  if (deliverySpan) {
    deliverySpan.textContent = deliveryCost === 0 ? 'Бесплатно' : formatPrice(deliveryCost);
  }
  
  if (totalSpan && cartData) {
    const subtotal = cartData.subtotal || 0;
    const discount = cartData.promo_discount || 0;
    const total = subtotal - discount + deliveryCost;
    totalSpan.textContent = formatPrice(total);
  }
}

function bindFormValidation() {
  // Phone mask
  const phoneInput = document.getElementById('phone');
  if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);
      if (value.length === 11) {
        e.target.value = value.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '+$1 ($2) $3-$4-$5');
      } else {
        e.target.value = value;
      }
    });
  }
  
  // Real-time validation
  const fields = ['fullname', 'phone', 'email'];
  fields.forEach(field => {
    const input = document.getElementById(field);
    if (input) {
      input.addEventListener('blur', () => validateField(field));
      input.addEventListener('input', () => {
        const error = document.getElementById(`${field}Error`);
        if (error) error.classList.remove('show');
        input.classList.remove('error');
      });
    }
  });
}

function validateField(field) {
  const input = document.getElementById(field);
  const error = document.getElementById(`${field}Error`);
  
  if (!input || !error) return true;
  
  let isValid = true;
  let message = '';
  
  switch (field) {
    case 'fullname':
      isValid = input.value.trim().length >= 2;
      message = 'Введите ваше полное имя';
      break;
    case 'phone':
      const digits = input.value.replace(/\D/g, '');
      isValid = digits.length === 11;
      message = 'Введите номер телефона (11 цифр)';
      break;
    case 'email':
      isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
      message = 'Введите корректный email';
      break;
  }
  
  if (!isValid) {
    input.classList.add('error');
    error.textContent = message;
    error.classList.add('show');
  } else {
    input.classList.remove('error');
    error.classList.remove('show');
  }
  
  return isValid;
}

function validateForm() {
  const fields = ['fullname', 'phone', 'email'];
  let isValid = true;
  
  fields.forEach(field => {
    if (!validateField(field)) isValid = false;
  });
  
  // Validate delivery
  const delivery = document.querySelector('input[name="delivery"]:checked');
  if (!delivery) {
    showToast('Выберите способ доставки', false);
    isValid = false;
  }
  
  // Validate address for courier
  if (delivery && delivery.value === 'courier') {
    const address = document.getElementById('address')?.value.trim();
    if (!address) {
      showToast('Введите адрес доставки', false);
      isValid = false;
    }
  }
  
  // Validate payment
  const payment = document.querySelector('input[name="payment"]:checked');
  if (!payment) {
    showToast('Выберите способ оплаты', false);
    isValid = false;
  }
  
  // Validate delivery date
  const deliveryDate = document.getElementById('deliveryDate')?.value;
  if (!deliveryDate && delivery && delivery.value === 'courier') {
    showToast('Выберите дату доставки', false);
    isValid = false;
  }
  
  return isValid;
}

function getFormData() {
  const delivery = document.querySelector('input[name="delivery"]:checked');
  const payment = document.querySelector('input[name="payment"]:checked');
  const register = document.getElementById('register')?.checked;
  
  const data = {
    fullname: document.getElementById('fullname')?.value.trim(),
    phone: document.getElementById('phone')?.value.trim(),
    email: document.getElementById('email')?.value.trim(),
    delivery_method: delivery?.value,
    payment_method: payment?.value,
    comment: document.getElementById('comment')?.value.trim(),
    register: register || false
  };
  
  if (delivery?.value === 'courier') {
    data.delivery_address = document.getElementById('address')?.value.trim();
    data.delivery_date = document.getElementById('deliveryDate')?.value;
  }
  
  return data;
}

function autoFillUserData() {
  const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
  if (user) {
    const nameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    
    if (nameInput && user.name) nameInput.value = user.name;
    if (emailInput && user.email) emailInput.value = user.email;
  }
}

async function submitOrder() {
  if (!validateForm()) return;
  
  if (!cartData || !cartData.items || cartData.items.length === 0) {
    showToast('Корзина пуста', false);
    return;
  }
  
  const formData = getFormData();
  
  // Disable button to prevent double submit
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Оформление...';
  }
  
  try {
    const result = await api.createOrder(formData);
    if (result.success && result.order_id) {
      // Clear cart from localStorage
      localStorage.setItem('pm_cart', '[]');
      // Redirect to success page
      window.location.href = `order-success.html?order_id=${result.order_id}`;
    } else {
      throw new Error(result.message || 'Ошибка оформления заказа');
    }
  } catch (error) {
    console.error('Order error:', error);
    showToast(error.message || 'Ошибка оформления заказа', false);
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Оформить заказ';
    }
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

// Export
window.initCheckout = initCheckout;