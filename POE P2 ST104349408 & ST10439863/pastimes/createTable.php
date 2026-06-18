<?php

require_once 'DBConn.php';
$conn = getDBConnection();
$msgs = [];

$conn->query("DROP TABLE IF EXISTS tblUser");
$msgs[] = ['s','Step 1: tblUser dropped (if it existed).'];

if ($conn->query("CREATE TABLE tblUser (
    user_id      INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(60)  NOT NULL,
    last_name    VARCHAR(60)  NOT NULL,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    email        VARCHAR(120) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    phone        VARCHAR(20)  DEFAULT NULL,
    address      VARCHAR(255) DEFAULT NULL,
    user_status  ENUM('pending','active','seller') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")) {
    $msgs[] = ['s','Step 2: tblUser created.'];
} else {
    $msgs[] = ['e','Step 2 error: '.$conn->error];
}

$file = __DIR__.'/database/userData.txt';
if (!file_exists($file)) {
    $msgs[] = ['e','Step 3: database/userData.txt not found.'];
} else {
    $lines = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $n = 0;
    foreach ($lines as $line) {
        if (str_starts_with(trim($line),'first_name')) continue;
        $p = preg_split('/\s+/', trim($line));
        if (count($p) < 5) continue;
        [$fn,$ln,$un,$em,$pw] = $p;
        $st = $conn->prepare("INSERT IGNORE INTO tblUser (first_name,last_name,username,email,password,user_status) VALUES (?,?,?,?,?,'active')");
        $st->bind_param('sssss',$fn,$ln,$un,$em,$pw);
        if ($st->execute()) $n++;
        $st->close();
    }
    $msgs[] = ['s',"Step 3: $n user(s) loaded from userData.txt."];
}
$conn->close();
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>createTable — Pastimes</title>
<style>body{font-family:sans-serif;background:#f0f0f5;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem}.card{background:#fff;border-radius:1.25rem;padding:2rem;max-width:560px;width:100%;box-shadow:0 16px 48px rgba(0,0,0,.1)}.msg{padding:.55rem .875rem;border-radius:.5rem;margin-bottom:.5rem;font-size:.83rem}.s{background:#dcfce7;color:#166534}.e{background:#fee2e2;color:#991b1b}.btn{display:inline-flex;align-items:center;gap:.4rem;margin-top:1.5rem;padding:.6rem 1.25rem;background:#111;color:#fff;border-radius:9999px;text-decoration:none;font-size:.83rem;margin-right:.5rem}</style>
</head><body><div class="card">
<h1 style="font-size:1.3rem;margin-bottom:1.25rem;">Create &amp; Load tblUser</h1>
<?php foreach($msgs as [$t,$m]): ?>
<div class="msg <?= $t ?>"><?= $t==='s'?'&#10003;':'&#10007;' ?> <?= htmlspecialchars($m) ?></div>
<?php endforeach ?>
<a class="btn" href="index.php">Back to site</a>
<a class="btn" href="loadClothingStore.php">Run loadClothingStore</a>
</div></body></html>
