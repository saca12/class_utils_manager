<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$host = "localhost";
$port = "5432";
$dbname = "esi_class_manager";
$user = "postgres";
$password = "16269758h";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $salle_id = $data['salle_id'] ?? null;
    $date = $data['date'] ?? null;
    $heure_debut = $data['heure_debut'] ?? null;
    $heure_fin = $data['heure_fin'] ?? null;
    $type_activite = $data['type_activite'] ?? 'TP';

    if (!$salle_id || !$date || !$heure_debut || !$heure_fin) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tp (user_id, salle_id, type_activite) VALUES (:user_id, :salle_id, :type_activite) RETURNING id");
    $stmt->execute(['user_id' => $user_id, 'salle_id' => $salle_id, 'type_activite' => $type_activite]);
    $tp_id = $stmt->fetchColumn();

    $stmt = $conn->prepare("INSERT INTO tp_schedules (tp_id, date, heure_debut, heure_fin) VALUES (:tp_id, :date, :heure_debut, :heure_fin)");
    $stmt->execute(['tp_id' => $tp_id, 'date' => $date, 'heure_debut' => $heure_debut, 'heure_fin' => $heure_fin]);

    echo json_encode(['success' => true, 'message' => 'Réservation enregistrée']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>