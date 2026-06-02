// Общие JS-функции
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

function showToast(message, isSuccess = true) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    const msgSpan = document.getElementById('toastMsg');
    const iconSpan = toast.querySelector('.toast-icon');
    if (msgSpan) msgSpan.textContent = message;
    if (iconSpan) iconSpan.textContent = isSuccess ? '✓' : '✕';
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), 3000);
}

async function addToCart(productId, quantity) {
    try {
        const response = await fetch('/api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId, quantity: quantity })
        });
        const data = await response.json();
        if (data.success) {
            updateCartCount(data.count);
            showToast('Товар добавлен в корзину');
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
}

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('#cartCount');
    cartCountElements.forEach(el => {
        if (el) el.textContent = count;
    });
}

// Swiper инициализация
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('mainSwiper')) {
        new Swiper('#mainSwiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            effect: 'fade',
            pagination: { el: '.swiper-pagination', clickable: true }
        });
    }
    
    if (document.getElementById('hitsSwiper')) {
        new Swiper('#hitsSwiper', {
            slidesPerView: 'auto',
            spaceBetween: 22,
            navigation: { prevEl: '#hitsPrev', nextEl: '#hitsNext' }
        });
    }
    
    if (document.getElementById('newSwiper')) {
        new Swiper('#newSwiper', {
            slidesPerView: 'auto',
            spaceBetween: 22,
            navigation: { prevEl: '#newPrev', nextEl: '#newNext' }
        });
    }
});