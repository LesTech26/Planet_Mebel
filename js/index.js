// Mock data for homepage
const SLIDES = [
    {
        title: 'Пространство, где живёт <em>уют</em>',
        label: 'Новая коллекция 2025',
        desc: 'Мебель ручной работы из массива дерева',
        cta: 'Смотреть коллекцию',
        link: 'catalog.html',
        bg: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1800&q=80'
    },
    {
        title: 'Минимализм и <em>тепло</em> дерева',
        label: 'Скандинавский стиль',
        desc: 'Простые формы, натуральные материалы',
        cta: 'Каталог диванов',
        link: 'catalog.html',
        bg: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1800&q=80'
    },
    {
        title: 'Отдых как <em>искусство</em>',
        label: 'Спальня мечты',
        desc: 'Кровати и матрасы премиум-класса',
        cta: 'Спальные гарнитуры',
        link: 'catalog.html',
        bg: 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=1800&q=80'
    }
];

const HITS_PRODUCTS = [
    { id: 1, name: 'Диван Модерн', category: 'Диваны', price: 89900, old_price: 114900, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&q=80', is_new: false },
    { id: 2, name: 'Кресло Лофт', category: 'Кресла', price: 34500, old_price: null, image: 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=600&q=80', is_new: true },
    { id: 3, name: 'Кровать Флоренция', category: 'Кровати', price: 67800, old_price: 79000, image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80', is_new: false },
    { id: 4, name: 'Шкаф Классик', category: 'Шкафы', price: 48200, old_price: null, image: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=600&q=80', is_new: false }
];

const NEW_PRODUCTS = [
    { id: 5, name: 'Стол Дуб', category: 'Столы', price: 29900, old_price: 35000, image: 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=600&q=80', is_new: true },
    { id: 6, name: 'Стул Тюльпан', category: 'Стулья', price: 12800, old_price: 15000, image: 'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?w=600&q=80', is_new: false },
    { id: 7, name: 'Диван Берген', category: 'Диваны', price: 104000, old_price: null, image: 'https://images.unsplash.com/photo-1493663284031-b7e3aaa4c8e7?w=600&q=80', is_new: true },
    { id: 8, name: 'Тумба Oslo', category: 'Тумбы', price: 16700, old_price: null, image: 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?w=600&q=80', is_new: true }
];

function renderCard(product) {
    const isFav = window.isFavorite ? window.isFavorite(product.id) : false;
    return `
        <div class="swiper-slide">
            <div class="product-card" onclick="location.href='product.html?id=${product.id}'">
                <div class="card-img">
                    <div class="card-img-inner" style="background-image: url('${product.image}')"></div>
                    ${product.is_new ? '<span class="card-badge">Новинка</span>' : ''}
                    <button class="card-fav ${isFav ? 'fav-active' : ''}" onclick="event.stopPropagation();handleFav(this, ${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${product.image}')">
                        ${isFav ? '♥' : '♡'}
                    </button>
                </div>
                <p class="card-cat">${product.category}</p>
                <div class="card-name">${product.name}</div>
                <div class="card-price">
                    <span>${formatPrice(product.price)}</span>
                    ${product.old_price ? `<span class="card-price-old">${formatPrice(product.old_price)}</span>` : ''}
                </div>
                <button class="card-add" onclick="event.stopPropagation();addToCart({productId: ${product.id}, name: '${product.name.replace(/'/g, "\\'")}', price: ${product.price}, quantity: 1, options: {}})">
                    В корзину
                </button>
            </div>
        </div>
    `;
}

function handleFav(btn, id, name, price, image) {
    const added = toggleFavorite({ id, name, price, image });
    btn.textContent = added ? '♥' : '♡';
    btn.classList.toggle('fav-active', added);
}

// Init slider
function initSlider() {
    const wrapper = document.getElementById('sliderWrapper');
    if (!wrapper) return;
    
    wrapper.innerHTML = SLIDES.map(slide => `
        <div class="swiper-slide">
            <div class="slide-bg" style="background-image: url('${slide.bg}')"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <p class="slide-label">${slide.label}</p>
                <h2 class="slide-title">${slide.title}</h2>
                <p class="slide-desc">${slide.desc}</p>
                <a href="${slide.link}" class="btn-primary">${slide.cta}</a>
            </div>
        </div>
    `).join('');
    
    new Swiper('#mainSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        effect: 'fade',
        fadeEffect: { crossFade: true },
        pagination: { el: '.swiper-pagination', clickable: true },
        speed: 900
    });
}

// Init product carousels
function initCarousel(wrapperId, products, prevId, nextId, swiperId) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;
    
    wrapper.innerHTML = products.map(renderCard).join('');
    
    new Swiper(`#${swiperId}`, {
        slidesPerView: 'auto',
        spaceBetween: 22,
        navigation: { prevEl: `#${prevId}`, nextEl: `#${nextId}` }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSlider();
    initCarousel('hitsWrapper', HITS_PRODUCTS, 'hitsPrev', 'hitsNext', 'hitsSwiper');
    initCarousel('newWrapper', NEW_PRODUCTS, 'newPrev', 'newNext', 'newSwiper');
});

window.handleFav = handleFav;