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

$app->run();