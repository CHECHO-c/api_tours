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
            $stmt = $this->pdo->query("SELECT reservas.fecha_reserva, clientes.nombres, tours.nombre, tours.descripcion, tours.ciudad, tours.precio, tours_has_reservas.cantidad_personas,reservas.total FROM tours_has_reservas JOIN reservas ON reservas.id = tours_has_reservas.reserva_id JOIN tours ON tours.id = tours_has_reservas.tour_id JOIN clientes ON clientes.documento = reservas.cliente_documento WHERE reservas.estado = 'Creada'");
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
            $stmt = $this->pdo->prepare("SELECT reservas.fecha_reserva, clientes.nombres, tours.nombre, tours.descripcion, tours.ciudad, tours.precio, tours_has_reservas.cantidad_personas,reservas.total FROM tours_has_reservas JOIN reservas ON reservas.id = tours_has_reservas.reserva_id JOIN tours ON tours.id = tours_has_reservas.tour_id JOIN clientes ON clientes.documento = reservas.cliente_documento WHERE reservas.id = ? AND reservas.estado = 'Creada'");
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
        $idTour = (int)($data['idTour'] ?? 0);
        $cantidadPersonas = (int)($data['cantidadPersonas'] ?? 0);
        $clienteDocumento = (int)($data['clienteDocumento'] ?? 0);
        try {
            $stmtValidarTour = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmtValidarTour->execute([$idTour]);
            $tour = $stmtValidarTour->fetch(PDO::FETCH_ASSOC);
            if(!$tour){
              return ['success' => false, 'error' => 'El Tour no existe'];
            }
            $stmtValidarCliente = $this->pdo->prepare("SELECT * FROM clientes WHERE documento = ?");
            $stmtValidarCliente->execute([$clienteDocumento]);
            $cliente = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);
            if(!$cliente){
                return ['success' => false, 'error' => 'El cliente no existe'];
            }
            $cuposDisponibles = $tour["cupos_totales"];
            if($cantidadPersonas <= 0 || $cantidadPersonas > $cuposDisponibles){
                return ['success' => false, 'error' => 'Cantidad de personas no válida o sin cupos disponibles'];
            }
            $stmtReserva = $this->pdo->prepare("INSERT INTO reservas (fecha_reserva, cliente_documento, estado) VALUES (NOW(), ?, 'Creada')");
            $stmtReserva->execute([$clienteDocumento]);
            $idReservaGenerado = $this->pdo->lastInsertId();
            $stmtActualizarReserva = $this->pdo->prepare("UPDATE reservas SET total = ? WHERE id = ?");
            $stmtActualizarReserva->execute([$tour["precio"] * $cantidadPersonas, $idReservaGenerado]);
            $stmtTours_has_reservas = $this->pdo->prepare("INSERT INTO tours_has_reservas VALUES (?, ?, ?)");
            $stmtTours_has_reservas->execute([$idReservaGenerado, $idTour, $cantidadPersonas]);
            $stmtActualizarCantidadTour = $this->pdo->prepare("UPDATE tours SET cupos_totales = ? WHERE id = ?");
            $stmtActualizarCantidadTour->execute([$cuposDisponibles - $cantidadPersonas, $idTour]);
            return ['success' => true, 'message' => 'Reserva creada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos', 'details' => $e->getMessage()];
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