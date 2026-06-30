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

$app->run();