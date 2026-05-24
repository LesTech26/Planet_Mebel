// Catalog page logic: filters, sorting, pagination, quick view

import { api } from './api.js';
import { showToast, debounce, showSpinner, hideSpinner, getUrlParams, updateUrlParams } from './utils.js';

let currentProducts = [];
let currentFilters = null;
let currentPage = 1;
let totalPages = 1;
let isLoading = false;

// DOM elements
let productsGrid;
let paginationContainer;
let productsCountSpan;
let sortSelect;

// Initialize catalog
export async function initCatalog() {
  productsGrid = document.getElementById('productsGrid');
  paginationContainer = document.getElementById('pagination');
  productsCountSpan = document.getElementById('productsCount');
  sortSelect = document.getElementById('sortSelect');
  
  if (!productsGrid) return;
  
  // Bind events
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      currentPage = 1;
      loadProducts();
    });
  }
  
  // Bind filter events
  bindFilterEvents();
  
  // Mobile filter
  initMobileFilter();
  
  // Load products from URL params
  const params = getUrlParams();
  currentPage = params.page || 1;
  if (sortSelect) sortSelect.value = params.sort || 'new';
  
  await loadProducts();
}

// Load products with current filters
async function loadProducts() {
  if (isLoading) return;
  isLoading = true;
  
  showSpinner(productsGrid);
  
  const params = getFilterParams();
  params.page = currentPage;
  params.limit = 12;
  params.sort = sortSelect ? sortSelect.value : 'new';
  
  try {
    const data = await api.getProducts(params);
    currentProducts = data.items;
    currentFilters = data.filters;
    totalPages = data.last_page;
    
    renderProducts();
    renderPagination();
    updateProductsCount(data.total);
    updateUrlFromFilters();
    
    // Update filter UI with available values
    updateFiltersUI();
  } catch (error) {
    console.error('Error loading products:', error);
    showToast('Ошибка загрузки товаров', false);
    productsGrid.innerHTML = '<div class="empty-catalog"><div class="empty-catalog-icon">⚠️</div><div class="empty-catalog-title">Ошибка загрузки</div><div class="empty-catalog-text">Попробуйте обновить страницу</div></div>';
  } finally {
    hideSpinner(productsGrid);
    isLoading = false;
  }
}

// Get filter parameters from UI
function getFilterParams() {
  const params = {};
  
  // Category (radio/checkbox)
  const selectedCategory = document.querySelector('input[name="category"]:checked');
  if (selectedCategory && selectedCategory.value) {
    params.category = selectedCategory.value;
  }
  
  // Price range
  const priceMin = document.getElementById('priceMin')?.value;
  const priceMax = document.getElementById('priceMax')?.value;
  if (priceMin && parseInt(priceMin) > 0) params.price_min = priceMin;
  if (priceMax && parseInt(priceMax) > 0) params.price_max = priceMax;
  
  // Materials (checkboxes)
  const selectedMaterials = Array.from(document.querySelectorAll('input[name="material"]:checked'))
    .map(cb => cb.value);
  if (selectedMaterials.length) params.materials = selectedMaterials;
  
  // Brand
  const brand = document.getElementById('brandSelect')?.value;
  if (brand && brand !== '') params.brand = brand;
  
  // Size
  const size = document.querySelector('input[name="size"]:checked')?.value;
  if (size) params.size = size;
  
  return params;
}

// Render products grid
function renderProducts() {
  if (!productsGrid) return;
  
  if (!currentProducts.length) {
    productsGrid.innerHTML = `
      <div class="empty-catalog" style="grid-column: 1/-1">
        <div class="empty-catalog-icon">🔍</div>
        <div class="empty-catalog-title">Товары не найдены</div>
        <div class="empty-catalog-text">Попробуйте изменить параметры фильтрации</div>
        <button class="btn btn-outline" onclick="resetFilters()">Сбросить фильтры</button>
      </div>
    `;
    return;
  }
  
  productsGrid.innerHTML = currentProducts.map(product => renderProductCard(product)).join('');
  
  // Bind card events
  document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', (e) => {
      // Don't navigate if clicking on button or fav
      if (e.target.closest('.card-add') || e.target.closest('.card-fav')) return;
      const productId = card.dataset.id;
      if (productId) window.location.href = `product.html?id=${productId}`;
    });
  });
  
  // Bind add to cart buttons
  document.querySelectorAll('.card-add').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const productId = parseInt(btn.dataset.id);
      const productName = btn.dataset.name;
      await handleAddToCart(productId, productName);
    });
  });
  
  // Bind favorite buttons
  document.querySelectorAll('.card-fav').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const productId = parseInt(btn.dataset.id);
      await handleToggleFavorite(btn, productId);
    });
  });
  
  // Bind quick view buttons
  document.querySelectorAll('.quick-view-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const productId = parseInt(btn.dataset.id);
      await showQuickView(productId);
    });
  });
}

