# Планета Мебели — Интернет-магазин мебели

## Описание проекта

Веб-сайт интернет-магазина мебели "Планета Мебели" с полным функционалом: каталог товаров, корзина, оформление заказов, личный кабинет, админ-панель, формы обратной связи и заявок от дизайнеров.

## Технологии

- **Backend:** PHP 7.4+ (нативный PHP без фреймворков)
- **Frontend:** HTML5, CSS3, JavaScript (ES6)
- **База данных:** MySQL
- **Библиотеки:** 
  - Swiper.js (карусели и слайдеры)
  - Font Awesome 6 (иконки)
  - Google Fonts (шрифты Cormorant Garamond и Jost)

## Структура проекта

```
planet-mebeli.loc/
├── index.php                 # Главная страница
├── catalog.php               # Каталог товаров
├── product.php              # Страница товара
├── cart.php                 # Корзина
├── checkout.php             # Оформление заказа
├── profile.php              # Личный кабинет
├── contacts.php             # Контакты
├── about.php                # О компании
├── delivery.php             # Доставка и оплата
├── designers.php            # Страница для дизайнеров (форма заявки)
│
├── admin/                   # Админ-панель
│   ├── index.php           # Главная страница админки
│   ├── products.php        # Управление товарами
│   ├── orders.php          # Управление заказами
│   ├── messages.php        # Сообщения с сайта
│   └── admin_designers.php # Заявки от дизайнеров
│
├── includes/                # Подключаемые файлы
│   ├── db.php              # Подключение к БД
│   ├── functions.php       # Вспомогательные функции
│   └── auth.php            # Авторизация
│
├── uploads/                 # Загруженные файлы
│   ├── products/           # Изображения товаров
│   └── slides/             # Изображения слайдера
│
└── assets/                  # Статические файлы
    ├── css/                # Стили
    ├── js/                 # Скрипты
    └── images/             # Изображения
```

## Установка

### 1. Требования

- PHP 7.4 или выше
- MySQL 5.7 или выше
- Apache/Nginx
- OpenServer / XAMPP / MAMP / Docker

### 2. Клонирование репозитория

```bash
git clone https://github.com/your-username/planet-mebeli.git
cd planet-mebeli
```

### 3. Настройка базы данных

1. Создайте базу данных MySQL (например, `planet_mebeli`)
2. Импортируйте SQL-файл из папки `database/`

### 4. Настройка подключения

Отредактируйте файл `includes/db.php`:

```php
$host = 'localhost';
$dbname = 'planet_mebeli';
$user = 'root';
$pass = '';
```

### 5. Настройка прав доступа

```bash
chmod -R 755 uploads/
chmod -R 755 admin/
```

## Структура базы данных

### Таблица `products` (товары)
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category VARCHAR(100),
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    image VARCHAR(500),
    material VARCHAR(100),
    description TEXT,
    specs TEXT,
    stock ENUM('in', 'low', 'out') DEFAULT 'in',
    stock_text VARCHAR(100) DEFAULT 'В наличии',
    is_new TINYINT(1) DEFAULT 0,
    is_hit TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Таблица `orders` (заказы)
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT,
    delivery_method ENUM('courier', 'pickup') DEFAULT 'courier',
    payment_method ENUM('card', 'cash', 'bank') DEFAULT 'card',
    comment TEXT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('new', 'processing', 'delivered', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Таблица `order_items` (товары в заказе)
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

### Таблица `messages` (сообщения с сайта)
```sql
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Таблица `designer_applications` (заявки дизайнеров)
```sql
CREATE TABLE designer_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company VARCHAR(200),
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    request_type ENUM('interior', 'architect', 'studio', 'other'),
    message TEXT,
    status ENUM('new', 'in_progress', 'completed', 'rejected') DEFAULT 'new',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Функционал

### Пользовательская часть

- **Главная страница** — слайдер, хиты продаж, новинки, категории
- **Каталог товаров** — отображение всех товаров с фильтрацией
- **Карточка товара** — детальная информация, галерея, характеристики, отзывы
- **Корзина** — добавление/удаление товаров, изменение количества
- **Оформление заказа** — форма с контактными данными, способами доставки и оплаты
- **Личный кабинет** — история заказов, избранное, редактирование профиля
- **Форма обратной связи** — отправка сообщений администратору
- **Форма для дизайнеров** — заявка на сотрудничество

### Административная панель

Доступ по адресу: `/admin/`

**Возможности:**
- Просмотр статистики (товары, заказы, сообщения)
- Управление товарами (CRUD: создание, редактирование, удаление)
- Загрузка изображений с автоматической конвертацией в WebP
- Управление заказами (изменение статуса, просмотр деталей)
- Просмотр и удаление сообщений от пользователей
- Просмотр и обработка заявок от дизайнеров
- Отметка прочитанных/непрочитанных сообщений и заявок

## Администратор

**Логин по умолчанию:**
- Email: `admin@planeta-mebeli.ru`
- Пароль: `admin123`

(Пароль можно изменить в базе данных, используя функцию `password_hash()`)

## Особенности

### Автоматическая конвертация изображений

При загрузке изображений через админ-панель они автоматически конвертируются в формат WebP для оптимизации загрузки сайта.

### Адаптивный дизайн

Сайт полностью адаптивен и корректно отображается на всех устройствах:
- Десктоп (1440px+)
- Ноутбуки (1024px-1440px)
- Планшеты (768px-1024px)
- Мобильные телефоны (320px-768px)

### Бургер-меню

На мобильных устройствах стандартное меню заменяется на бургер-меню с выезжающей панелью.

### Корзина

Корзина реализована с использованием `localStorage`, что позволяет сохранять товары даже после перезагрузки страницы без авторизации.

## API и вспомогательные функции

### Основные функции в `includes/functions.php`:

- `db()` — получение соединения с БД
- `getProducts($filters)` — получение списка товаров
- `getProduct($id)` — получение товара по ID
- `formatPrice($price)` — форматирование цены
- `generateSlug($string)` — генерация URL-slug
- `requireAdmin()` — проверка прав администратора
- `isUserLoggedIn()` — проверка авторизации

## Настройка окружения

### Для OpenServer

1. Поместите проект в папку `domains/planet-mebeli.loc`
2. В настройках домена укажите корневую директорию
3. Создайте базу данных через phpMyAdmin

### Для XAMPP

1. Поместите проект в `htdocs/planet-mebeli`
2. Запустите Apache и MySQL
3. Откройте `http://localhost/planet-mebeli`

## Безопасность

- Защита от SQL-инъекций через PDO и подготовленные запросы
- Экранирование вывода (htmlspecialchars)
- Проверка прав доступа в админ-панели
- Защита паролей (хэширование)
- Валидация форм на клиенте и сервере

## Поддержка браузеров

- Chrome (последняя версия)
- Firefox (последняя версия)
- Safari (последняя версия)
- Edge (последняя версия)
- Opera (последняя версия)

## Разработчик

Проект разработан компанией **Лес Тех** — специализация на разработке WEB-проектов.

## Лицензия

© 2026 Планета Мебели. Все права защищены.


---

## Дополнительная информация

### Версия PHP
Рекомендуется PHP 7.4 или выше

### Настройки PHP
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### Техническая поддержка
По вопросам технической поддержки обращайтесь в компанию-разработчика **Лес Тех**:
- Email: lestech1@yandex.ru

