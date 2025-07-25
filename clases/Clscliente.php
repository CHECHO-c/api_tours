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
class Cliente{
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM empleado");
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($clientes){
                return $clientes;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage());
            return [];
        }
    }
    public function getById($documento): ?array {
        if (!is_numeric($documento)) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM empelado WHERE documento = ?");
            $stmt->execute([$documento]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if($cliente){
                return $cliente;
            }else{
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerClientesNumeroDocumento: " . $e->getMessage());
            return null;
        }
    }
    public function create($data): bool {
        $documento = (int)($data['documento'] ?? 0);
        $nombres = htmlspecialchars($data['nombre'] ?? '');
        $apellidos = htmlspecialchars($data['apellido'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $idArea = htmlspecialchars($data['idArea'] ?? '');
        try {
            $stmt = $this->pdo->prepare("INSERT INTO empleado VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$documento, $nombres, $apellidos, $telefono, $idArea]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    public function update($documento, $data): bool {
        if (!is_numeric($documento)) return false;
        $nombres = htmlspecialchars($data['nombre'] ?? '');
        $apellidos = htmlspecialchars($data['apellido'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $idArea = htmlspecialchars($data['idArea'] ?? '');
        try {
            $stmt = $this->pdo->prepare("UPDATE empleado SET nombre = ?, apellido = ?, telefono = ?, idArea = ? WHERE documento = ?");
            return $stmt->execute([$nombres, $apellidos, $telefono, $idArea, $documento]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    public function delete($nroDocumento): bool {
        if (!is_numeric($nroDocumento)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM empleado WHERE documento = ?");
            $stmt->execute([$nroDocumento]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if($cliente){
                $stmt = $this->pdo->prepare("DELETE FROM empleado WHERE documento = ?");
                return $stmt->execute([$nroDocumento]);
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