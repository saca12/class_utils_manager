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

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $user_id = $_SESSION['user_id'];

    if (!isset($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }

    // Vérifier si le TP appartient à l'utilisateur
    $stmt = $conn->prepare("SELECT salle_id FROM tp WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $salle_id = $stmt->fetchColumn();
    if ($salle_id === false) {
        echo json_encode(['success' => false, 'message' => 'TP non trouvé ou non autorisé']);
        exit;
    }

    // Supprimer le TP
    $stmt = $conn->prepare("DELETE FROM tp WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        // Mettre à jour la disponibilité de la salle
        $stmt = $conn->prepare("
            UPDATE classrooms
            SET disponible = NOT EXISTS (
                SELECT 1 FROM tp WHERE salle_id = :salle_id
            )
            WHERE id = :salle_id
        ");
        $stmt->execute(['salle_id' => $salle_id]);
        echo json_encode(['success' => true, 'message' => 'TP supprimé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'TP non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}

$conn = null;
?>