<?php

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'message' => 'CampusEats API is running'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/vendors', function ($request, $response) {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM vendors");
    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/menu-items', function ($request, $response) {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM menu_items");
    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/promotions', function ($request, $response) {
    $db = getDB();

    $stmt = $db->query("
        SELECT 
            p.*,
            v.name AS vendor_name,
            v.location AS vendor_location
        FROM promotions p
        JOIN vendors v ON p.vendor_id = v.vendor_id
        ORDER BY p.created_at DESC
    ");

    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/notifications', function ($request, $response) {
    $db = getDB();

    $stmt = $db->query("
        SELECT *
        FROM notifications
        ORDER BY created_at DESC
    ");

    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/api/notifications/{id}/read', function ($request, $response, $args) {
    $db = getDB();
    $notificationId = $args['id'];

    $stmt = $db->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE notification_id = ?
    ");

    $stmt->execute([$notificationId]);

    $response->getBody()->write(json_encode([
        'message' => 'Notification marked as read'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/orders', function ($request, $response) {
    $db = getDB();

    $stmt = $db->query("
        SELECT 
            o.*,
            u.name AS customer_name,
            v.name AS vendor_name
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN vendors v ON o.vendor_id = v.vendor_id
        ORDER BY o.created_at DESC
    ");

    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/orders', function ($request, $response) {
    $db = getDB();
    $data = $request->getParsedBody();

    $userId = $data['user_id'] ?? null;
    $vendorId = $data['vendor_id'] ?? null;
    $subtotal = $data['subtotal'] ?? 0;
    $serviceFee = $data['service_fee'] ?? 0;
    $taxAmount = $data['tax_amount'] ?? 0;
    $total = $data['total'] ?? 0;
    $pickupAt = $data['pickup_at'] ?? null;
    $items = $data['items'] ?? [];

    if (!$userId || !$vendorId || empty($items)) {
        $response->getBody()->write(json_encode([
            'error' => 'user_id, vendor_id and items are required'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    if ($pickupAt === 'ASAP - 15 mins') {
        $pickupAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    } elseif ($pickupAt && !preg_match('/^\d{4}-\d{2}-\d{2}/', $pickupAt)) {
        $pickupAt = date('Y-m-d') . ' ' . date('H:i:s', strtotime($pickupAt));
    }

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO orders (
                user_id,
                vendor_id,
                status,
                subtotal,
                service_fee,
                tax_amount,
                total,
                pickup_at,
                payment_method,
                payment_label,
                payment_status,
                note
            )
            VALUES (?, ?, 'placed', ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $vendorId,
            $subtotal,
            $serviceFee,
            $taxAmount,
            $total,
            $pickupAt,
            $data['payment_method'] ?? null,
            $data['payment_label'] ?? null,
            $data['payment_status'] ?? null,
            $data['note'] ?? null
        ]);

        $orderId = $db->lastInsertId();

        $itemStmt = $db->prepare("
            INSERT INTO order_items (
                order_id,
                menu_item_id,
                quantity,
                unit_price
            )
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $itemStmt->execute([
                $orderId,
                $item['menu_item_id'],
                $item['quantity'],
                $item['unit_price']
            ]);
        }

        $db->commit();

        $orderStmt = $db->prepare("
            SELECT 
                o.*,
                u.name AS customer_name,
                v.name AS vendor_name
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            JOIN vendors v ON o.vendor_id = v.vendor_id
            WHERE o.order_id = ?
        ");

        $orderStmt->execute([$orderId]);
        $newOrder = $orderStmt->fetch();

        $response->getBody()->write(json_encode($newOrder));

        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $db->rollBack();

        $response->getBody()->write(json_encode([
            'error' => 'Unable to create order',
            'details' => $e->getMessage()
        ]));

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }
});

$app->put('/api/orders/{id}/status', function ($request, $response, $args) {
    $db = getDB();
    $orderId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $status = $data['status'] ?? null;

    $allowedStatuses = ['placed', 'preparing', 'ready', 'collected', 'cancelled'];

    if (!$status || !in_array($status, $allowedStatuses)) {
        $response->getBody()->write(json_encode([
            'error' => 'Invalid order status.'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $stmt = $db->prepare("
        UPDATE orders
        SET status = ?
        WHERE order_id = ?
    ");

    $stmt->execute([$status, $orderId]);

    $stmt = $db->prepare("
        SELECT 
            o.*,
            u.name AS customer_name,
            v.name AS vendor_name
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN vendors v ON o.vendor_id = v.vendor_id
        WHERE o.order_id = ?
    ");

    $stmt->execute([$orderId]);
    $updatedOrder = $stmt->fetch();

    if (!$updatedOrder) {
        $response->getBody()->write(json_encode([
            'error' => 'Order not found.'
        ]));

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode($updatedOrder));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/order-items', function ($request, $response) {
    $db = getDB();

    $stmt = $db->query("
        SELECT 
            oi.*,
            mi.name,
            mi.image_url
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
        ORDER BY oi.order_item_id ASC
    ");

    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/reviews', function ($request, $response) {
    $db = getDB();

    $stmt = $db->query("
        SELECT 
            r.*,
            u.name AS customer_name,
            v.name AS vendor_name
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        JOIN vendors v ON r.vendor_id = v.vendor_id
        ORDER BY r.created_at DESC
    ");

    $data = $stmt->fetchAll();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/reviews', function ($request, $response) {
    $db = getDB();
    $data = $request->getParsedBody();

    $orderId = $data['order_id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $vendorId = $data['vendor_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? '';

    if (!$orderId || !$userId || !$vendorId || !$rating) {
        $response->getBody()->write(json_encode([
            'error' => 'order_id, user_id, vendor_id, and rating are required'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    if ($rating < 1 || $rating > 5) {
        $response->getBody()->write(json_encode([
            'error' => 'Rating must be between 1 and 5'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $orderStmt = $db->prepare("
        SELECT *
        FROM orders
        WHERE order_id = ? AND user_id = ? AND vendor_id = ?
    ");
    $orderStmt->execute([$orderId, $userId, $vendorId]);
    $order = $orderStmt->fetch();

    if (!$order) {
        $response->getBody()->write(json_encode([
            'error' => 'Order not found'
        ]));

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
    }

    if ($order['status'] !== 'collected') {
        $response->getBody()->write(json_encode([
            'error' => 'Review can only be submitted after the order is collected'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO reviews (
                order_id,
                user_id,
                vendor_id,
                rating,
                comment
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orderId,
            $userId,
            $vendorId,
            $rating,
            $comment
        ]);

        $reviewId = $db->lastInsertId();

        $reviewStmt = $db->prepare("
            SELECT 
                r.*,
                u.name AS customer_name,
                v.name AS vendor_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN vendors v ON r.vendor_id = v.vendor_id
            WHERE r.review_id = ?
        ");
        $reviewStmt->execute([$reviewId]);
        $newReview = $reviewStmt->fetch();

        $response->getBody()->write(json_encode($newReview));
        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            'error' => 'This order has already been reviewed'
        ]));

        return $response
            ->withStatus(409)
            ->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/api/register', function ($request, $response) {
    $db = getDB();
    $data = $request->getParsedBody();

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'customer';

    if (!$name || !$email || !$password) {
        $response->getBody()->write(json_encode([
            'error' => 'Name, email, and password are required'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $checkStmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);

    if ($checkStmt->fetch()) {
        $response->getBody()->write(json_encode([
            'error' => 'Email already exists'
        ]));

        return $response
            ->withStatus(409)
            ->withHeader('Content-Type', 'application/json');
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, status)
        VALUES (?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $name,
        $email,
        $passwordHash,
        $role
    ]);

    $userId = $db->lastInsertId();

    $userStmt = $db->prepare("
        SELECT user_id, name, email, role, status, created_at
        FROM users
        WHERE user_id = ?
    ");
    $userStmt->execute([$userId]);

    $newUser = $userStmt->fetch();

    $response->getBody()->write(json_encode($newUser));

    return $response
        ->withStatus(201)
        ->withHeader('Content-Type', 'application/json');
});

$app->post('/api/login', function ($request, $response) {
    $db = getDB();
    $data = $request->getParsedBody();

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        $response->getBody()->write(json_encode([
            'error' => 'Email and password are required'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $stmt = $db->prepare("
        SELECT 
            u.user_id,
            u.name,
            u.email,
            u.password_hash,
            u.role,
            u.status,
            vu.vendor_id
        FROM users u
        LEFT JOIN vendor_users vu ON u.user_id = vu.user_id
        WHERE u.email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Invalid email or password'
        ]));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    if ($user['status'] !== 'active') {
        $response->getBody()->write(json_encode([
            'error' => 'Account is not active'
        ]));

        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }

    unset($user['password_hash']);

    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();