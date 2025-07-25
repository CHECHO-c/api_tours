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
class Guia{
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM area");
            $guias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($guias){
                return $guias;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }
    public function getById($identificacion): ?array {
        if (!is_numeric($identificacion)) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM area WHERE idArea = ?");
            $stmt->execute([$identificacion]);
            $guia = $stmt->fetch(PDO::FETCH_ASSOC);
            if($guia){
                return $guia;
            }else{
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return null;
        }
    }
    public function create($data): bool {
        $identificacion = (int)($data['idArea'] ?? 0);
        $nombres = htmlspecialchars($data['nombre'] ?? '');
        $apellidos = htmlspecialchars($data['descripcion'] ?? '');

        try {
            $stmt = $this->pdo->prepare("INSERT INTO area VALUES (?, ?, ?)");
            return $stmt->execute([$identificacion, $nombres, $apellidos]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    public function update($identificacion, $data): bool {
        if (!is_numeric($identificacion)) return false;
        $nombres = htmlspecialchars($data['nombre'] ?? '');
        $apellidos = htmlspecialchars($data['descripcion'] ?? '');
        
        try {
            $stmt = $this->pdo->prepare("UPDATE area SET nombre_area = ?, descripcion_area = ?  WHERE idArea  = ?");
            return $stmt->execute([$nombres, $apellidos,  $identificacion]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    public function delete($identificacion): bool {
        if (!is_numeric($identificacion)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM area WHERE idArea = ?");
            $stmt->execute([$identificacion]);
            $guia = $stmt->fetch(PDO::FETCH_ASSOC);
            if($guia)
            {
                $stmt = $this->pdo->prepare("DELETE FROM area WHERE idArea = ?");
                return $stmt->execute([$identificacion]);
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