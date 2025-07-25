<?php
require_once './index.php';
header('Content-Type: application/json');
if (!isset($pdo)) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
    exit();
}
$tour = new Tour($pdo);
switch ($method) {
  case 'GET':
    $idTour = $id ?? null;
    if(empty($idTour)){
        $tours = $tour->getAll(); // antes: obtenerTours
        if ($tours && count($tours) > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $tours]);
        } else {
            http_response_code(204);
            echo json_encode(['success' => true, 'data' => []]);
        }
    } else {
        $tours = $tour->getById($idTour); // antes: obtenerTourID
        if ($tours) {
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $tours]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Tour no encontrado']);
        }
    }
    break;
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
        $success = $tour->create($data); // antes: crearTour
        if ($success) {
            http_response_code(201); 
            echo json_encode(['success' => true, 'message' => 'Tour creado correctamente']);
        } else {
            http_response_code(400); 
            echo json_encode(['success' => false, 'error' => 'No se pudo crear el tour']);
        }
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'PUT':
    $idTour = $id ?? null;
    if (!$idTour || !is_numeric($idTour)) {
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
        $success = $tour->update($idTour, $data); // antes: actualizarTour
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Tour actualizado correctamente']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el tour. Verifica los datos.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'DELETE':
    $idTour = $id ?? null;
    if (!$idTour || !is_numeric($idTour)) {
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
        $success = $tour->delete($idTour); // antes: eliminarTour
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Tour eliminado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Tour no encontrado o ya eliminado']);
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
