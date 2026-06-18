-- ============================================================
-- myClothingStore.sql
-- Full DDL export for ClothingStore database
-- Pastimes - Pre-Loved Fashion Platform (South Africa)
-- Student: [Name] [Student Number]
-- Date: 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS ClothingStore
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ClothingStore;

SET FOREIGN_KEY_CHECKS = 0;

-- ── tblUser ──────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS tblUser;
CREATE TABLE tblUser (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(60)  NOT NULL,
    last_name   VARCHAR(60)  NOT NULL,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(120) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL COMMENT 'MD5 hashed',
    phone       VARCHAR(20)  DEFAULT NULL,
    address     VARCHAR(255) DEFAULT NULL,
    user_status ENUM('pending','active','seller') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── tblAdmin ─────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS tblAdmin;
CREATE TABLE tblAdmin (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(120) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL COMMENT 'MD5 hashed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── tblClothes ───────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS tblClothes;
CREATE TABLE tblClothes (
    clothes_id  INT AUTO_INCREMENT PRIMARY KEY,
    brand       VARCHAR(100)  NOT NULL,
    item_name   VARCHAR(255)  NOT NULL,
    description TEXT          DEFAULT NULL,
    size        VARCHAR(20)   NOT NULL,
    condition_  ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
    sell_price  DECIMAL(10,2) NOT NULL,
    category    VARCHAR(60)   NOT NULL,
    image_file  VARCHAR(255)  DEFAULT NULL,
    seller_id   INT           DEFAULT NULL,
    stock_qty   INT NOT NULL  DEFAULT 1,
    CONSTRAINT fk_clothes_seller
        FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── tblAorder ────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS tblAorder;
CREATE TABLE tblAorder (
    order_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    clothes_id  INT           NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    order_date  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    session_id  VARCHAR(120)  DEFAULT NULL,
    CONSTRAINT fk_order_user
        FOREIGN KEY (user_id)    REFERENCES tblUser(user_id)    ON DELETE CASCADE,
    CONSTRAINT fk_order_clothes
        FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Seed tblAdmin  (password: Password1) ─────────────────────────────────────
INSERT INTO tblAdmin (username, email, password) VALUES
('admin', 'admin@pastimes.co.za', '2ac9cb7dc02b3c0083eb70898e549b63');

-- ── Seed tblUser  (all passwords = Password1 → MD5) ──────────────────────────
INSERT INTO tblUser (first_name, last_name, username, email, password, phone, address, user_status) VALUES
('Thabo',   'Nkosi',        'user01',   'thabo.nkosi@gmail.com',          '2ac9cb7dc02b3c0083eb70898e549b63', '0821234567', '12 Mandela Ave, Soweto, 1804',        'active'),
('Naledi',  'Dlamini',      'naledi01', 'naledi.dlamini@webmail.co.za',    '2ac9cb7dc02b3c0083eb70898e549b63', '0833456789', '45 Nelson Rd, Durban North, 4051',    'active'),
('James',   'van der Berg', 'jvdb01',   'james.vdb@gmail.com',             '2ac9cb7dc02b3c0083eb70898e549b63', '0741122334', '7 Oak Street, Stellenbosch, 7600',    'seller'),
('Fatima',  'Patel',        'fatima01', 'fatima.patel@outlook.com',        '2ac9cb7dc02b3c0083eb70898e549b63', '0609988776', '23 Gandhi St, Lenasia, 1827',         'seller'),
('Sipho',   'Mokoena',      'sipho01',  'sipho.mokoena@pastimes.co.za',    '2ac9cb7dc02b3c0083eb70898e549b63', '0712233445', '88 Jan Smuts Ave, Rosebank, 2196',    'active'),
('Sarah',   'Smith',        'sarah01',  'sarah.smith@gmail.com',           '2ac9cb7dc02b3c0083eb70898e549b63', '0831231234', '5 Bree St, Cape Town CBD, 8001',      'seller'),
('Mike',    'Johnson',      'mike01',   'mike.johnson@gmail.com',          '2ac9cb7dc02b3c0083eb70898e549b63', '0724441234', '101 Louis Botha Ave, Orange Grove',   'seller'),
('Lisa',    'Brown',        'lisa01',   'lisa.brown@yahoo.com',            '2ac9cb7dc02b3c0083eb70898e549b63', '0664443322', '33 Florida Rd, Morningside, 4001',    'seller'),
('David',   'Wilson',       'david01',  'david.wilson@webmail.co.za',      '2ac9cb7dc02b3c0083eb70898e549b63', '0789991234', '19 Church St, Pretoria CBD, 0002',    'seller'),
('Lerato',  'Mokoena',      'lerato01', 'lerato.mokoena@gmail.com',        '2ac9cb7dc02b3c0083eb70898e549b63', '0651234567', '34 Vilakazi St, Orlando West, 1804',  'active');

-- ── Seed tblClothes (30 entries) ─────────────────────────────────────────────
INSERT INTO tblClothes (brand, item_name, description, size, condition_, sell_price, category, image_file, stock_qty) VALUES
('Nike',           'Nike Air Max 90',             'Air Max 90 sneakers, white/black colourway.',             '9',  'Excellent', 850.00,  'Sneakers',  'nike_airmax90.jpg',       2),
('Nike',           'Nike Air Force 1 Low',        'Nike Air Force 1 Low - Black and white.',                 '10', 'Excellent', 1199.00, 'Sneakers',  'nike_airforce1.jpg',      1),
('Nike',           'Nike Air Jordan 1 Retro',     'Nike Air Jordan 1 Retro - University Blue.',              '9',  'Very Good', 2499.00, 'Sneakers',  'nike_jordan1.jpg',        1),
('Nike',           'Nike Jordan 1 High OG',       'Nike Jordan 1 High OG - Pale Ivory.',                    '8',  'Excellent', 2799.00, 'Sneakers',  'nike_jordan1_high.jpg',   1),
('Nike',           'Nike Air Max 90 Alt',         'Nike Air Max 90 - Alternate colourway.',                  '10', 'Excellent', 899.00,  'Sneakers',  'nike_airmax90_2.jpg',     1),
('Nike',           'Nike Dunk Low Retro',         'Nike Dunk Low Retro - Panda colourway.',                  '9',  'Excellent', 1799.00, 'Sneakers',  'nike_dunklow.jpg',        1),
('Adidas',         'Adidas Originals Samba OG',  'Adidas Originals Samba OG - White gum sole.',             '9',  'Excellent', 950.00,  'Sneakers',  'adidas_samba.jpg',        2),
('Adidas',         'Adidas Ultraboost',           'Adidas Ultraboost Trainers - Triple white.',              '10', 'Very Good', 1599.00, 'Sneakers',  'adidas_ultraboost.jpg',   1),
('Adidas',         'Adidas Handball Spezial',     'Adidas Handball Spezial - Suede low-top.',                '9',  'Good',      699.00,  'Sneakers',  'adidas_spezial.jpg',      2),
('Adidas',         'Adidas EQT Support ADV',      'Adidas EQT Support ADV - Clean white.',                   '10', 'Very Good', 750.00,  'Sneakers',  'adidas_eqt.jpg',          1),
('Adidas',         'Adidas Samba OG Black',       'Adidas Samba OG - Black gum sole.',                       '10', 'Very Good', 1299.00, 'Sneakers',  'adidas_samba2.jpg',       1),
('Puma',           'Puma Speedcat OG',            'Puma Speedcat OG - Iconic racing sneaker.',               '10', 'Excellent', 749.00,  'Sneakers',  'puma_speedcat.jpg',       1),
('Puma',           'Puma Suede Classic',          'Puma Suede Classic - Timeless streetwear.',               '9',  'Very Good', 599.00,  'Sneakers',  'puma_suede.jpg',          1),
('Puma',           'Puma Basket Classic',         'Puma Basket Classic - White leather.',                    '9',  'Good',      549.00,  'Sneakers',  'puma_basket.jpg',         3),
('Zara',           'Zara Floral Midi Dress',      'Zara Floral Midi Dress - Summer print.',                  'M',  'Excellent', 350.00,  'Dresses',   'zara_floraldress.jpg',    1),
('Zara',           'Zara Floral Midi Dress Alt',  'Zara Floral Midi Dress - Multicolour print.',             'M',  'Excellent', 699.00,  'Dresses',   'zara_floraldress2.jpg',   1),
('Zara',           'Zara Printed Midi Dress',     'Zara Printed Midi Dress with Belt.',                      'S',  'Very Good', 499.00,  'Dresses',   'zara_printeddress.jpg',   1),
('Tommy Hilfiger', 'Tommy Hilfiger Flag Polo',    'Tommy Hilfiger Flag Logo Polo Shirt.',                    'L',  'Excellent', 399.00,  'Tees',      'tommy_polo.jpg',          2),
('Tommy Hilfiger', 'Tommy Hilfiger Cable Knit',   'Tommy Hilfiger Heritage Cable Knit Sweater.',             'M',  'Good',      899.00,  'Knitwear',  'tommy_heritage.jpg',      1),
('Tommy Hilfiger', 'Tommy Hilfiger Knit Sweater', 'Tommy Hilfiger cable-knit crew sweater in navy.',         'M',  'Excellent', 699.00,  'Knitwear',  'tommy_knit.jpg',          1),
('Tommy Hilfiger', 'Tommy Hilfiger Flag Tee',     'Tommy Hilfiger Flag Logo Tee.',                           'L',  'Very Good', 199.00,  'Tees',      'tommy_flagtee.jpg',       2),
('Calvin Klein',   'Calvin Klein Graphic Tee',    'Calvin Klein Jeans Graphics Tee - Black.',                'M',  'Excellent', 450.00,  'Tees',      'ck_graphictee.jpg',       2),
('Calvin Klein',   'Calvin Klein Monologo Sweat', 'Calvin Klein Outline Monologo Sweater - Black.',          'L',  'Very Good', 799.00,  'Knitwear',  'ck_sweater.jpg',          1),
('Calvin Klein',   'Calvin Klein Zip Hoodie',     'Calvin Klein Zip Hoodie - Black.',                        'M',  'Good',      299.00,  'Outerwear', 'ck_zip.jpg',              1),
('Calvin Klein',   'Calvin Klein Quilted Gilet',  'Calvin Klein Quilted Gilet - Black.',                     'L',  'Excellent', 1099.00, 'Outerwear', 'ck_gilet.jpg',            1),
('Guess',          'Guess Iconic Logo Tee',        'Guess Iconic Logo Tee - Pure white.',                    'M',  'Excellent', 349.00,  'Tees',      'guess_iconictee.jpg',     2),
('Guess',          'Guess Stacie Zip Sweatshirt',  'Guess Stacie Seamless Zip Sweatshirt - Black.',          'S',  'Good',      649.00,  'Outerwear', 'guess_zip.jpg',           1),
('H&M',            'H&M Oversized Knit',           'H&M Oversized Knit Sweater - Neutral tone.',             'XL', 'Very Good', 249.00,  'Knitwear',  'hm_knit.jpg',             2),
('Woolworths',     'Woolworths Maxi Dress',         'Woolworths Belted Pleated Maxi Dress.',                  'M',  'Excellent', 480.00,  'Dresses',   'woolworths_maxidress.jpg', 1),
('Woolworths',     'Woolworths Oxford Shirt',       'Woolworths Pure Cotton Oxford Shirt - White.',           'L',  'Excellent', 399.00,  'Tees',      'woolworths_shirt.jpg',    2),

-- ── Tops ─────────────────────────────────────────────────────────────────────
('Unbranded',      'Green Long-Sleeve T-Shirt',          'Plain green long-sleeve cotton tee - relaxed fit.',                          'M',        'Good',      120.00,  'Tees',      'green-long-sleeve-tshirt.jpg',              1),
('Sienne',         'Sienne Red Plaid Cropped Shirt',     'Red plaid cropped button-up shirt - relaxed street style.',                  'S',        'Very Good', 195.00,  'Tops',      'red-plaid-cropped-shirt.jpg',               1),
('Woolworths',     'Woolworths Pink Polo Shirt',         'Classic Woolworths pique polo in dusty pink.',                               'M',        'Excellent', 230.00,  'Tops',      'pink-polo-shirt.jpg',                       1),
('Unbranded',      'Money Is The Motive Graphic Tee',    'Bold graffiti-text graphic tee in black.',                                   'L',        'Good',      150.00,  'Tees',      'money-is-the-motive-graphic-tshirt.jpg',    1),
('Mocome',         'Mocome 5-Pack Multicolour T-Shirts', 'Set of 5 plain cotton tees in assorted colours.',                           'M',        'Excellent', 380.00,  'Tees',      'multicolor-tshirt-pack-mocome.jpg',         1),
('Adidas',         'Adidas Originals 3-Stripe Tee',      'Adidas Originals classic 3-stripe tee in black.',                           'L',        'Very Good', 290.00,  'Tees',      'black-adidas-3stripe-tshirt.jpg',           1),
('Unbranded',      'Olive V-Neck T-Shirt',               'Plain olive-green v-neck tee - everyday essential.',                        'M',        'Good',      110.00,  'Tees',      'olive-vneck-tshirt.jpg',                    1),
('Unbranded',      'Grey Oversized T-Shirt',             'Relaxed drop-shoulder oversized tee in light grey.',                        'L',        'Good',      140.00,  'Tees',      'grey-oversized-tshirt.jpg',                 1),
('Next',           'Next 5-Pack Multicolour T-Shirts',   'Next essential 5-pack of plain crew-neck tees.',                            'M',        'Excellent', 340.00,  'Tees',      'multicolor-tshirt-5pack-next.jpg',          1),
('Unbranded',      'Black Polo Shirt',                   'Classic pique polo shirt in plain black.',                                  'M',        'Very Good', 165.00,  'Tops',      'black-polo-shirt.jpg',                      1),

-- ── Trousers & Jeans ─────────────────────────────────────────────────────────
('Unbranded',      'Brown Pleated Dress Trousers',       'Tailored brown pleated trousers - smart casual.',                           '32',       'Good',      210.00,  'Trousers',  'brown-pleated-trousers.jpg',                1),
('Wrangler',       'Wrangler Blue Slim Fit Jeans',       'Wrangler slim-fit jeans in classic mid blue.',                              '32',       'Very Good', 360.00,  'Jeans',     'blue-slim-jeans-wrangler.jpg',              1),
('Unbranded',      'Black Chino Trousers',               'Slim-cut black chinos - versatile day-to-night wear.',                     '32',       'Good',      185.00,  'Trousers',  'black-chino-trousers.jpg',                  1),
('Next',           'Next Dark Blue Slim Fit Jeans',      'Next slim-fit jeans in dark indigo wash.',                                  '30',       'Very Good', 285.00,  'Jeans',     'dark-blue-slim-jeans-next.jpg',             1),
('Dickies',        'Dickies Olive Work Chino Trousers',  'Dickies durable work chinos in olive - relaxed fit.',                      '34',       'Good',      330.00,  'Trousers',  'olive-chino-trousers-dickies.jpg',          1),
('Unbranded',      'Beige Chino Trousers',               'Classic straight-cut beige chinos - smart casual.',                        '32',       'Good',      175.00,  'Trousers',  'beige-chino-trousers.jpg',                  1),
('Unbranded',      'Sage Green Chino Trousers',          'Soft sage-green chinos - slim tapered fit.',                               '32',       'Very Good', 195.00,  'Trousers',  'sage-green-chino-trousers.jpg',             1),
('Unbranded',      'Dark Blue Straight Fit Jeans',       'Relaxed straight-leg jeans in dark blue wash.',                            '32',       'Good',      240.00,  'Jeans',     'dark-blue-straight-jeans.jpg',              1),
('LL Bean',        'LL Bean Medium Blue Straight Jeans', 'LL Bean classic straight jeans in medium blue wash.',                      '34',       'Very Good', 310.00,  'Jeans',     'medium-blue-straight-jeans-llbean.jpg',     1),
('Unbranded',      'Black Formal Slim Trousers',         'Slim-cut formal dress trousers in plain black.',                           '32',       'Excellent', 260.00,  'Trousers',  'black-formal-dress-trousers.jpg',           1),

-- ── Jewellery ────────────────────────────────────────────────────────────────
('Unbranded',      'Silver Diamond Solitaire Ring',      'Classic silver-tone solitaire ring with CZ diamond centre.',               'One Size', 'Excellent', 450.00,  'Jewellery', 'silver-diamond-solitaire-ring.jpg',         1),
('Unbranded',      'Silver Diamond Halo Twist Ring',     'Elegant halo twist band with CZ stones in silver.',                       'One Size', 'Excellent', 530.00,  'Jewellery', 'silver-diamond-halo-ring.jpg',              1),
('Unbranded',      'Gold Clover Mother of Pearl Bracelet','18k gold-plated clover bracelet with mother-of-pearl inlay.',             'One Size', 'Excellent', 490.00,  'Jewellery', 'gold-clover-bracelet-mother-of-pearl.jpg',  1),
('Unbranded',      'Silver Cuban Link Bracelet',         'Chunky silver-tone Cuban link chain bracelet.',                           'One Size', 'Very Good', 360.00,  'Jewellery', 'silver-cuban-link-bracelet.jpg',            1),
('Unbranded',      'Assorted Gold Jewellery Set',        'Curated set of gold-tone rings, chains and pendants.',                    'One Size', 'Good',      620.00,  'Jewellery', 'gold-jewelry-collection.jpg',               1),
('Unbranded',      'Gold Diamond Bangle Set (3-Piece)',  'Set of 3 gold-plated bangles with CZ diamond accents.',                   'One Size', 'Excellent', 780.00,  'Jewellery', 'gold-diamond-bangle-set.jpg',               1),
('Unbranded',      'Malachite Clover Jewellery Set',     'Matching necklace, bracelet and earrings in green malachite and gold.',   'One Size', 'Excellent', 695.00,  'Jewellery', 'green-malachite-clover-jewelry-set.jpg',    1);
