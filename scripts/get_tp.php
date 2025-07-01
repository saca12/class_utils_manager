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

    $id = $_GET['id'] ?? null;
    if (!isset($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }

    // Récupérer le TP
    $stmt = $conn->prepare("SELECT id, tp_nom, matiere, salle_id, logiciels FROM tp WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $tp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tp) {
        echo json_encode(['success' => false, 'message' => 'TP non trouvé']);
        exit;
    }

    // Récupérer les horaires
    $stmt = $conn->prepare("SELECT date, heure_debut, heure_fin FROM tp_schedules WHERE tp_id = :tp_id");
    $stmt->execute(['tp_id' => $id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'tp' => $tp, 'schedules' => $schedules]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}

$conn = null;
?>