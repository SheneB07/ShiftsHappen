<?php
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/page_grid.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$pageId = (int) ($_POST['page_id'] ?? 0);
$page = getPageForBuilder($con, $pageId);

if (!$page) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Pagina niet gevonden.']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'save_page') {
    $payload = json_decode($_POST['payload'] ?? '', true);

    if (!is_array($payload)) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige data.']);
        exit();
    }

    $result = saveEntirePageLayout($con, $pageId, $payload, $_FILES);
    echo json_encode($result);
    exit();
}

if ($action === 'add_row') {
    $columnType = (int) ($_POST['columnType'] ?? 1);
    $rowId = addGridRow($con, $pageId, $columnType);
    $rows = getPageGridRows($con, $pageId);
    $newRow = null;

    foreach ($rows as $row) {
        if ((int) $row['id'] === $rowId) {
            $newRow = $row;
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Rij toegevoegd.',
        'row' => $newRow,
        'rows' => $rows,
    ]);
    exit();
}

if ($action === 'delete_row') {
    $rowId = (int) ($_POST['row_id'] ?? 0);
    deleteGridRow($con, $rowId, $pageId);

    echo json_encode([
        'success' => true,
        'message' => 'Rij verwijderd.',
        'rows' => getPageGridRows($con, $pageId),
    ]);
    exit();
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Onbekende actie.']);
