<?php
require_once 'baseDeDatos.php';
require_once 'clases/Clscliente.php';
require_once 'clases/Clsguia.php';
require_once 'clases/Clstour.php';
require_once 'clases/Clsreserva.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

$cliente = new Cliente($pdo);
$guia = new Guia($pdo);
$tour = new Tour($pdo);
$reserva = new Reserva($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

$resource = $uri[1] ?? null;
$id = $uri[2] ?? null;

switch ($resource) {
    case 'empleado':
        require 'api/ctrCliente.php';
        break;
    case 'area':
        require 'api/ctrGuia.php';
        break;
    case 'tarea':
        require 'api/ctrTour.php';
        break;
    case 'asignacion':
        require 'api/ctrReserva.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'endpoint incorrecto']);
        break;
};

?>