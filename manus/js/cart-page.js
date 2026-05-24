// Cart page logic

import { api } from './api.js';
import { showToast, formatPrice, showSpinner, hideSpinner } from './utils.js';

let currentCart = null;
let appliedPromo = null;

// DOM elements
let cartTableBody;
let cartSummary;
let emptyCartBlock;

export async function initCartPage() {
  cartTableBody = document.getElementById('cartTableBody');
  cartSummary = document.getElementById('cartSummary');
  emptyCartBlock = document.getElementById('emptyCart');
  
  await loadCart();
  
  // Bind promo form
  const promoForm = document.getElementById('promoForm');
  if (promoForm) {
    promoForm.addEventListener('submit', (e) => {
      e.preventDefault();
      applyPromo();
    });
  }
  
  // Bind clear cart
  const clearCartBtn = document.getElementById('clearCart');
  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', clearCart);
  }
  
  // Bind checkout
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      window.location.href = 'checkout.html';
    });
  }
}

async function loadCart() {
  try {
    const cart = await api.getCart();
    currentCart = cart;
    renderCart();
  } catch (error) {
    console.error('Error loading cart:', error);
    showToast('Ошибка загрузки корзины', false);
  }
}

function renderCart() {
  const hasItems = currentCart.items && currentCart.items.length > 0;
  
  if (!hasItems) {
    if (cartTableBody) cartTableBody.closest('.cart-table').style.display = 'none';
    if (cartSummary) cartSummary.style.display = 'none';
    if (emptyCartBlock) emptyCartBlock.style.display = 'block';
    return;
  }
  
  if (cartTableBody) cartTableBody.closest('.cart-table').style.display = 'table';
  if (cartSummary) cartSummary.style.display = 'block';
  if (emptyCartBlock) emptyCartBlock.style.display = 'none';
  
  // Render table rows
  if (cartTableBody) {
    cartTableBody.innerHTML = currentCart.items.map(item => renderCartRow(item)).join('');
  }
  
  // Render summary
  if (cartSummary) {
    renderSummary();
  }
  
  // Bind row events
  bindRowEvents();
}

function renderCartRow(item) {
  const hasOptions = item.options && (item.options.color || item.options.material);
  const optionsText = hasOptions ? `${item.options.color || ''} ${item.options.material || ''}`.trim() : '';
  
  return `
    <tr data-item-key="${item.id}">
      <td>
        <div class="cart-product-cell">
          <div class="cart-product-img" style="background-image: url('https://via.placeholder.com/80?text=Фото')"></div>
          <div class="cart-product-info">
            <a href="product.html?id=${item.product_id}" class="cart-product-name">${escapeHtml(item.name)}</a>
            ${optionsText ? `<div class="cart-product-options">${escapeHtml(optionsText)}</div>` : ''}
          </div>
        </div>
      </td>
      <td class="cart-price">${formatPrice(item.price)}</td>
      <td>
        <div class="cart-quantity">
          <button class="cart-qty-btn" data-action="decr" data-key="${item.id}">−</button>
          <input type="text" class="cart-qty-input" value="${item.quantity}" data-key="${item.id}" readonly>
          <button class="cart-qty-btn" data-action="incr" data-key="${item.id}">+</button>
        </div>
      </td>
      <td class="cart-subtotal">${formatPrice(item.total)}</td>
      <td>
        <button class="cart-remove" data-action="remove" data-key="${item.id}">✕</button>
      </td>
    </tr>
  `;
}

