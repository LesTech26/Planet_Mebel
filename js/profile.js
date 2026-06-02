let orders = [];
let favorites = [];

const MOCK_ORDERS = [
    {
        id: 'PM-2025-0312',
        date: '12 марта 2025',
        status: 'delivered',
        status_text: 'Доставлен',
        total: 124400,
        items: [
            { name: 'Диван Модерн', qty: 1, price: 89900, image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=100&q=80' }
        ]
    },
    {
        id: 'PM-2025-0118',
        date: '18 января 2025',
        status: 'delivered',
        status_text: 'Доставлен',
        total: 48200,
        items: [
            { name: 'Шкаф Классик', qty: 1, price: 48200, image: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=100&q=80' }
        ]
    }
];

function loadData() {
    orders = MOCK_ORDERS;
    favorites = getFavs();
    renderOrders();
    renderFavorites();
}

function renderOrders() {
    const container = document.getElementById('ordersList');
    if (!container) return;
    
    if (orders.length === 0) {
        container.innerHTML = '<div class="empty-state">У вас пока нет заказов</div>';
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="order-card" id="order-${order.id}">
            <div class="order-header" onclick="toggleOrder('${order.id}')">
                <div class="order-number">${order.id}</div>
                <div class="order-date">${order.date}</div>
                <span class="order-status status-${order.status}">${order.status_text}</span>
                <div class="order-total">${formatPrice(order.total)}</div>
                <span class="order-chevron">▼</span>
            </div>
            <div class="order-body">
                <div class="order-items">
                    ${order.items.map(item => `
                        <div class="order-item-row">
                            <div class="order-item-img" style="background-image: url('${item.image}')"></div>
                            <div class="order-item-name">${item.name} × ${item.qty}</div>
                            <div>${formatPrice(item.price * item.qty)}</div>
                        </div>
                    `).join('')}
                </div>
                <div class="order-footer">
                    <span>Итого: ${formatPrice(order.total)}</span>
                    <button class="btn-reorder" onclick="reorder(${JSON.stringify(order.items).replace(/"/g, '&quot;')})">Повторить заказ</button>
                </div>
            </div>
        </div>
    `).join('');
}

function toggleOrder(id) {
    document.getElementById(`order-${id}`)?.classList.toggle('open');
}

function reorder(items) {
    items.forEach(item => {
        addToCart({
            productId: Math.random(),
            name: item.name,
            price: item.price,
            quantity: item.qty,
            options: {}
        });
    });
    showToast('Товары добавлены в корзину');
}

function renderFavorites() {
    const container = document.getElementById('favoritesGrid');
    if (!container) return;
    
    if (favorites.length === 0) {
        container.innerHTML = '<div class="empty-state" style="grid-column:1/-1">В избранном пока ничего нет</div>';
        return;
    }
    
    container.innerHTML = favorites.map(fav => `
        <div class="fav-card" onclick="location.href='product.html?id=${fav.id}'">
            <div class="fav-card-img">
                <div class="fav-card-img-inner" style="background-image: url('${fav.image}')"></div>
                <button class="fav-remove" onclick="event.stopPropagation();removeFavorite(${fav.id})">✕</button>
            </div>
            <div class="fav-card-name">${fav.name}</div>
            <div class="fav-card-price">${formatPrice(fav.price)}</div>
            <button class="btn-add-cart" onclick="event.stopPropagation();addToCart({productId: ${fav.id}, name: '${fav.name.replace(/'/g, "\\'")}', price: ${fav.price}, quantity: 1, options: {}})">
                В корзину
            </button>
        </div>
    `).join('');
}

function removeFavorite(id) {
    const newFavs = favorites.filter(f => f.id !== id);
    saveFavs(newFavs);
    favorites = newFavs;
    renderFavorites();
    showToast('Удалено из избранного');
}

function loadProfile() {
    const user = getUser();
    if (user) {
        const nameParts = user.name?.split(' ') || ['Пользователь', ''];
        document.getElementById('profileName').value = nameParts[0] || '';
        document.getElementById('profileSurname').value = nameParts.slice(1).join(' ') || '';
        document.getElementById('profileEmail').value = user.email || '';
        document.getElementById('profilePhone').value = user.phone || '';
        document.getElementById('profileAddress').value = user.address || '';
        
        document.getElementById('userName').textContent = user.name || 'Пользователь';
        document.getElementById('userEmail').textContent = user.email || '';
        document.getElementById('userAvatar').textContent = (user.name?.charAt(0) || 'П').toUpperCase();
    }
}

function saveProfile(e) {
    e.preventDefault();
    const userData = {
        name: `${document.getElementById('profileName').value} ${document.getElementById('profileSurname').value}`.trim(),
        email: document.getElementById('profileEmail').value,
        phone: document.getElementById('profilePhone').value,
        address: document.getElementById('profileAddress').value
    };
    localStorage.setItem('pm_user', JSON.stringify(userData));
    loadProfile();
    showToast('Данные сохранены');
}

function switchTab(tabName) {
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(`tab-${tabName}`)?.classList.add('active');
    document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
    
    if (tabName === 'favorites') {
        favorites = getFavs();
        renderFavorites();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadData();
    loadProfile();
    
    document.getElementById('profileForm')?.addEventListener('submit', saveProfile);
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });
    
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) switchTab(tab);
});

window.toggleOrder = toggleOrder;
window.reorder = reorder;
window.removeFavorite = removeFavorite;
window.switchTab = switchTab;