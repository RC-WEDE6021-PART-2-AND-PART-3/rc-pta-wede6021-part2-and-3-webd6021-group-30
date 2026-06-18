<?php

if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool  { return !empty($_SESSION['user_id']); }
function isAdmin(): bool     { return !empty($_SESSION['admin_id']); }
function authName(): string  { return htmlspecialchars($_SESSION['user_name'] ?? ''); }
function redirect(string $u): never { header("Location: $u"); exit; }

function getCart(): array  { return $_SESSION['cart'] ?? []; }
function cartCount(): int  { return array_sum(array_column(getCart(), 'qty')); }

function addToCart(array $item): void {
    $id = (int)$item['clothes_id'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']++;
    } else {
        $_SESSION['cart'][$id] = [
            'clothes_id'  => $id,
            'brand'       => $item['brand'],
            'item_name'   => $item['item_name'],
            'sell_price'  => $item['sell_price'],
            'image_file'  => $item['image_file'],
            'size'        => $item['size'],
            'qty'         => 1,
        ];
    }
}

function cartTotal(): float {
    return array_sum(array_map(fn($i) => $i['sell_price'] * $i['qty'], getCart()));
}
