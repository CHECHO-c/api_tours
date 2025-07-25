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
                echo json_encode(['success' => true, 'data' => [], 'message' => 'No hay guías registradas en el sistema.']);
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
        echo json_encode(['success' => false, 'error' => 'El ID proporcionado no es válido. Debe ser un número.']);
        exit();
    }else{
        try {
            $guias = $guia->getById($identificacion);
            if ($guias) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $guias]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'No se encontró ningún guía con el ID proporcionado.']);
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
        echo json_encode(['success' => false, 'error' => 'El formato de los datos enviados no es válido. Por favor, envía un JSON válido.']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'No se pudo establecer conexión con la base de datos. Intenta más tarde.']);
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
        echo json_encode(['success' => false, 'error' => 'El ID proporcionado no es válido. Debe ser un número.']);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos JSON invalidos o vacios']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexion a base de datos no disponible']);
        exit();
    }
    try {
        $success = $guia->update($identificacion, $data);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Guía actualizada correctamente.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la guía. Verifica los datos enviados.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;//zxd
  case 'DELETE':
    $identificacion = $id ?? null;
    if (!$identificacion || !is_numeric($identificacion)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID invalido o no proporcionado']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexion a base de datos no disponible']);
        exit();
    }
    try {
        $success = $guia->delete($identificacion);
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Guía eliminada correctamente.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No se encontró la guía a eliminar.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'El método HTTP solicitado no está permitido para este recurso.']);
}
?>
