// Полная версия product.js
const MOCK_PRODUCT = {
    id: 1,
    name: 'Диван Модерн',
    category: 'Диваны',
    article: 'PM-00001',
    stock: 'in',
    stock_text: 'В наличии',
    rating: 4.8,
    review_count: 12,
    base_price: 89900,
    base_old_price: 114900,
    images: [
        'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=900&q=80',
        'https://images.unsplash.com/photo-1493663284031-b7e3aaa4c8e7?w=900&q=80',
        'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=900&q=80'
    ],
    colors: [
        { name: 'Серый', hex: '#9BA7A0' },
        { name: 'Бежевый', hex: '#C9B99A' },
        { name: 'Синий', hex: '#5A7A99' }
    ],
    materials: ['Велюр', 'Рогожка', 'Экокожа'],
    specs: [
        ['Ширина', '220 см'], ['Глубина', '90 см'], ['Высота', '85 см'],
        ['Тип механизма', 'Еврокнижка'], ['Наполнитель', 'Пружинный блок + ППУ']
    ]
};

const productVariants = {
    'Серый__Велюр': { price: 89900, old_price: 114900, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=900&q=80' },
    'Серый__Рогожка': { price: 84900, old_price: 109900, image: 'https://images.unsplash.com/photo-1493663284031-b7e3aaa4c8e7?w=900&q=80' },
    'Серый__Экокожа': { price: 94900, old_price: null, image: 'https://images.unsplash.com/photo-1540574163026-643ea20ade25?w=900&q=80' },
    'Бежевый__Велюр': { price: 91900, old_price: 117000, image: 'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=900&q=80' },
    'Бежевый__Рогожка': { price: 86900, old_price: null, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=900&q=80' },
    'Синий__Велюр': { price: 93900, old_price: 119000, image: 'https://images.unsplash.com/photo-1567016432779-094069958ea5?w=900&q=80' }
};

const MOCK_REVIEWS = [
    { id: 1, name: 'Алина К.', rating: 5, date: '15 мая 2025', text: 'Отличный диван! Качество превосходное.' },
    { id: 2, name: 'Михаил С.', rating: 4, date: '2 апреля 2025', text: 'Хороший диван за свои деньги.' }
];

const CROSS_SELL = [
    { id: 2, name: 'Кресло Лофт', category: 'Кресла', price: 34500, image: 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=600&q=80' },
    { id: 5, name: 'Стол Дуб', category: 'Столы', price: 29900, image: 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=600&q=80' }
];

let currentProduct = null;
let selectedColor = '';
let selectedMaterial = '';
let quantity = 1;

function getVariant() {
    const key = `${selectedColor}__${selectedMaterial}`;
    return productVariants[key] || { price: currentProduct.base_price, old_price: currentProduct.base_old_price, image: currentProduct.images[0] };
}

function applyVariant() {
    const variant = getVariant();
    const priceHtml = `
        <span class="price-current">${formatPrice(variant.price)}</span>
        ${variant.old_price ? `<span class="price-old">${formatPrice(variant.old_price)}</span>` : ''}
        ${variant.old_price ? `<span class="price-save">−${Math.round((1 - variant.price / variant.old_price) * 100)}%</span>` : ''}
    `;
    document.getElementById('productPrice').innerHTML = priceHtml;
    document.getElementById('mainImg').style.backgroundImage = `url('${variant.image}')`;
}

function renderProduct(product) {
    currentProduct = product;
    document.title = `${product.name} — Планета Мебели`;
    document.getElementById('productCategory').textContent = product.category;
    document.getElementById('productName').textContent = product.name;
    document.getElementById('productArticle').textContent = `Артикул: ${product.article}`;
    
    const stockClass = product.stock === 'in' ? 'in' : product.stock === 'low' ? 'low' : 'out';
    document.getElementById('productStock').innerHTML = `
        <span class="stock-dot ${stockClass}"></span>
        <span>${product.stock_text}</span>
    `;
    
    // Gallery
    document.getElementById('mainImg').style.backgroundImage = `url('${product.images[0]}')`;
    document.getElementById('galleryThumbs').innerHTML = product.images.map((img, i) => `
        <div class="thumb ${i === 0 ? 'active' : ''}" style="background-image: url('${img}')" onclick="setMainImage('${img}', this)"></div>
    `).join('');
    
    // Colors
    if (product.colors && product.colors.length) {
        selectedColor = product.colors[0].name;
        document.getElementById('colorOptions').style.display = 'block';
        document.getElementById('colorList').innerHTML = product.colors.map(c => `
            <div class="color-option" style="background: ${c.hex}" title="${c.name}" onclick="selectColor('${c.name}', this)"></div>
        `).join('');
        document.querySelectorAll('.color-option')[0]?.classList.add('active');
    }
    
    // Materials
    if (product.materials && product.materials.length) {
        selectedMaterial = product.materials[0];
        document.getElementById('materialOptions').style.display = 'block';
        document.getElementById('materialList').innerHTML = product.materials.map(m => `
            <button class="material-option" onclick="selectMaterial('${m}', this)">${m}</button>
        `).join('');
        document.querySelectorAll('.material-option')[0]?.classList.add('active');
    }
    
    applyVariant();
    
    // Specs
    document.getElementById('specsTable').innerHTML = product.specs.map(([k, v]) => `<tr><td>${k}</td><td>${v}</td></tr>`).join('');
    
    // Fav button
    const isFav = window.isFavorite ? window.isFavorite(product.id) : false;
    const favBtn = document.getElementById('favBtn');
    favBtn.textContent = isFav ? '♥' : '♡';
    favBtn.classList.toggle('active', isFav);
    
    renderReviews(MOCK_REVIEWS);
    renderCrossSell();
}

function setMainImage(url, el) {
    document.getElementById('mainImg').style.backgroundImage = `url('${url}')`;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function selectColor(color, el) {
    selectedColor = color;
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
    applyVariant();
}

function selectMaterial(material, el) {
    selectedMaterial = material;
    document.querySelectorAll('.material-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
    applyVariant();
}

function changeQty(delta) {
    quantity = Math.max(1, Math.min(99, quantity + delta));
    document.getElementById('qtyValue').value = quantity;
}

function addToCartHandler() {
    if (!currentProduct) return;
    const variant = getVariant();
    addToCart({
        productId: currentProduct.id,
        name: currentProduct.name,
        price: variant.price,
        quantity: quantity,
        options: { color: selectedColor, material: selectedMaterial },
        image: variant.image
    });
}

function toggleFavHandler() {
    if (!currentProduct) return;
    const variant = getVariant();
    const added = toggleFavorite({
        id: currentProduct.id,
        name: currentProduct.name,
        price: variant.price,
        image: currentProduct.images[0]
    });
    const favBtn = document.getElementById('favBtn');
    favBtn.textContent = added ? '♥' : '♡';
    favBtn.classList.toggle('active', added);
}

function renderReviews(reviews) {
    const container = document.getElementById('reviewsList');
    if (!container) return;
    
    if (!reviews.length) {
        container.innerHTML = '<p>Пока нет отзывов. Будьте первым!</p>';
        return;
    }
    
    container.innerHTML = reviews.map(r => `
        <div class="review-item">
            <div class="review-header">
                <div class="review-avatar">${r.name.charAt(0)}</div>
                <div class="review-name">${r.name}</div>
                <div class="review-rating">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</div>
                <div class="review-date">${r.date}</div>
            </div>
            <div class="review-text">${r.text}</div>
        </div>
    `).join('');
}

function renderCrossSell() {
    const container = document.getElementById('crossWrapper');
    if (!container) return;
    
    container.innerHTML = CROSS_SELL.map(p => `
        <div class="swiper-slide">
            <div class="cross-card" onclick="location.href='product.html?id=${p.id}'">
                <div class="cross-card-img">
                    <div class="cross-card-img-inner" style="background-image: url('${p.image}')"></div>
                </div>
                <p class="cross-card-cat">${p.category}</p>
                <div class="cross-card-name">${p.name}</div>
                <div class="cross-card-price">${formatPrice(p.price)}</div>
            </div>
        </div>
    `).join('');
    
    new Swiper('#crossSwiper', {
        slidesPerView: 'auto',
        spaceBetween: 20,
        navigation: { prevEl: '#crossPrev', nextEl: '#crossNext' }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = parseInt(urlParams.get('id')) || 1;
    renderProduct(MOCK_PRODUCT);
    
    document.getElementById('qtyMinus')?.addEventListener('click', () => changeQty(-1));
    document.getElementById('qtyPlus')?.addEventListener('click', () => changeQty(1));
    document.getElementById('addToCartBtn')?.addEventListener('click', addToCartHandler);
    document.getElementById('favBtn')?.addEventListener('click', toggleFavHandler);
    
    const showFormBtn = document.getElementById('showReviewFormBtn');
    const reviewForm = document.getElementById('reviewForm');
    if (showFormBtn && reviewForm) {
        showFormBtn.addEventListener('click', () => {
            reviewForm.style.display = reviewForm.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    const submitBtn = document.getElementById('submitReviewBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            const name = document.getElementById('reviewName')?.value;
            const rating = document.getElementById('reviewRating')?.value;
            const text = document.getElementById('reviewText')?.value;
            if (name && text) {
                showToast('Спасибо за отзыв! Он будет опубликован после модерации.');
                document.getElementById('reviewForm').style.display = 'none';
            }
        });
    }
});

window.setMainImage = setMainImage;
window.selectColor = selectColor;
window.selectMaterial = selectMaterial;