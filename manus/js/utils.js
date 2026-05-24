// Utils: toast, debounce, formatPrice, etc.

let toastTimeout = null;

export function showToast(message, isSuccess = true) {
  let toast = document.getElementById('toast');
  
  // Если тоста нет на странице, создаём
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    toast.innerHTML = '<span class="toast-icon">✓</span><span id="toastMsg"></span>';
    document.body.appendChild(toast);
  }
  
  const icon = toast.querySelector('.toast-icon');
  const msgSpan = toast.querySelector('#toastMsg');
  
  icon.textContent = isSuccess ? '✓' : '✕';
  msgSpan.textContent = message;
  
  toast.classList.add('show');
  
  if (toastTimeout) clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

export function debounce(func, delay) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
}

export function formatPrice(price) {
  return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

export function showSpinner(container) {
  const spinner = document.createElement('div');
  spinner.className = 'spinner';
  spinner.id = 'loadingSpinner';
  
  // Очищаем контейнер и добавляем спиннер
  container.innerHTML = '';
  container.appendChild(spinner);
}

export function hideSpinner(container) {
  const spinner = document.getElementById('loadingSpinner');
  if (spinner && spinner.parentNode === container) {
    spinner.remove();
  }
}

export function getUrlParams() {
  const params = new URLSearchParams(window.location.search);
  return {
    page: parseInt(params.get('page')) || 1,
    sort: params.get('sort') || 'new',
    category: params.get('category') || '',
    price_min: params.get('price_min') || '',
    price_max: params.get('price_max') || '',
    material: params.get('material') || '',
    brand: params.get('brand') || '',
    size: params.get('size') || ''
  };
}

export function updateUrlParams(params) {
  const url = new URL(window.location.href);
  Object.keys(params).forEach(key => {
    if (params[key]) {
      url.searchParams.set(key, params[key]);
    } else {
      url.searchParams.delete(key);
    }
  });
  window.history.pushState({}, '', url);
}