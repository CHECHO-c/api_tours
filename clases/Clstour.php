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
            $stmt = $this->pdo->query("SELECT * FROM tarea");
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
            $stmt = $this->pdo->prepare("SELECT * FROM tarea WHERE idTarea = ?");
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
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $prioridad = htmlspecialchars($data['prioridad'] ?? '');
        $idTarea = (int)($data['idTarea'] ?? 0);
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tarea (descripcion_tarea, prioridad, idTarea) VALUES (?, ?, ?)");
            return $stmt->execute([$descripcion, $prioridad, $idTarea]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    public function update($id, $data): bool {
        if (!is_numeric($id)) return false;
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $prioridad = htmlspecialchars($data['prioridad'] ?? '');
        try {
            $stmt = $this->pdo->prepare("UPDATE tarea SET descripcion_tarea = ?, prioridad = ? WHERE idTarea = ?");
            return $stmt->execute([$descripcion, $prioridad,$id]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }
    public function delete($id): bool {
        if (!is_numeric($id)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tarea WHERE idTarea = ?");
            $stmt->execute([$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            if($tour)
            {
                $stmt = $this->pdo->prepare("DELETE FROM tarea WHERE idTarea = ?");
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