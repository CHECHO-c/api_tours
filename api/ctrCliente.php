<?php
require_once './index.php';
header('Content-Type: application/json');
if (!isset($pdo)) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor. Por favor, intente más tarde.']);
    exit();
}
$cliente = new Cliente($pdo);
switch ($method) {
  case 'GET':
    $documento = $id ?? null;
    if(empty($documento)){
        $clientes = $cliente->getAll(); // antes: obtenerClientes
        if ($clientes && count($clientes) > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $clientes]);
        } else {
            http_response_code(204);
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No hay clientes registrados en el sistema.']);
        }
    } else {
        $clientes = $cliente->getById($documento); // antes: obtenerClientesNumeroDocumento
        if ($clientes) {
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $clientes]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No se encontró ningún cliente con el ID proporcionado.']);
        }
    }
    break;
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
        $success = $cliente->create($data); // antes: crearCliente
        if ($success) {
            http_response_code(201); 
            echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente']);
        } else {
            http_response_code(400); 
            echo json_encode(['success' => false, 'error' => 'No se pudo crear el cliente']);
        }
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor. Por favor, intente más tarde.'
        ]);
    }
    break;
  case 'PUT':
    $documento = $id ?? null;
    if (!$documento || !is_numeric($documento)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El ID proporcionado no es válido. Debe ser un número.']);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos o faltantes.']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexion a base de datos no disponible']);
        exit();
    }
    try {
        $success = $cliente->update($documento, $data); // antes: actualizarCliente
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el cliente. Verifica los datos enviados.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor. Por favor, intente más tarde.'
        ]);
    }
    break;
  case 'DELETE':
    $documento = $id ?? null;
    if (!$documento || !is_numeric($documento)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos o faltantes.']);
        exit();
    }
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexion a base de datos no disponible']);
        exit();
    }
    try {
        $success = $cliente->delete($documento); // antes: eliminarCliente
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No se encontró el cliente a eliminar.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor. Por favor, intente más tarde.'
        ]);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'El método HTTP solicitado no está permitido para este recurso.']);
}
?>