function renderSummary() {
  const subtotal = currentCart.subtotal || 0;
  const discount = currentCart.promo_discount || 0;
  const delivery = currentCart.delivery_cost || 0;
  const total = currentCart.total || subtotal - discount + delivery;
  
  const summaryHtml = `
    <h3 class="summary-title">Ваш заказ</h3>
    <div class="summary-row">
      <span>Товары (${currentCart.items.length})</span>
      <span>${formatPrice(subtotal)}</span>
    </div>
    ${discount > 0 ? `
    <div class="summary-row summary-discount">
      <span>Скидка</span>
      <span>−${formatPrice(discount)}</span>
    </div>
    ` : ''}
    <div class="summary-row">
      <span>Доставка</span>
      <span>${delivery === 0 ? 'Бесплатно' : formatPrice(delivery)}</span>
    </div>
    <div class="summary-row total">
      <span>Итого</span>
      <span>${formatPrice(total)}</span>
    </div>
    <div class="promo-section">
      <div class="promo-input-group">
        <input type="text" id="promoCode" class="promo-input" placeholder="Промокод" ${appliedPromo ? 'disabled' : ''}>
        <button class="promo-btn" id="applyPromoBtn" ${appliedPromo ? 'disabled' : ''}>Применить</button>
      </div>
      ${appliedPromo ? `<div class="promo-message success">Промокод ${appliedPromo.code} применён!</div>` : ''}
      <div id="promoMessage" class="promo-message"></div>
    </div>
    <button class="checkout-btn" id="checkoutBtn">Оформить заказ</button>
    <span class="clear-cart" id="clearCart">Очистить корзину</span>
  `;
  
  cartSummary.innerHTML = summaryHtml;
  
  // Re-bind promo button
  const applyPromoBtn = document.getElementById('applyPromoBtn');
  if (applyPromoBtn) {
    applyPromoBtn.addEventListener('click', applyPromo);
  }
  
  // Re-bind checkout button
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      window.location.href = 'checkout.html';
    });
  }
  
  // Re-bind clear cart
  const clearCartBtn = document.getElementById('clearCart');
  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', clearCart);
  }
}

function bindRowEvents() {
  // Quantity buttons
  document.querySelectorAll('.cart-qty-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const action = btn.dataset.action;
      const key = btn.dataset.key;
      const input = document.querySelector(`.cart-qty-input[data-key="${key}"]`);
      let newQty = parseInt(input.value);
      
      if (action === 'incr') newQty++;
      if (action === 'decr') newQty--;
      
      if (newQty < 1) {
        await removeItem(key);
      } else {
        await updateQuantity(key, newQty);
      }
    });
  });
  
  // Remove buttons
  document.querySelectorAll('.cart-remove').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const key = btn.dataset.key;
      await removeItem(key);
    });
  });
}

async function updateQuantity(itemKey, quantity) {
  try {
    const result = await api.updateCartItem(itemKey, quantity);
    if (result.success) {
      await loadCart();
      updateCartCount();
      showToast('Количество обновлено');
    }
  } catch (error) {
    showToast('Ошибка обновления количества', false);
  }
}

async function removeItem(itemKey) {
  try {
    const result = await api.removeCartItem(itemKey);
    if (result.success) {
      await loadCart();
      updateCartCount();
      showToast('Товар удалён из корзины');
    }
  } catch (error) {
    showToast('Ошибка удаления товара', false);
  }
}

async function clearCart() {
  if (!confirm('Очистить всю корзину?')) return;
  
  try {
    const result = await api.clearCart();
    if (result.success) {
      await loadCart();
      updateCartCount();
      appliedPromo = null;
      showToast('Корзина очищена');
    }
  } catch (error) {
    showToast('Ошибка очистки корзины', false);
  }
}

async function applyPromo() {
  const promoInput = document.getElementById('promoCode');
  const code = promoInput?.value.trim();
  
  if (!code) {
    showMessage('Введите промокод', 'error');
    return;
  }
  
  try {
    const result = await api.applyPromo(code);
    if (result.success) {
      appliedPromo = result.promo;
      await loadCart();
      showMessage(`Промокод ${code} применён!`, 'success');
      if (promoInput) promoInput.disabled = true;
      const applyBtn = document.getElementById('applyPromoBtn');
      if (applyBtn) applyBtn.disabled = true;
    }
  } catch (error) {
    showMessage(error.message || 'Промокод недействителен', 'error');
  }
}

function showMessage(message, type) {
  const msgDiv = document.getElementById('promoMessage');
  if (msgDiv) {
    msgDiv.textContent = message;
    msgDiv.className = `promo-message ${type}`;
    setTimeout(() => {
      msgDiv.className = 'promo-message';
    }, 3000);
  }
}

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem('pm_cart') || '[]');
  const count = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartCountElements = document.querySelectorAll('.cart-count');
  cartCountElements.forEach(el => {
    if (el) el.textContent = count;
  });
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

// Export for use in HTML
window.initCartPage = initCartPage;