// Render single product card
function renderProductCard(product) {
  const hasDiscount = product.old_price && product.old_price > product.price;
  const discountPercent = hasDiscount ? Math.round((1 - product.price / product.old_price) * 100) : 0;
  const stars = '★'.repeat(Math.round(product.rating)) + '☆'.repeat(5 - Math.round(product.rating));
  
  return `
    <div class="product-card" data-id="${product.id}">
      <div class="card-img">
        <div class="card-img-inner" style="background-image: url('${product.image}')"></div>
        ${product.is_new ? '<span class="card-badge">Новинка</span>' : ''}
        ${product.is_hit ? '<span class="card-badge" style="left:auto;right:10px;background:var(--gold)">Хит</span>' : ''}
        <button class="card-fav" data-id="${product.id}" data-faved="false">♡</button>
      </div>
      <p class="card-cat">${product.category_name}</p>
      <a class="card-name">${product.name}</a>
      <div class="card-rating" style="display:flex;align-items:center;gap:6px;margin-bottom:8px">
        <span style="color:var(--gold);font-size:12px">${stars}</span>
        <span style="font-size:11px;color:var(--mid)">(${product.review_count})</span>
      </div>
      <div class="card-price">
        <span>${formatPrice(product.price)}</span>
        ${hasDiscount ? `<span class="card-price-old">${formatPrice(product.old_price)}</span>` : ''}
        ${hasDiscount ? `<span style="font-size:11px;color:var(--green);margin-left:6px">-${discountPercent}%</span>` : ''}
      </div>
      <button class="card-add" data-id="${product.id}" data-name="${product.name.replace(/'/g, "\\'")}">В корзину</button>
    </div>
  `;
}

// Render pagination
function renderPagination() {
  if (!paginationContainer) return;
  
  if (totalPages <= 1) {
    paginationContainer.innerHTML = '';
    return;
  }
  
  let html = '';
  
  // Previous button
  html += `<a href="#" class="${currentPage === 1 ? 'disabled' : ''}" onclick="goToPage(${currentPage - 1}); return false;">←</a>`;
  
  // Page numbers
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);
  
  if (startPage > 1) {
    html += `<a href="#" onclick="goToPage(1); return false;">1</a>`;
    if (startPage > 2) html += `<span>...</span>`;
  }
  
  for (let i = startPage; i <= endPage; i++) {
    html += `<a href="#" class="${i === currentPage ? 'active' : ''}" onclick="goToPage(${i}); return false;">${i}</a>`;
  }
  
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) html += `<span>...</span>`;
    html += `<a href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>`;
  }
  
  // Next button
  html += `<a href="#" class="${currentPage === totalPages ? 'disabled' : ''}" onclick="goToPage(${currentPage + 1}); return false;">→</a>`;
  
  paginationContainer.innerHTML = html;
}

// Update products count display
function updateProductsCount(total) {
  if (productsCountSpan) {
    productsCountSpan.textContent = `Найдено ${total} товаров`;
  }
}

// Update URL with current filters
function updateUrlFromFilters() {
  const params = getFilterParams();
  params.page = currentPage;
  if (sortSelect) params.sort = sortSelect.value;
  updateUrlParams(params);
}

// Bind filter events
function bindFilterEvents() {
  // Price inputs with debounce
  const priceMin = document.getElementById('priceMin');
  const priceMax = document.getElementById('priceMax');
  
  if (priceMin) {
    priceMin.addEventListener('input', debounce(() => {
      currentPage = 1;
      loadProducts();
    }, 500));
  }
  
  if (priceMax) {
    priceMax.addEventListener('input', debounce(() => {
      currentPage = 1;
      loadProducts();
    }, 500));
  }
  
  // Category radios
  document.querySelectorAll('input[name="category"]').forEach(radio => {
    radio.addEventListener('change', () => {
      currentPage = 1;
      loadProducts();
    });
  });
  
  // Material checkboxes
  document.querySelectorAll('input[name="material"]').forEach(cb => {
    cb.addEventListener('change', () => {
      currentPage = 1;
      loadProducts();
    });
  });
  
  // Size radios
  document.querySelectorAll('input[name="size"]').forEach(radio => {
    radio.addEventListener('change', () => {
      currentPage = 1;
      loadProducts();
    });
  });
  
  // Brand select
  const brandSelect = document.getElementById('brandSelect');
  if (brandSelect) {
    brandSelect.addEventListener('change', () => {
      currentPage = 1;
      loadProducts();
    });
  }
  
  // Reset button
  const resetBtn = document.getElementById('resetFilters');
  if (resetBtn) {
    resetBtn.addEventListener('click', resetFilters);
  }
}

// Reset all filters
window.resetFilters = function() {
  // Clear category radios
  document.querySelectorAll('input[name="category"]').forEach(radio => radio.checked = false);
  
  // Clear price inputs
  const priceMin = document.getElementById('priceMin');
  const priceMax = document.getElementById('priceMax');
  if (priceMin) priceMin.value = '';
  if (priceMax) priceMax.value = '';
  
  // Clear material checkboxes
  document.querySelectorAll('input[name="material"]').forEach(cb => cb.checked = false);
  
  // Clear size radios
  document.querySelectorAll('input[name="size"]').forEach(radio => radio.checked = false);
  
  // Reset brand select
  const brandSelect = document.getElementById('brandSelect');
  if (brandSelect) brandSelect.value = '';
  
  // Reset sort
  if (sortSelect) sortSelect.value = 'new';
  
  currentPage = 1;
  loadProducts();
};

