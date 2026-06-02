// Mock products
const ALL_PRODUCTS = [
    { id: 1, name: 'Диван Модерн', category: 'Диваны', categoryId: 1, price: 89900, old_price: 114900, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&q=80', material: 'Ткань', is_new: false },
    { id: 2, name: 'Кресло Лофт', category: 'Кресла', categoryId: 2, price: 34500, old_price: null, image: 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=600&q=80', material: 'Ткань', is_new: true },
    { id: 3, name: 'Кровать Флоренция', category: 'Кровати', categoryId: 3, price: 67800, old_price: 79000, image: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80', material: 'Дерево', is_new: false },
    { id: 4, name: 'Шкаф Классик', category: 'Шкафы', categoryId: 4, price: 48200, old_price: null, image: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=600&q=80', material: 'Дерево', is_new: false },
    { id: 5, name: 'Стол Дуб', category: 'Столы', categoryId: 5, price: 29900, old_price: 35000, image: 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=600&q=80', material: 'Дерево', is_new: true },
    { id: 6, name: 'Стул Тюльпан', category: 'Стулья', categoryId: 6, price: 12800, old_price: 15000, image: 'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?w=600&q=80', material: 'Металл', is_new: false },
    { id: 7, name: 'Диван Берген', category: 'Диваны', categoryId: 1, price: 104000, old_price: null, image: 'https://images.unsplash.com/photo-1493663284031-b7e3aaa4c8e7?w=600&q=80', material: 'Ткань', is_new: true },
    { id: 8, name: 'Тумба Oslo', category: 'Тумбы', categoryId: 9, price: 16700, old_price: null, image: 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?w=600&q=80', material: 'Дерево', is_new: true },
    { id: 9, name: 'Торшер Лайт', category: 'Освещение', categoryId: 7, price: 9600, old_price: 11200, image: 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&q=80', material: 'Металл', is_new: true },
    { id: 10, name: 'Зеркало Frame', category: 'Декор', categoryId: 8, price: 22300, old_price: null, image: 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=600&q=80', material: 'Комбинированный', is_new: false }
];

const CATEGORIES = [
    { id: 1, name: 'Диваны' }, { id: 2, name: 'Кресла' }, { id: 3, name: 'Кровати' },
    { id: 4, name: 'Шкафы' }, { id: 5, name: 'Столы' }, { id: 6, name: 'Стулья' },
    { id: 7, name: 'Освещение' }, { id: 8, name: 'Декор' }
];

const MATERIALS = ['Дерево', 'Ткань', 'Металл', 'Комбинированный'];

let currentProducts = [...ALL_PRODUCTS];
let currentPage = 1;
const perPage = 9;

function renderProductCard(product) {
    const isFav = window.isFavorite ? window.isFavorite(product.id) : false;
    return `
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
    `;
}

function renderProducts() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    
    if (currentProducts.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--mid)">Товары не найдены</div>';
        document.getElementById('productsCount').innerText = 'Найдено товаров: 0';
        return;
    }
    
    const start = (currentPage - 1) * perPage;
    const pageItems = currentProducts.slice(start, start + perPage);
    
    grid.innerHTML = pageItems.map(renderProductCard).join('');
    document.getElementById('productsCount').innerText = `Найдено товаров: ${currentProducts.length}`;
    renderPagination();
}

function renderPagination() {
    const totalPages = Math.ceil(currentProducts.length / perPage);
    const container = document.getElementById('pagination');
    if (!container) return;
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderProducts();
    window.scrollTo({ top: document.querySelector('.products-area')?.offsetTop - 100, behavior: 'smooth' });
}

function applyFilters() {
    let filtered = [...ALL_PRODUCTS];
    
    const selectedCat = document.querySelector('input[name="category"]:checked')?.value;
    if (selectedCat) filtered = filtered.filter(p => p.categoryId == selectedCat);
    
    const priceMin = parseInt(document.getElementById('priceMin')?.value) || 0;
    const priceMax = parseInt(document.getElementById('priceMax')?.value) || Infinity;
    filtered = filtered.filter(p => p.price >= priceMin && p.price <= priceMax);
    
    const selectedMaterials = Array.from(document.querySelectorAll('input[name="material"]:checked')).map(cb => cb.value);
    if (selectedMaterials.length) filtered = filtered.filter(p => selectedMaterials.includes(p.material));
    
    const sort = document.getElementById('sortSelect')?.value;
    if (sort === 'price_asc') filtered.sort((a, b) => a.price - b.price);
    else if (sort === 'price_desc') filtered.sort((a, b) => b.price - a.price);
    
    currentProducts = filtered;
    currentPage = 1;
    renderProducts();
}

function buildFilters() {
    const catHtml = CATEGORIES.map(c => `
        <label class="category-item">
            <input type="radio" name="category" value="${c.id}" onchange="applyFilters()"> ${c.name}
            <span class="category-count">${ALL_PRODUCTS.filter(p => p.categoryId === c.id).length}</span>
        </label>
    `).join('');
    
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.innerHTML = catHtml + '<label class="category-item"><input type="radio" name="category" value="" onchange="applyFilters()" checked> Все категории</label>';
    }
    
    const matHtml = MATERIALS.map(m => `
        <label class="checkbox-item">
            <input type="checkbox" name="material" value="${m}" onchange="applyFilters()"> ${m}
        </label>
    `).join('');
    
    const materialFilter = document.getElementById('materialFilter');
    if (materialFilter) materialFilter.innerHTML = matHtml;
}

function handleFav(btn, id, name, price, image) {
    const added = toggleFavorite({ id, name, price, image });
    btn.textContent = added ? '♥' : '♡';
    btn.classList.toggle('fav-active', added);
}

document.addEventListener('DOMContentLoaded', () => {
    buildFilters();
    applyFilters();
    
    document.getElementById('priceMin')?.addEventListener('input', applyFilters);
    document.getElementById('priceMax')?.addEventListener('input', applyFilters);
    document.getElementById('sortSelect')?.addEventListener('change', applyFilters);
    document.getElementById('resetFilters')?.addEventListener('click', () => {
        document.querySelectorAll('input[name="category"]').forEach(r => r.checked = false);
        document.querySelectorAll('input[name="material"]').forEach(c => c.checked = false);
        document.getElementById('priceMin').value = '';
        document.getElementById('priceMax').value = '';
        document.getElementById('sortSelect').value = 'default';
        applyFilters();
    });
    
    // Mobile filter
    const mobileBtn = document.getElementById('mobileFilterBtn');
    const sidebar = document.getElementById('filtersSidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', () => sidebar.classList.add('open'));
        if (overlay) overlay.addEventListener('click', () => sidebar.classList.remove('open'));
    }
});

window.changePage = changePage;
window.handleFav = handleFav;
window.applyFilters = applyFilters;