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

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['classroom_id']) || empty($data['marque']) || empty($data['modele']) || empty($data['ram']) || empty($data['os']) || empty($data['etat'])) {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO computers (classroom_id, modele, marque, ram, processeur, carte_reseau, ports, peripheriques, logiciels, disque, os, etat, quantite, user_id)
        VALUES (:classroom_id, :modele, :marque, :ram, :processeur, :carte_reseau, :ports, :peripheriques, :logiciels, :disque, :os, :etat, :quantite, :user_id)
        RETURNING id
    ");
    $stmt->execute([
        'classroom_id' => $data['classroom_id'],
        'modele' => $data['modele'],
        'marque' => $data['marque'],
        'ram' => $data['ram'],
        'processeur' => $data['processeur'] ?? null,
        'carte_reseau' => $data['carte_reseau'] ?? null,
        'ports' => $data['ports'] ?? null,
        'peripheriques' => $data['peripheriques'] ?? null,
        'logiciels' => $data['logiciels'] ?? null,
        'disque' => $data['disque'] ?? null,
        'os' => $data['os'],
        'etat' => $data['etat'],
        'quantite' => $data['quantite'] ?? 1,
        'user_id' => $user_id
    ]);

    $computer_id = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'message' => 'Ordinateur enregistré avec succès', 'id' => $computer_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>