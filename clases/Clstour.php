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
class Tour{
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getAll(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM tours");
            $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($tours){
                return $tours;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerTours: " . $e->getMessage());
            return [];
        }
    }
    public function getById($id): ?array {
        if (!is_numeric($id)) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmt->execute([$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            if($tour){
                return $tour;
            }else{
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerTourID: " . $e->getMessage());
            return null;
        }
    }
    public function create($data): bool {
        $nombre = htmlspecialchars($data['nombre'] ?? '');
        $ciudad = htmlspecialchars($data['ciudad'] ?? '');
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $precio = (double)($data['precio'] ?? 0.00);
        $cuposTotales = (int)($data['cupos_totales'] ?? 0);
        $idGuia = (int)($data['guias_identificacion'] ?? 0);
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tours (nombre, ciudad, descripcion, precio, cupos_totales, guias_identificacion) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$nombre, $ciudad, $descripcion, $precio, $cuposTotales, $idGuia]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    public function update($id, $data): bool {
        if (!is_numeric($id)) return false;
        $nombre = htmlspecialchars($data['nombre'] ?? '');
        $ciudad = htmlspecialchars($data['ciudad'] ?? '');
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $precio = (double)($data['precio'] ?? 0);
        $cuposTotales = (int)($data['cupos_totales'] ?? 0);
        $idGuia = (int)($data['guias_identificacion'] ?? 0);
        try {
            $stmt = $this->pdo->prepare("UPDATE tours SET nombre = ?, ciudad = ?, descripcion = ?, precio = ?, cupos_totales = ?, guias_identificacion = ? WHERE id = ?");
            return $stmt->execute([$nombre, $ciudad, $descripcion, $precio, $cuposTotales, $idGuia, $id]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    public function delete($id): bool {
        if (!is_numeric($id)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmt->execute([$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            if($tour)
            {
                $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id = ?");
                return $stmt->execute([$id]);
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