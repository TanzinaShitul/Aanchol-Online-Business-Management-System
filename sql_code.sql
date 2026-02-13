
CREATE DATABASE aanchol_db;
USE aanchol_db;

CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL
);

CREATE TABLE districts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    division_id INT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    FOREIGN KEY (division_id) REFERENCES divisions(id)
);

CREATE TABLE upazilas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    district_id INT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id)
);


CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    division_id INT,
    district_id INT,
    upazila_id INT,
    detailed_address TEXT,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (upazila_id) REFERENCES upazilas(id)
);


INSERT INTO divisions (name, name_en) VALUES
('ঢাকা', 'Dhaka'),
('চট্টগ্রাম', 'Chittagong'),
('খুলনা', 'Khulna'),
('রাজশাহী', 'Rajshahi'),
('বরিশাল', 'Barisal'),
('সিলেট', 'Sylhet'),
('রংপুর', 'Rangpur'),
('ময়মনসিংহ', 'Mymensingh');

INSERT INTO districts (division_id, name, name_en) VALUES
(1, 'ঢাকা', 'Dhaka'),
(1, 'গাজীপুর', 'Gazipur'),
(1, 'নারায়ণগঞ্জ', 'Narayanganj'),
(2, 'চট্টগ্রাম', 'Chittagong'),
(2, 'কক্সবাজার', 'Coxs Bazar'),
(3, 'খুলনা', 'Khulna'),
(3, 'বাগেরহাট', 'Bagerhat'),
(4, 'রাজশাহী', 'Rajshahi'),
(4, 'নাটোর', 'Natore');

INSERT INTO upazilas (district_id, name, name_en) VALUES
(1, 'ঢাকা সদর', 'Dhaka Sadar'),
(1, 'মিরপুর', 'Mirpur'),
(1, 'উত্তরা', 'Uttara'),
(2, 'গাজীপুর সদর', 'Gazipur Sadar'),
(2, 'কালীগঞ্জ', 'Kaliakair'),
(3, 'নারায়ণগঞ্জ সদর', 'Narayanganj Sadar'),
(3, 'সোনারগাঁ', 'Sonargaon'),
(4, 'চট্টগ্রাম সদর', 'Chittagong Sadar'),
(4, 'পটিয়া', 'Patiya'),
(5, 'কক্সবাজার সদর', 'Coxs Bazar Sadar'),
(5, 'টেকনাফ', 'Teknaf');


INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@aanchol.com', '$2y$10$YourHashedPasswordHere', NULL, 'admin');


CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL
);


CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);


CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    division_id INT,
    district_id INT,
    upazila_id INT,
    detailed_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (upazila_id) REFERENCES upazilas(id)
);


CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


INSERT INTO categories (name, slug) VALUES
('Bangles', 'bangles'),
('Sarees', 'sarees'),
('Panjabi', 'panjabi'),
('Dress', 'dress'),
('Bags', 'bags');