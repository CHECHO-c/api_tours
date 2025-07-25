<?php
$allowed_origin = '*';
header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
class Reserva{
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll(): array {
        try {
            $stmt = $this->pdo->query("SELECT  asignaciones.idAsignaciones,empleado.nombre,tarea.descripcion_tarea,asignaciones.fecha_asignacion,asignaciones.fecha_entrega,estado.nombre_estado FROM asignaciones 
JOIN empleado ON empleado.documento=asignaciones.docuemento_empleado
JOIN estado ON estado.idEstado = asignaciones.idEstado
JOIN tarea ON  tarea.idTarea = asignaciones.idTarea");
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($reservas){
                return $reservas;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerReservas: " . $e->getMessage());
            return [];
        }
    }
    public function getById($id): ?array {
        if (!is_numeric($id)) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT  asignaciones.idAsignaciones,empleado.nombre,tarea.descripcion_tarea,asignaciones.fecha_asignacion,asignaciones.fecha_entrega,estado.nombre_estado FROM asignaciones 
JOIN empleado ON empleado.documento=asignaciones.docuemento_empleado
JOIN estado ON estado.idEstado = asignaciones.idEstado
JOIN tarea ON  tarea.idTarea = asignaciones.idTarea WHERE idAsignaciones= ?");
            $stmt->execute([$id]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            if($reserva){
                return $reserva;
            }else{
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerReservaID: " . $e->getMessage());
            return null;
        }
    }
    public function create($data): array {
        $idAsignacion = (int)($data['idAsignacion'] ?? 0);
        $clienteDocumento = (int)($data['documentoCliente'] ?? 0);
        $idTarea = (int) ($data["idTarea"] ?? 0);
        try {

            $verificarDocumento = $this->pdo->prepare("SELECT * FROM empleado WHERE documento = ?");
            $verificarDocumento->execute([$clienteDocumento]);
    



            return ['success' => true, 'message' => 'Reserva creada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos culo', 'details' => $e->getMessage()];
        }
    }
    public function update($idReserva, $data): array {
        if (!is_numeric($idReserva)) return ['success' => false, 'error' => 'El ID no es valido'];
        $clienteDocumento = (int)($data['clienteDocumento'] ?? 0);
        try {
            $stmtValidarCliente = $this->pdo->prepare("SELECT * FROM clientes WHERE documento = ?");
            $stmtValidarCliente->execute([$clienteDocumento]);
            $cliente = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);
            if(!$cliente){
                return ['success' => false, 'error' => 'El cliente no existe'];
            }
            $stmtActualizarReserva = $this->pdo->prepare("UPDATE reservas SET cliente_documento = ? WHERE id = ?");
            $stmtActualizarReserva->execute([$clienteDocumento, $idReserva]);
            return ['success' => true, 'message' => 'Reserva actualizada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos', 'details' => $e->getMessage()];
        }
    }
    public function delete($idReserva): bool {
        if (!is_numeric($idReserva)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reservas WHERE id = ? AND estado = 'Creada'");
            $stmt->execute([$idReserva]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            if($reserva)
            {
                $stmt = $this->pdo->prepare("UPDATE reservas SET estado = 'Eliminado' WHERE id = ?");
                return $stmt->execute([$idReserva]);
            }else{
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }
}
?>