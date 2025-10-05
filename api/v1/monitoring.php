<?php
// File: monitoring.php (Router Monitoring Infus)
// PASTIKAN TIDAK ADA SPASI ATAU BARIS KOSONG SEBELUM TAG INI
ini_set('display_errors', 1);
error_reporting(E_ALL); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'Database.php'; 
require_once 'InfusController.php'; 

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("status" => "error", "message" => "Database connection failed."));
    exit();
}

$controller = new InfusController($db); 
$request_method = $_SERVER["REQUEST_METHOD"];
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = array_filter(explode('/', $uri_path));
$endpoint = end($uri_segments);
if (strpos($endpoint, 'monitoring.php') !== false) {
    $endpoint = 'list'; 
}


// --- Routing Monitoring ---
if ($request_method == 'GET' && $endpoint == 'list') {
    $filter_unit = isset($_GET['unit']) ? $_GET['unit'] : 'ALL';
    $output = $controller->getInfusDashboardData($filter_unit); // MENDAPATKAN DATA DAN SERVER_NOW
    http_response_code(200);
    // MENGEMBALIKAN DATA DAN SERVER_NOW
    echo json_encode(array("status" => "success", "data" => $output['data'], "server_now" => $output['server_now'])); 
    exit();
} 
elseif ($request_method == 'POST' && $endpoint == 'add_log') {
    $data = json_decode(file_get_contents("php://input"), true); 
    if ($controller->addInfusLog($data)) {
        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Log monitoring berhasil dicatat."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal mencatat log."));
    }
    exit();
}
elseif ($request_method == 'POST' && $endpoint == 'add_order') {
    $data = json_decode(file_get_contents("php://input"), true); 
    if ($controller->addInfusOrder($data)) {
        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Order infus baru berhasil dicatat."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal mencatat order."));
    }
    exit();
}
elseif ($request_method == 'POST' && $endpoint == 'end_order') {
    $data = json_decode(file_get_contents("php://input"), true); 
    if ($controller->endInfusOrder($data)) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Order berhasil dihentikan."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal menghentikan order."));
    }
    exit();
}
elseif ($request_method == 'GET' && $endpoint == 'list_infus') {
    $data = $controller->getInfusMasterList();
    http_response_code(200);
    echo json_encode($data);
    exit();
}
else {
    http_response_code(404);
    echo json_encode(array("status" => "error", "message" => "Endpoint monitoring not found."));
}