// Go to page
window.goToPage = function(page) {
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  loadProducts();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Add to cart handler
async function handleAddToCart(productId, productName) {
  try {
    await api.addToCart(productId, 1, {});
    showToast(`«${productName}» добавлен в корзину`);
    updateCartCount();
  } catch (error) {
    showToast('Ошибка добавления в корзину', false);
  }
}

// Toggle favorite
async function handleToggleFavorite(btn, productId) {
  const isFaved = btn.classList.contains('fav-active');
  const favs = JSON.parse(localStorage.getItem('pm_favs') || '[]');
  const index = favs.findIndex(f => f.id === productId);
  
  if (index > -1) {
    favs.splice(index, 1);
    btn.classList.remove('fav-active');
    btn.textContent = '♡';
    showToast('Удалено из избранного');
  } else {
    const product = currentProducts.find(p => p.id === productId);
    if (product) {
      favs.push({ id: productId, name: product.name, price: product.price, image: product.image });
      btn.classList.add('fav-active');
      btn.textContent = '♥';
      showToast('Добавлено в избранное');
    }
  }
  localStorage.setItem('pm_favs', JSON.stringify(favs));
}

// Quick view modal
async function showQuickView(productId) {
  const product = currentProducts.find(p => p.id === productId);
  if (!product) return;
  
  const modalHtml = `
    <div id="quickViewModal" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:1000;display:flex;align-items:center;justify-content:center">
      <div style="background:var(--warm);max-width:900px;width:90%;max-height:90%;overflow:auto;border-radius:8px;position:relative">
        <button style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer" onclick="closeQuickView()">✕</button>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;padding:24px">
          <div><img src="${product.image}" style="width:100%;border-radius:8px"></div>
          <div>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:28px;margin-bottom:8px">${product.name}</h2>
            <p class="card-cat" style="margin-bottom:12px">${product.category_name}</p>
            <div class="card-price" style="margin-bottom:16px">
              <span style="font-size:28px">${formatPrice(product.price)}</span>
              ${product.old_price ? `<span class="card-price-old" style="font-size:18px">${formatPrice(product.old_price)}</span>` : ''}
            </div>
            <p style="color:var(--mid);margin-bottom:24px">${product.description || 'Качественная мебель из натуральных материалов. Стильный дизайн и надёжная конструкция.'}</p>
            <button class="btn btn-primary" style="width:100%" onclick="quickViewAddToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}')">В корзину</button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  document.body.style.overflow = 'hidden';
}

window.closeQuickView = function() {
  const modal = document.getElementById('quickViewModal');
  if (modal) modal.remove();
  document.body.style.overflow = '';
};

window.quickViewAddToCart = async function(productId, productName) {
  await handleAddToCart(productId, productName);
  closeQuickView();
};

// Update filters UI with available values
function updateFiltersUI() {
  if (!currentFilters) return;
  
  // Update price slider range
  const priceMinInput = document.getElementById('priceMin');
  const priceMaxInput = document.getElementById('priceMax');
  if (priceMinInput && currentFilters.price_min) {
    priceMinInput.placeholder = `от ${currentFilters.price_min.toLocaleString()} ₽`;
  }
  if (priceMaxInput && currentFilters.price_max) {
    priceMaxInput.placeholder = `до ${currentFilters.price_max.toLocaleString()} ₽`;
  }
  
  // Update brand select
  const brandSelect = document.getElementById('brandSelect');
  if (brandSelect && currentFilters.brands) {
    const currentValue = brandSelect.value;
    brandSelect.innerHTML = '<option value="">Все бренды</option>' + 
      currentFilters.brands.map(b => `<option value="${b}">${b}</option>`).join('');
    brandSelect.value = currentValue;
  }
}

// Mobile filter
function initMobileFilter() {
  const mobileBtn = document.getElementById('mobileFilterBtn');
  const sidebar = document.querySelector('.filters-sidebar');
  const overlay = document.getElementById('mobileFilterOverlay');
  
  if (!mobileBtn || !sidebar) return;
  
  mobileBtn.addEventListener('click', () => {
    sidebar.classList.add('open');
    if (overlay) overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
  });
  
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.style.display = 'none';
      document.body.style.overflow = '';
    });
  }
}

// Update cart count in header
function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem('pm_cart') || '[]');
  const count = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartCountElements = document.querySelectorAll('.cart-count');
  cartCountElements.forEach(el => {
    if (el) el.textContent = count;
  });
}

// Format price helper
function formatPrice(price) {
  return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

// Export for use in HTML
window.initCatalog = initCatalog;