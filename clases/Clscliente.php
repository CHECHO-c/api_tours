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
            $stmt = $this->pdo->query("SELECT * FROM clientes");
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
            $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE documento = ?");
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
        $documento = (int)($data['telefono'] ?? 0);
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $email = htmlspecialchars($data['email'] ?? '');
        try {
            $stmt = $this->pdo->prepare("INSERT INTO clientes VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$documento, $nombres, $apellidos, $telefono, $email]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    public function update($documento, $data): bool {
        if (!is_numeric($documento)) return false;
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $email = htmlspecialchars($data['email'] ?? '');
        try {
            $stmt = $this->pdo->prepare("UPDATE clientes SET nombres = ?, apellidos = ?, telefono = ?, email = ? WHERE documento = ?");
            return $stmt->execute([$nombres, $apellidos, $telefono, $email, $documento]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    public function delete($nroDocumento): bool {
        if (!is_numeric($nroDocumento)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE documento = ?");
            $stmt->execute([$nroDocumento]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if($cliente){
                $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE documento = ?");
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