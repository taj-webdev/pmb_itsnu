<?php
// dashboard/mahasiswa/api_notifikasi.php
session_start();
require_once("../../config/database.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}
$user_id = intval($_SESSION['user']['id']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    $q = query("SELECT id, pesan, tipe, status_baca, created_at FROM notifikasi
                WHERE user_id = $user_id
                ORDER BY created_at DESC
                LIMIT $limit");
    $rows = [];
    while ($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['data' => $rows]);
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_all') {
        query("UPDATE notifikasi SET status_baca='dibaca' WHERE user_id = $user_id AND status_baca='belum_dibaca'");
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($action === 'mark_read') {
        $ids = $_POST['ids'] ?? '';
        $clean = preg_replace('/[^0-9,]/', '', $ids);
        if ($clean) {
            query("UPDATE notifikasi SET status_baca='dibaca' WHERE id IN ($clean) AND user_id = $user_id");
        }
        echo json_encode(['ok' => true]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['error' => 'invalid_action']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method_not_allowed']);
