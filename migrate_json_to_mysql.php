<?php
require __DIR__ . '/config.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this migration from the command line.\n");
}

$pdo = db();
$usersAdded = 0;
$ordersAdded = 0;
$pdo->beginTransaction();

try {
    $findUser = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $insertUser = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    foreach (load_json(USERS_FILE) as $email => $account) {
        $findUser->execute([$email]);
        if (!$findUser->fetchColumn()) {
            $insertUser->execute([$account['name'], $email, $account['pass']]);
            $usersAdded++;
        }
    }

    $findOrder = $pdo->prepare('SELECT id FROM orders WHERE order_code = ?');
    $findUser->closeCursor();
    $findUserByEmail = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $insertOrder = $pdo->prepare('INSERT INTO orders (order_code, ordered_at, user_id, recipient_name, phone, address, payment_method, notes, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    foreach (load_json(ORDERS_FILE) as $order) {
        $findOrder->execute([$order['kode']]);
        if ($findOrder->fetchColumn()) continue;

        $findUserByEmail->execute([$order['akun'] ?? '']);
        $userId = $findUserByEmail->fetchColumn() ?: null;
        $insertOrder->execute([
            $order['kode'], $order['waktu'], $userId, $order['nama'], $order['hp'], $order['alamat'],
            $order['bayar'], $order['catatan'] ?? '', $order['total'], $order['status'],
        ]);
        $orderId = $pdo->lastInsertId();
        foreach ($order['items'] as $item) {
            $insertItem->execute([
                $orderId, isset($item['id']) ? (int)$item['id'] : null, $item['name'], $item['qty'], $item['price'], $item['sub'],
            ]);
        }
        $ordersAdded++;
    }

    $pdo->commit();
    echo "Migration selesai. Akun ditambahkan: {$usersAdded}; pesanan ditambahkan: {$ordersAdded}.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Migration gagal: {$e->getMessage()}\n");
    exit(1);
}