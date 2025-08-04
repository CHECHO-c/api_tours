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
        $clienteDocumento = (int)($data['documento'] ?? 0);
        $idTarea = (int) ($data["idTarea"] ?? 0);
        $fechaAsignacion = date("Y-m-d H:i:s");
        $fechaEntrega = $data["fechaEntrega"] ?? date("Y-m-d H:i:s")    ;
        $estado = (int)$data["idEstado"];
        try {

            $verificarDocumento = $this->pdo->prepare("SELECT * FROM empleado WHERE documento = ?");
            $verificarDocumento->execute([$clienteDocumento]);
            $cantidadDocumentos = $verificarDocumento->rowCount();

            if($cantidadDocumentos ==0){
                return ['success'=>false, "error"=>"EL documento no existe"];
            }

            $verificarTarea = $this->pdo->prepare("SELECT * FROM tarea WHERE idTarea = ?");
            $verificarTarea->execute([$idTarea]);
            $cantidadTarea = $verificarTarea->rowCount();

            if($cantidadTarea==0){
                return ['success'=>false, "error"=>"La tarea no existe"];
            }

            $consulta = "INSERT INTO asignaciones (documento_empleado,idTarea,fecha_asignacion,fecha_entrega,estado) values (?,?,?,?,?)";
            $stmtCrear = $this->pdo->prepare($consulta);
            $stmtCrear->execute([$clienteDocumento,$idTarea,$fechaAsignacion,$fechaEntrega,$estado]);
            return ['success' => true, 'message' => 'Reserva creada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos culo', 'details' => $e->getMessage()];
        }
    }
    public function update($idAsignacion, $data): array {
        if (!is_numeric($idAsignacion)) return ['success' => false, 'error' => 'El ID no es valido'];
            
            
            $fechaEntrega = $data["fechaEntrega"] ?? date("Y-m-d H:i:s")    ;
            $idEstado = (int)$data["idEstado"];
        try {
            $stmtValidarCliente = $this->pdo->prepare("SELECT * FROM asignaciones WHERE idAsignaciones = ?");
            $stmtValidarCliente->execute([$idAsignacion]);
            $cliente = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);
            if(!$cliente){
                return ['success' => false, 'error' => 'la asignacion no existe'];
            }
            $stmtActualizarReserva = $this->pdo->prepare("UPDATE asignaciones SET fecha_entrega = ?, idEstado=? WHERE id = ?");
            $stmtActualizarReserva->execute([$fechaEntrega,$idEstado ,$idAsignacion]);
            return ['success' => true, 'message' => 'asignacion actualizada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos', 'details' => $e->getMessage()];
        }
    }
    public function delete($idAsignacion): bool {
        if (!is_numeric( $idAsignacion)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM asignaciones WHERE idAsignaciones = ? AND idEstado = 1 ");
            $stmt->execute([$idAsignacion]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            if($reserva)
            {
                $stmt = $this->pdo->prepare("UPDATE asignaciones SET idEstado = 0 WHERE id = ?");
                return $stmt->execute([$idAsignacion]);
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