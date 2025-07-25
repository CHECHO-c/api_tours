<?php
require_once './index.php';
header('Content-Type: application/json');
if (!isset($pdo)) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
    exit();
}
$guia = new Guia($pdo);
switch ($method) {
  case 'GET':
    $identificacion = $id ?? null;
    if(empty($identificacion)){
        try {
            $guias = $guia->getAll();
            if ($guias && count($guias) > 0) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $guias]);
            } else {
                http_response_code(204);
                echo json_encode(['success' => true, 'data' => []]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener los guias',
                'details' => $e->getMessage()
            ]);
        }
    break;
    }elseif(!$identificacion || !is_numeric($identificacion)){
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inv 1lido o no proporcionado']);
        exit();
    }else{
        try {
            $guias = $guia->getById($identificacion);
            if ($guias) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $guias]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Guia no encontrado']);
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
        echo json_encode(['success' => false, 'error' => 'Datos JSON inv 1lidos o vac 1os aca']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
        exit();
    }
    try {
        $success = $guia->create($data);
        if ($success) {
            http_response_code(201); 
            echo json_encode(['success' => true, 'message' => 'Guia creado correctamente']);
        } else {
            http_response_code(400); 
            echo json_encode(['success' => false, 'error' => 'No se pudo crear el guia']);
        }
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'PUT':
    $identificacion = $id ?? null;
    if (!$identificacion || !is_numeric($identificacion)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inv 1lido o no proporcionado']);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos JSON inv 1lidos o vac 1os']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexi 1n a base de datos no disponible']);
        exit();
    }
    try {
        $success = $guia->update($identificacion, $data);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Guia actualizado correctamente']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el guia. Verifica los datos.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  case 'DELETE':
    $identificacion = $id ?? null;
    if (!$identificacion || !is_numeric($identificacion)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inv 1lido o no proporcionado']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexi 1n a base de datos no disponible']);
        exit();
    }
    try {
        $success = $guia->delete($identificacion);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Guia eliminado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Guia no encontrado o ya eliminado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['error' => 'M 1todo no permitido']);
}
?>
