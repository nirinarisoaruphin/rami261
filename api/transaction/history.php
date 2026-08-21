<?php
// api/transaction/history.php - API historique des transactions
require_once '../../includes/config.php';
require_once '../../includes/TransactionManager.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, [], 'Non authentifié');
}

$userId = $_SESSION['user_id'];
$transactionManager = new TransactionManager();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : null;

$history = $transactionManager->getHistory($userId, $limit, $offset, $type);

if ($history['success']) {
    jsonResponse(true, $history);
} else {
    jsonResponse(false, [], $history['error']);
}
?>