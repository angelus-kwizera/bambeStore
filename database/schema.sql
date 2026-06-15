-- Bambe E-Commerce Database Schema
-- Clothes & Shoes Store - Rwanda

CREATE DATABASE IF NOT EXISTS bambe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bambe;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL DEFAULT 'Kigali',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Clothes', 'clothes', 'Trendy clothing for men and women'),
('Shoes', 'shoes', 'Stylish footwear for every occasion');

-- Products - Clothes
INSERT INTO products (category_id, name, slug, description, price, image_url, stock, featured) VALUES
(1, 'Classic White Cotton T-Shirt', 'classic-white-tshirt', 'Premium cotton t-shirt with a comfortable fit. Perfect for everyday wear in Kigali''s warm climate.', 15000.00, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=600&fit=crop', 50, 1),
(1, 'Denim Jacket - Blue', 'denim-jacket-blue', 'Stylish blue denim jacket with modern cut. A wardrobe essential for cool evenings.', 45000.00, 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=600&h=600&fit=crop', 30, 1),
(1, 'African Print Dress', 'african-print-dress', 'Beautiful Ankara-inspired dress celebrating Rwandan fashion heritage. Vibrant colors and elegant design.', 55000.00, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&h=600&fit=crop', 25, 1),
(1, 'Slim Fit Chinos - Khaki', 'slim-fit-chinos-khaki', 'Versatile khaki chinos suitable for office or casual outings. Stretch fabric for comfort.', 35000.00, 'https://images.unsplash.com/photo-1473966968600-fa801b279a01?w=600&h=600&fit=crop', 40, 0),
(1, 'Hooded Sweatshirt - Grey', 'hooded-sweatshirt-grey', 'Cozy grey hoodie perfect for Musanze trips or rainy season in Rwanda.', 40000.00, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=600&fit=crop', 35, 0),
(1, 'Linen Button-Up Shirt', 'linen-button-up-shirt', 'Breathable linen shirt ideal for Rwanda''s tropical weather. Available in natural beige.', 32000.00, 'https://images.unsplash.com/photo-1596755094514-f87e34085b56?w=600&h=600&fit=crop', 28, 0),
(1, 'Women''s Yoga Leggings', 'womens-yoga-leggings', 'High-performance leggings for workouts or casual wear. Moisture-wicking fabric.', 28000.00, 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=600&h=600&fit=crop', 45, 0),
(1, 'Men''s Polo Shirt - Navy', 'mens-polo-shirt-navy', 'Classic navy polo shirt. Smart casual look for business meetings or weekend brunch.', 25000.00, 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=600&h=600&fit=crop', 55, 1);

-- Products - Shoes
INSERT INTO products (category_id, name, slug, description, price, image_url, stock, featured) VALUES
(2, 'Running Sneakers - White', 'running-sneakers-white', 'Lightweight running shoes with cushioned sole. Great for jogging around Kigali.', 65000.00, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop', 40, 1),
(2, 'Leather Oxford Shoes', 'leather-oxford-shoes', 'Handcrafted leather oxford shoes for formal occasions. Premium quality finish.', 85000.00, 'https://images.unsplash.com/photo-1614252238956-18c856637de3?w=600&h=600&fit=crop', 20, 1),
(2, 'Canvas Slip-On - Black', 'canvas-slip-on-black', 'Easy slip-on canvas shoes for everyday comfort. Minimalist black design.', 30000.00, 'https://images.unsplash.com/photo-1525966220534-1d18576a451d?w=600&h=600&fit=crop', 60, 0),
(2, 'Hiking Boots - Brown', 'hiking-boots-brown', 'Durable hiking boots for Volcanoes National Park adventures. Waterproof leather.', 95000.00, 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?w=600&h=600&fit=crop', 15, 0),
(2, 'Women''s Heeled Sandals', 'womens-heeled-sandals', 'Elegant heeled sandals for special occasions. Comfortable block heel design.', 48000.00, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=600&h=600&fit=crop', 22, 1),
(2, 'Sport Sandals - Outdoor', 'sport-sandals-outdoor', 'Rugged sport sandals for outdoor activities. Adjustable straps and grip sole.', 38000.00, 'https://images.unsplash.com/photo-1603487743391-5e37b7e5c1a8?w=600&h=600&fit=crop', 35, 0),
(2, 'Kids School Shoes', 'kids-school-shoes', 'Durable black school shoes for children. Comfortable all-day wear.', 35000.00, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&h=600&fit=crop', 50, 0),
(2, 'Basketball High-Tops', 'basketball-high-tops', 'Classic high-top basketball shoes with ankle support. Bold red and white design.', 72000.00, 'https://images.unsplash.com/photo-1511556532299-6fae28f8f0e0?w=600&h=600&fit=crop', 18, 1);

-- Default admin (username: admin, password: admin123)
INSERT INTO admins (username, password_hash, full_name) VALUES
('admin', '$2y$10$0oU1rpsxqRtva1TwZFxSSOT70VxgbCkP7ZQ2YhjoXFgr5O0grKH/u', 'Bambe Administrator');
