<?php

$conn = @new mysqli('localhost', 'root', '');
$msgs = [];

if ($conn->connect_error) {
    die('<p style="font-family:sans-serif;color:red">Cannot connect to MySQL: ' . $conn->connect_error . '</p>');
}

$conn->query("CREATE DATABASE IF NOT EXISTS ClothingStore DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('ClothingStore');
$msgs[] = ['s', 'Database ClothingStore created.'];

$conn->query("SET FOREIGN_KEY_CHECKS=0");
foreach (['tblMessages','tblSellRequest','tblOrderItem','tblOrder','tblAorder','tblClothes','tblAdmin','tblUser'] as $t) {
    $conn->query("DROP TABLE IF EXISTS `$t`");
}
$conn->query("SET FOREIGN_KEY_CHECKS=1");

$conn->query("CREATE TABLE tblUser (
    user_id      INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(60)  NOT NULL,
    last_name    VARCHAR(60)  NOT NULL,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    email        VARCHAR(120) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL COMMENT 'MD5 hash',
    phone        VARCHAR(20)  DEFAULT NULL,
    address      VARCHAR(255) DEFAULT NULL,
    user_status  ENUM('pending','active','seller') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblUser created.'];

$conn->query("CREATE TABLE tblAdmin (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(120) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL COMMENT 'MD5 hash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblAdmin created.'];

$conn->query("CREATE TABLE tblClothes (
    clothes_id  INT AUTO_INCREMENT PRIMARY KEY,
    brand       VARCHAR(100) NOT NULL,
    item_name   VARCHAR(255) NOT NULL,
    description TEXT         DEFAULT NULL,
    size        VARCHAR(20)  NOT NULL,
    condition_  ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
    sell_price  DECIMAL(10,2) NOT NULL,
    category    VARCHAR(60)  NOT NULL,
    image_file  VARCHAR(255) DEFAULT NULL,
    seller_id   INT          DEFAULT NULL,
    stock_qty   INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_seller FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblClothes created.'];

$conn->query("CREATE TABLE tblOrder (
    order_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    order_ref        VARCHAR(20) NOT NULL,
    total_price      DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
    payment_status   ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) DEFAULT NULL,
    session_id       VARCHAR(120) DEFAULT NULL,
    payment_method   VARCHAR(20)  NOT NULL DEFAULT 'cod',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tblorder_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblOrder created.'];

$conn->query("CREATE TABLE tblOrderItem (
    item_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    clothes_id INT DEFAULT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_tblorderitem_order  FOREIGN KEY (order_id)   REFERENCES tblOrder(order_id)    ON DELETE CASCADE,
    CONSTRAINT fk_tblorderitem_clothes FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblOrderItem created.'];

$conn->query("CREATE TABLE tblSellRequest (
    request_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT           NOT NULL,
    brand        VARCHAR(100)  NOT NULL,
    item_name    VARCHAR(255)  NOT NULL,
    description  TEXT          DEFAULT NULL,
    size         VARCHAR(20)   NOT NULL,
    condition_   ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
    ask_price    DECIMAL(10,2) NOT NULL,
    category     VARCHAR(60)   NOT NULL,
    image_file   VARCHAR(255)  DEFAULT NULL,
    status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sellreq_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblSellRequest created.'];

$conn->query("CREATE TABLE tblMessages (
    message_id  INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT NOT NULL,
    receiver_id INT NOT NULL,
    clothes_id  INT DEFAULT NULL,
    subject     VARCHAR(255) NOT NULL,
    body        TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    sent_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_s FOREIGN KEY (sender_id)   REFERENCES tblUser(user_id)    ON DELETE CASCADE,
    CONSTRAINT fk_msg_r FOREIGN KEY (receiver_id) REFERENCES tblUser(user_id)    ON DELETE CASCADE,
    CONSTRAINT fk_msg_c FOREIGN KEY (clothes_id)  REFERENCES tblClothes(clothes_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$msgs[] = ['s', 'Table tblMessages created.'];

$ap = md5('Password1');   
$conn->query("INSERT INTO tblAdmin (username,email,password) VALUES ('admin','admin@pastimes.co.za','$ap')");
$msgs[] = ['s', 'Admin seeded: admin@pastimes.co.za / Password1'];

$p1 = md5('Password1');
$users = [
    // Demo user 'user01' removed
    ['Naledi',  'Dlamini',      'naledi01', 'naledi.dlamini@webmail.co.za',    $p1, '0833456789', '45 Nelson Rd, Durban North, 4051',    'active'],
    ['James',   'van der Berg', 'jvdb01',   'james.vdb@gmail.com',             $p1, '0741122334', '7 Oak Street, Stellenbosch, 7600',    'seller'],
    ['Fatima',  'Patel',        'fatima01', 'fatima.patel@outlook.com',        $p1, '0609988776', '23 Gandhi St, Lenasia, 1827',         'seller'],
    ['Sipho',   'Mokoena',      'sipho01',  'sipho.mokoena@pastimes.co.za',    $p1, '0712233445', '88 Jan Smuts Ave, Rosebank, 2196',    'active'],
    ['Sarah',   'Smith',        'sarah01',  'sarah.smith@gmail.com',           $p1, '0831231234', '5 Bree St, Cape Town CBD, 8001',      'seller'],
    ['Mike',    'Johnson',      'mike01',   'mike.johnson@gmail.com',          $p1, '0724441234', '101 Louis Botha Ave, Orange Grove',   'seller'],
    ['Lisa',    'Brown',        'lisa01',   'lisa.brown@yahoo.com',            $p1, '0664443322', '33 Florida Rd, Morningside, 4001',    'seller'],
    ['David',   'Wilson',       'david01',  'david.wilson@webmail.co.za',      $p1, '0789991234', '19 Church St, Pretoria CBD, 0002',    'seller'],
    ['Lerato',  'Mokoena',      'lerato01', 'lerato.mokoena@gmail.com',        $p1, '0651234567', '34 Vilakazi St, Orlando West, 1804',  'active'],
];
$su = $conn->prepare("INSERT INTO tblUser (first_name,last_name,username,email,password,phone,address,user_status) VALUES (?,?,?,?,?,?,?,?)");
$n = 0;
foreach ($users as [$fn,$ln,$un,$em,$pw,$ph,$ad,$st]) {
    $su->bind_param('ssssssss',$fn,$ln,$un,$em,$pw,$ph,$ad,$st);
    if ($su->execute()) $n++;
}
$su->close();
$msgs[] = ['s', "$n users seeded."];

$clothes = [
    
    ['Nike','Nike Air Max 90','Air Max 90 sneakers, white/black colourway.','9','Excellent',850.00,'Sneakers','nike_airmax90.jpg', null],
    ['Nike','Nike Air Force 1 Low','Nike Air Force 1 Low — Black and white.','10','Excellent',1199.00,'Sneakers','nike_airforce1.jpg', null],
    ['Nike','Nike Air Jordan 1 Retro','Nike Air Jordan 1 Retro — University Blue.','9','Very Good',2499.00,'Sneakers','nike_jordan1.jpg', null],
    ['Nike','Nike Jordan 1 High OG','Nike Jordan 1 High OG — Pale Ivory.','8','Excellent',2799.00,'Sneakers','nike_jordan1_high.jpg', null],
    ['Nike','Nike Air Max 90 Alt','Nike Air Max 90 - Alternate colourway.','10','Excellent',899.00,'Sneakers','nike_airmax90_2.jpg', 'fatima01'],
    ['Nike','Nike Dunk Low Retro','Nike Dunk Low Retro — Panda colourway.','9','Excellent',1799.00,'Sneakers','nike_dunklow.jpg', 'fatima01'],
    
    ['Adidas','Adidas Originals Samba OG','Adidas Originals Samba OG — White gum sole.','9','Excellent',950.00,'Sneakers','adidas_samba.jpg', 'sarah01'],
    ['Adidas','Adidas Ultraboost','Adidas Ultraboost Trainers — Triple white.','10','Very Good',1599.00,'Sneakers','adidas_ultraboost.jpg', 'sarah01'],
    ['Adidas','Adidas Handball Spezial','Adidas Handball Spezial — Suede low-top.','9','Good',699.00,'Sneakers','adidas_spezial.jpg', 'sarah01'],
    ['Adidas','Adidas EQT Support ADV','Adidas EQT Support ADV — Clean white.','10','Very Good',750.00,'Sneakers','adidas_eqt.jpg', 'sarah01'],
    ['Adidas','Adidas Samba OG Black','Adidas Samba OG - Black gum sole.','10','Very Good',1299.00,'Sneakers','adidas_samba2.jpg', 'naledi01'],
    
    ['Puma','Puma Speedcat OG','Puma Speedcat OG — Iconic racing sneaker.','10','Excellent',749.00,'Sneakers','puma_speedcat.jpg', 'lisa01'],
    ['Puma','Puma Suede Classic','Puma Suede Classic — Timeless streetwear.','9','Very Good',599.00,'Sneakers','puma_suede.jpg', 'lisa01'],
    ['Puma','Puma Basket Classic','Puma Basket Classic - White leather.','9','Good',549.00,'Sneakers','puma_basket.jpg', 'jvdb01'],
    
    ['Zara','Zara Floral Midi Dress','Zara Floral Midi Dress — Summer print.','M','Excellent',350.00,'Dresses','zara_floraldress.jpg', 'mike01'],
    ['Zara','Zara Floral Midi Dress Alt','Zara Floral Midi Dress — Multicolour print.','M','Excellent',699.00,'Dresses','zara_floraldress2.jpg', 'david01'],
    ['Zara','Zara Printed Midi Dress','Zara Printed Midi Dress with Belt.','S','Very Good',499.00,'Dresses','zara_printeddress.jpg', 'mike01'],
    
    ['Tommy Hilfiger','Tommy Hilfiger Flag Polo','Tommy Hilfiger Flag Logo Polo Shirt.','L','Excellent',399.00,'Tees','tommy_polo.jpg', 'david01'],
    ['Tommy Hilfiger','Tommy Hilfiger Cable Knit','Tommy Hilfiger Heritage Cable Knit Sweater.','M','Good',899.00,'Knitwear','tommy_heritage.jpg', 'david01'],
    ['Tommy Hilfiger','Tommy Hilfiger Knit Sweater','Tommy Hilfiger cable-knit crew sweater in navy.','M','Excellent',699.00,'Knitwear','tommy_knit.jpg', 'sarah01'],
    ['Tommy Hilfiger','Tommy Hilfiger Flag Tee','Tommy Hilfiger Flag Logo Tee.','L','Very Good',199.00,'Tees','tommy_flagtee.jpg', 'sipho01'],
    
    ['Calvin Klein','Calvin Klein Graphic Tee','Calvin Klein Jeans Graphics Tee — Black.','M','Excellent',450.00,'Tees','ck_graphictee.jpg', null],
    ['Calvin Klein','Calvin Klein Monologo Sweater','Calvin Klein Outline Monologo Sweater — Black.','L','Very Good',799.00,'Knitwear','ck_sweater.jpg', 'sarah01'],
    ['Calvin Klein','Calvin Klein Zip Hoodie','Calvin Klein Zip Hoodie - Black.','M','Good',299.00,'Outerwear','ck_zip.jpg', null],
    ['Calvin Klein','Calvin Klein Quilted Gilet','Calvin Klein Quilted Gilet - Black.','L','Excellent',1099.00,'Outerwear','ck_gilet.jpg', 'naledi01'],
    
    ['Guess','Guess Iconic Logo Tee','Guess Iconic Logo Tee — Pure white.','M','Excellent',349.00,'Tees','guess_iconictee.jpg', 'mike01'],
    ['Guess','Guess Stacie Zip Sweatshirt','Guess Stacie Seamless Zip Sweatshirt — Black.','S','Good',649.00,'Outerwear','guess_zip.jpg', 'lisa01'],
    ['Guess','Guess Winter Jacket','Guess Winter Jacket — Quilted black.','L','Very Good',699.00,'Outerwear','guess_jacket.jpg', null],
    ['Guess','Guess Graphic Tee','Guess graphic logo tee — white.','M','Excellent',699.00,'Tees','guess_tshirt.jpg', 'mike01'],
    ['Guess','Guess Denim Jacket','Guess Denim Jacket - Mid wash.','M','Excellent',699.00,'Outerwear','guess_jacket.jpg', null],
    
    ['H&M','H&M Oversized Knit','H&M Oversized Knit Sweater — Neutral tone.','XL','Very Good',249.00,'Knitwear','hm_knit.jpg', 'david01'],
    ['H&M','H&M Fine-Knit Sweater','H&M fine-knit pullover in oat melange.','M','Excellent',699.00,'Knitwear','hm_knit2.jpg', 'lisa01'],
    
    ['Woolworths','Woolworths Maxi Dress','Woolworths Belted Pleated Maxi Dress.','M','Excellent',480.00,'Dresses','woolworths_maxidress.jpg', null],
    ['Woolworths','Woolworths Golfer Knit','Woolworths Golfer Knit - Neutral stripe.','M','Very Good',449.00,'Knitwear','woolworths_golfer.jpg', 'jvdb01'],
    ['Woolworths','Woolworths Knit Set','Woolworths coordinated knit two-piece.','L','Excellent',699.00,'Knitwear','woolworths_knits.jpg', 'david01'],
    ['Woolworths','Woolworths Classic Sweater','Woolworths premium wool-blend crew sweater.','L','Excellent',699.00,'Knitwear','woolworths_sweater.jpg', null],
    ['Woolworths','Woolworths Oxford Shirt','Woolworths Pure Cotton Oxford Shirt — White.','L','Excellent',399.00,'Tees','woolworths_shirt.jpg', 'sipho01'],
    
    ['Puma','Puma Basket Classic (Alt)','Puma Basket Classic sneaker — alternative colourway.','M','Excellent',699.00,'Sneakers','puma_basket.jpg', 'sarah01'],
    ['Tommy Hilfiger','Tommy Hilfiger Heritage Knit','Tommy Hilfiger heritage crew knit — burgundy.','M','Excellent',699.00,'Knitwear','tommy_heritage.jpg', 'sarah01'],
    ['Calvin Klein','Calvin Klein Zip Jacket','Calvin Klein logo zip-up track jacket.','M','Excellent',699.00,'Outerwear','ck_zip.jpg', 'sarah01'],

    // Sneakers (new)
    ['Puma','Puma Speedcat OG Alt','Puma Speedcat OG — alternate colourway.','9','Very Good',699.00,'Sneakers','puma_speedcat2.jpg', 'jvdb01'],

    // Tees & Shirts (new)
    ['Adidas','Adidas 3-Stripe Tee','Classic Adidas 3-stripe tee — black.','M','Good',249.00,'Tees','black-adidas-3stripe-tshirt.jpg', 'naledi01'],
    ['Generic','Grey Oversized T-Shirt','Relaxed oversized tee in grey.','L','Good',149.00,'Tees','grey-oversized-tshirt.jpg', null],
    ['Generic','Red Plaid Cropped Shirt','Cropped red plaid shirt.','S','Good',199.00,'Tees','red-plaid-cropped-shirt.jpg', 'mike01'],
    ['Generic','Green Long-Sleeve T-Shirt','Solid green long-sleeve tee.','M','Good',179.00,'Tees','green-long-sleeve-tshirt.jpg', null],
    ['Generic','Money Is The Motive Graphic Tee','Bold slogan graphic tee.','M','Good',199.00,'Tees','money-is-the-motive-graphic-tshirt.jpg', 'sipho01'],
    ['Next','Multicolour T-Shirt 5-Pack','Next crew-neck tee 5-pack — multicolour.','M','Good',349.00,'Tees','multicolor-tshirt-5pack-next.jpg', null],
    ['Mocome','Multicolour T-Shirt Pack','Mocome value multicolour tee pack.','M','Good',299.00,'Tees','multicolor-tshirt-pack-mocome.jpg', null],
    ['Generic','Black Polo Shirt','Classic slim-fit black polo shirt.','L','Good',249.00,'Tees','black-polo-shirt.jpg', 'david01'],
    ['Generic','Pink Polo Shirt','Classic slim-fit pink polo shirt.','M','Good',249.00,'Tees','pink-polo-shirt.jpg', 'lisa01'],
    ['Generic','Olive V-Neck T-Shirt','Olive green v-neck tee.','M','Good',169.00,'Tees','olive-vneck-tshirt.jpg', null],

    // Trousers (new)
    ['Generic','Black Chino Trousers','Slim-fit black chino trousers.','32','Good',299.00,'Trousers','black-chino-trousers.jpg', 'jvdb01'],
    ['Generic','Black Formal Dress Trousers','Tailored black formal trousers.','32','Good',349.00,'Trousers','black-formal-dress-trousers.jpg', null],
    ['Generic','Beige Chino Trousers','Classic beige chino trousers.','32','Good',279.00,'Trousers','beige-chino-trousers.jpg', 'naledi01'],
    ['Generic','Sage Green Chino Trousers','Sage green casual chino trousers.','32','Good',279.00,'Trousers','sage-green-chino-trousers.jpg', null],
    ['Generic','Brown Pleated Trousers','Brown pleated dress trousers.','32','Good',329.00,'Trousers','brown-pleated-trousers.jpg', 'david01'],
    ['Dickies','Dickies Olive Chino Trousers','Dickies utility olive chino.','32','Good',349.00,'Trousers','olive-chino-trousers-dickies.jpg', 'mike01'],

    // Jeans (new)
    ['Wrangler','Wrangler Blue Slim Jeans','Wrangler slim-fit blue denim jeans.','32','Good',399.00,'Jeans','blue-slim-jeans-wrangler.jpg', 'sarah01'],
    ['Next','Next Dark Blue Slim Jeans','Next slim-fit dark-wash jeans.','32','Good',349.00,'Jeans','dark-blue-slim-jeans-next.jpg', 'lisa01'],
    ['Generic','Dark Blue Straight Jeans','Dark blue straight-leg denim jeans.','32','Good',349.00,'Jeans','dark-blue-straight-jeans.jpg', null],
    ['L.L.Bean','L.L.Bean Medium Blue Straight Jeans','L.L.Bean classic straight-leg jeans.','32','Good',449.00,'Jeans','medium-blue-straight-jeans-llbean.jpg', 'fatima01'],

    // Accessories (new)
    ['Generic','Silver Cuban Link Bracelet','Stainless steel Cuban link chain bracelet.','One Size','Excellent',299.00,'Accessories','silver-cuban-link-bracelet.jpg', 'sipho01'],
    ['Generic','Gold Clover & Mother of Pearl Bracelet','Gold-plated clover charm bracelet with mother of pearl.','One Size','Excellent',349.00,'Accessories','gold-clover-bracelet-mother-of-pearl.jpg', null],
    ['Generic','Green Malachite Clover Jewellery Set','Malachite clover necklace & earring set.','One Size','Excellent',449.00,'Accessories','green-malachite-clover-jewelry-set.jpg', 'lisa01'],
    ['Generic','Gold Jewellery Collection','Mixed gold-tone jewellery set.','One Size','Excellent',399.00,'Accessories','gold-jewelry-collection.jpg', null],
    ['Generic','Silver Diamond Solitaire Ring','Sterling silver solitaire ring.','One Size','Excellent',499.00,'Accessories','silver-diamond-solitaire-ring.jpg', 'naledi01'],
    ['Generic','Silver Diamond Halo Ring','Sterling silver halo-set ring.','One Size','Excellent',549.00,'Accessories','silver-diamond-halo-ring.jpg', null],
    ['Generic','Gold Diamond Bangle Set','Gold-plated diamond-accent bangle set.','One Size','Excellent',599.00,'Accessories','gold-diamond-bangle-set.jpg', 'fatima01'],
];

$userMap = [];
$res = $conn->query("SELECT user_id, username FROM tblUser");
while ($r = $res->fetch_assoc()) $userMap[$r['username']] = $r['user_id'];

$stWith = $conn->prepare("INSERT INTO tblClothes (brand,item_name,description,size,condition_,sell_price,category,image_file,seller_id,stock_qty) VALUES (?,?,?,?,?,?,?,?,?,?)");
$stNone = $conn->prepare("INSERT INTO tblClothes (brand,item_name,description,size,condition_,sell_price,category,image_file,seller_id,stock_qty) VALUES (?,?,?,?,?,?,?,?,NULL,?)");
$cn = 0;
foreach ($clothes as [$br,$nm,$de,$si,$co,$pr,$ca,$im,$seller_un]) {
    $seller_id = $seller_un ? ($userMap[$seller_un] ?? null) : null;
    $qty = rand(1, 3);
    if ($seller_id !== null) {
        $stWith->bind_param('sssssdssii', $br,$nm,$de,$si,$co,$pr,$ca,$im,$seller_id,$qty);
        if ($stWith->execute()) $cn++;
        else $msgs[] = ['e', "Failed [$nm]: " . $stWith->error];
    } else {
        $stNone->bind_param('sssssdssi', $br,$nm,$de,$si,$co,$pr,$ca,$im,$qty);
        if ($stNone->execute()) $cn++;
        else $msgs[] = ['e', "Failed [$nm]: " . $stNone->error];
    }
}
$stWith->close(); $stNone->close();
$msgs[] = ['s', "$cn clothing items seeded."];

$txt = "first_name last_name username email MD5_password\n";
foreach ($users as [$fn,$ln,$un,$em,$pw,$ph,$ad,$st]) {
    $txt .= "$fn $ln $un $em $pw\n";
}
file_put_contents(__DIR__ . '/database/userData.txt', $txt);
$msgs[] = ['s', 'database/userData.txt written.'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pastimes Setup</title>
<style>
 *{box-sizing:border-box;margin:0;padding:0}
 body{font-family:'Segoe UI',sans-serif;background:#f0f0f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
 .card{background:#fff;border-radius:1.5rem;padding:2.5rem;max-width:600px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.12)}
 h1{font-size:1.5rem;margin-bottom:.25rem}
 .sub{color:#666;font-size:.875rem;margin-bottom:1.75rem}
 .msg{display:flex;align-items:flex-start;gap:.6rem;padding:.55rem .875rem;border-radius:.5rem;margin-bottom:.5rem;font-size:.83rem}
 .s{background:#dcfce7;color:#166534}.e{background:#fee2e2;color:#991b1b}
 .creds{margin-top:1.5rem;padding:1.1rem;background:#f8f7ff;border-radius:.75rem;font-size:.82rem;line-height:1.9}
 .creds code{background:#e8e7f0;padding:.1rem .4rem;border-radius:.3rem}
 .btns{margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap}
 .btn{padding:.65rem 1.4rem;border-radius:9999px;text-decoration:none;font-size:.875rem;font-weight:500;display:inline-flex;align-items:center;gap:.4rem}
 .btn-dark{background:#111;color:#fff}.btn-outline{background:#f4f4f8;color:#111;border:1px solid #ddd}
</style>
</head>
<body>
<div class="card">
  <h1>Pastimes — Setup Complete</h1>
  <p class="sub">ClothingStore database initialised for XAMPP</p>
  <?php foreach($msgs as [$t,$m]): ?>
    <div class="msg <?= $t ?>"><?= $t==='s'?'&#10003;':'&#10007;' ?> <?= htmlspecialchars($m) ?></div>
  <?php endforeach ?>
  <div class="creds">
    <strong>Admin Login</strong><br>
    Email: <code>admin@pastimes.co.za</code> &nbsp;|&nbsp; Password: <code>Password1</code><br><br>
    <strong>Demo User Login</strong><br>
    Username: <code>user01</code> &nbsp;|&nbsp; Password: <code>Password1</code><br>
    Username: <code>sarah01</code> &nbsp;|&nbsp; Password: <code>Password1</code> (seller)
  </div>
  <div class="btns">
    <a class="btn btn-dark" href="index.php">Go to Pastimes</a>
    <a class="btn btn-outline" href="admin_login.php">Admin Panel</a>
  </div>
</div>
</body>
</html>
