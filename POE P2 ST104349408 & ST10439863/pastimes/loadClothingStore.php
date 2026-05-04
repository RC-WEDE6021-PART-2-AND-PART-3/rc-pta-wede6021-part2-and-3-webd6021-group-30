<?php

require_once 'DBConn.php';
$conn = getDBConnection();
$msgs = [];

function runQ(mysqli $c, string $sql, string $label, array &$m): void {
    if ($c->query($sql)) $m[] = ['s',"&#10003; $label"];
    else $m[] = ['e',"&#10007; $label: ".$c->error];
}

$conn->query("SET FOREIGN_KEY_CHECKS=0");
foreach(['tblMessages','tblSellRequest','tblOrderItem','tblOrder','tblAorder','tblClothes','tblAdmin','tblUser'] as $t) runQ($conn,"DROP TABLE IF EXISTS `$t`","Dropped $t",$msgs);
$conn->query("SET FOREIGN_KEY_CHECKS=1");

runQ($conn,"CREATE TABLE tblUser (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(60)  NOT NULL,
    last_name   VARCHAR(60)  NOT NULL,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(120) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    phone       VARCHAR(20)  DEFAULT NULL,
    address     VARCHAR(255) DEFAULT NULL,
    user_status ENUM('pending','active','seller') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","Created tblUser",$msgs);

runQ($conn,"CREATE TABLE tblAdmin (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(120) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","Created tblAdmin",$msgs);

runQ($conn,"CREATE TABLE tblClothes (
    clothes_id INT AUTO_INCREMENT PRIMARY KEY,
    brand      VARCHAR(100) NOT NULL,
    item_name  VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    size       VARCHAR(20)  NOT NULL,
    condition_ ENUM('Excellent','Very Good','Good') NOT NULL DEFAULT 'Good',
    sell_price DECIMAL(10,2) NOT NULL,
    category   VARCHAR(60)  NOT NULL,
    image_file VARCHAR(255) DEFAULT NULL,
    seller_id  INT          DEFAULT NULL,
    stock_qty  INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_seller FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","Created tblClothes",$msgs);

runQ($conn,"CREATE TABLE tblOrder (
    order_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    order_ref        VARCHAR(20) NOT NULL,
    total_price      DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','paid','shipped','cancelled') NOT NULL DEFAULT 'pending',
    payment_status   ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) DEFAULT NULL,
    session_id       VARCHAR(120) DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tblorder_user FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","Created tblOrder",$msgs);

runQ($conn,"CREATE TABLE tblOrderItem (
    item_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    clothes_id INT DEFAULT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_tblorderitem_order   FOREIGN KEY (order_id)   REFERENCES tblOrder(order_id)    ON DELETE CASCADE,
    CONSTRAINT fk_tblorderitem_clothes FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4","Created tblOrderItem",$msgs);

$ap = md5('Password1');
$conn->query("INSERT IGNORE INTO tblAdmin (username,email,password) VALUES ('admin','admin@pastimes.co.za','$ap')");
$msgs[] = ['s','&#10003; Admin seeded (admin@pastimes.co.za / Password1)'];

$file = __DIR__.'/database/userData.txt';
$n = 0;
if (file_exists($file)) {
    foreach(file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line),'first_name')) continue;
        $p = preg_split('/\s+/',trim($line));
        if (count($p)<5) continue;
        [$fn,$ln,$un,$em,$pw]=$p;
        $st=$conn->prepare("INSERT IGNORE INTO tblUser (first_name,last_name,username,email,password,user_status) VALUES (?,?,?,?,?,'active')");
        $st->bind_param('sssss',$fn,$ln,$un,$em,$pw);
        if($st->execute()) $n++;
        $st->close();
    }
}
$msgs[] = ['s',"&#10003; $n user(s) loaded from userData.txt"];
$conn->close();
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Load ClothingStore — Pastimes</title>
<style>body{font-family:sans-serif;background:#f0f0f5;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem}.card{background:#fff;border-radius:1.25rem;padding:2rem;max-width:600px;width:100%;box-shadow:0 16px 48px rgba(0,0,0,.1)}.msg{padding:.55rem .875rem;border-radius:.5rem;margin-bottom:.4rem;font-size:.82rem}.s{background:#dcfce7;color:#166534}.e{background:#fee2e2;color:#991b1b}.btn{display:inline-flex;align-items:center;gap:.4rem;margin-top:1.5rem;padding:.6rem 1.25rem;background:#111;color:#fff;border-radius:9999px;text-decoration:none;font-size:.83rem;margin-right:.5rem}</style>
</head><body><div class="card">
<h1 style="font-size:1.3rem;margin-bottom:1.25rem;">Load ClothingStore Database</h1>
<?php foreach($msgs as [$t,$m]): ?>
<div class="msg <?= $t ?>"><?= $m ?></div>
<?php endforeach ?>
<p style="font-size:.78rem;color:#666;margin-top:1rem;">Run <a href="setup.php">setup.php</a> to also seed clothing items.</p>
<a class="btn" href="index.php">Back to site</a>
<a class="btn" href="admin_login.php">Admin panel</a>
</div></body></html>
