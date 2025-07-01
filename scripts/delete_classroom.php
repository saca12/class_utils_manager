<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$port = "5432";
$dbname = "esi_class_manager";
$user = "postgres";
$password = "16269758h";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de la salle requis']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM classrooms WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Salle supprimée avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Salle non trouvée ou non autorisée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>