-- ============================================================
-- add_new_products.sql
-- Run this in phpMyAdmin to add 27 new products to the live
-- ClothingStore database without resetting existing data.
-- ============================================================

USE ClothingStore;

INSERT INTO tblClothes (brand, item_name, description, size, condition_, sell_price, category, image_file, stock_qty) VALUES

-- Tops
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

-- Trousers & Jeans
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

-- Jewellery
('Unbranded',      'Silver Diamond Solitaire Ring',      'Classic silver-tone solitaire ring with CZ diamond centre.',               'One Size', 'Excellent', 450.00,  'Jewellery', 'silver-diamond-solitaire-ring.jpg',         1),
('Unbranded',      'Silver Diamond Halo Twist Ring',     'Elegant halo twist band with CZ stones in silver.',                       'One Size', 'Excellent', 530.00,  'Jewellery', 'silver-diamond-halo-ring.jpg',              1),
('Unbranded',      'Gold Clover Mother of Pearl Bracelet','18k gold-plated clover bracelet with mother-of-pearl inlay.',             'One Size', 'Excellent', 490.00,  'Jewellery', 'gold-clover-bracelet-mother-of-pearl.jpg',  1),
('Unbranded',      'Silver Cuban Link Bracelet',         'Chunky silver-tone Cuban link chain bracelet.',                           'One Size', 'Very Good', 360.00,  'Jewellery', 'silver-cuban-link-bracelet.jpg',            1),
('Unbranded',      'Assorted Gold Jewellery Set',        'Curated set of gold-tone rings, chains and pendants.',                    'One Size', 'Good',      620.00,  'Jewellery', 'gold-jewelry-collection.jpg',               1),
('Unbranded',      'Gold Diamond Bangle Set (3-Piece)',  'Set of 3 gold-plated bangles with CZ diamond accents.',                   'One Size', 'Excellent', 780.00,  'Jewellery', 'gold-diamond-bangle-set.jpg',               1),
('Unbranded',      'Malachite Clover Jewellery Set',     'Matching necklace, bracelet and earrings in green malachite and gold.',   'One Size', 'Excellent', 695.00,  'Jewellery', 'green-malachite-clover-jewelry-set.jpg',    1);
