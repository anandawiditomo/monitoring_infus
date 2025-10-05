<?php
// PASTIKAN TIDAK ADA SPASI ATAU BARIS KOSONG SEBELUM TAG INI
ini_set('display_errors', 1);
error_reporting(E_ALL); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'Database.php'; 
require_once 'MasterInfusController.php'; // HANYA MEMUAT MASTER CONTROLLER

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("status" => "error", "message" => "Database connection failed."));
    exit();
}

$controller = new MasterInfusController($db); // HANYA SATU CONTROLLER
$request_method = $_SERVER["REQUEST_METHOD"];
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = array_filter(explode('/', $uri_path));
$endpoint = end($uri_segments);


// --- Routing Master Infus ---
if ($request_method == 'GET' && $endpoint == 'master_list') {
    $data = $controller->getInfusMasterListDetailed();
    http_response_code(200);
    echo json_encode(array("status" => "success", "data" => $data));
    exit();
}
elseif ($request_method == 'GET' && $endpoint == 'master_get') {
    $kd_obat_infus = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$kd_obat_infus) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Missing ID."));
        exit();
    }
    $data = $controller->getInfusMasterById($kd_obat_infus);
    if ($data) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "data" => $data));
    } else {
        http_response_code(404);
        echo json_encode(array("status" => "error", "message" => "Item not found."));
    }
    exit();
}
elseif ($request_method == 'POST' && $endpoint == 'master_add') {
    $data = json_decode(file_get_contents("php://input"), true); 
    $result = $controller->addInfusMaster($data);
    if ($result === 'DUPLICATE_ENTRY') {
        http_response_code(409);
        echo json_encode(array("status" => "error", "message" => "Gagal: Kode Obat Infus sudah ada."));
    } elseif ($result) {
        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Master Infus berhasil ditambahkan."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal menambahkan master infus."));
    }
    exit();
}
elseif ($request_method == 'POST' && $endpoint == 'master_update') {
    $data = json_decode(file_get_contents("php://input"), true); 
    if ($controller->updateInfusMaster($data)) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Master Infus berhasil diupdate."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal mengupdate master infus."));
    }
    exit();
}
elseif ($request_method == 'POST' && $endpoint == 'master_delete') {
    $data = json_decode(file_get_contents("php://input"), true); 
    if (empty($data['kd_obat_infus'])) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Missing ID."));
        exit();
    }
    if ($controller->deleteInfusMaster($data['kd_obat_infus'])) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Master Infus berhasil dihapus."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal menghapus master infus."));
    }
    exit();
}
else {
    http_response_code(404);
    echo json_encode(array("status" => "error", "message" => "Endpoint master not found."));
}