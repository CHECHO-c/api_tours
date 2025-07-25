<?php
require_once './index.php';
header('Content-Type: application/json');
if (!isset($pdo)) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
    exit();
}
$reserva = new Reserva($pdo);
switch ($method) {
  case 'GET':
    $idReserva = $id ?? null;
    if(empty($idReserva)){
        try {
            $reservas = $reserva->getAll();
            if ($reservas && count($reservas) > 0) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $reservas]);
            } else {
                http_response_code(204);
                echo json_encode(['success' => true, 'data' => []]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener las reservas',
                'details' => $e->getMessage()
            ]);
        }
    break;
    }elseif(!$idReserva || !is_numeric($idReserva)){
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();
    }else{
        try {
            $reservas = $reserva->getById($idReserva);
            if ($reservas) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $reservas]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Reserva no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
        }
        break;
    }
  case 'POST':
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos o vacíos aca']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
        exit();
    }
    try {
        $resultado = $reserva->create($data);
        if ($resultado['success']) {
            http_response_code(201);
        } else {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'PUT':
    $idReserva = $id ?? null;
    if (!$idReserva || !is_numeric($idReserva)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos o vacíos']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexión a base de datos no disponible']);
        exit();
    }
    try {
        $resultado = $reserva->update($idReserva, $data);
        if ($resultado['success']) {
            http_response_code(201);
        } else {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'DELETE':
    $idReserva = $id ?? null;
    if (!$idReserva || !is_numeric($idReserva)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexión a base de datos no disponible']);
        exit();
    }
    try {
        $success = $reserva->delete($idReserva);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Reserva eliminado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Reserva no encontrado o ya eliminado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
