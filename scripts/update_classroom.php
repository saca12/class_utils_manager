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

    $stmt = $conn->prepare("
        UPDATE classrooms 
        SET nom = :nom, batiment = :batiment, etage = :etage, tables = :tables, 
            chaises = :chaises, prises = :prises, etat = :etat, problemes = :problemes, disponible = :disponible
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([
        'id' => $id,
        'user_id' => $user_id,
        'nom' => $data['nom'],
        'batiment' => $data['batiment'],
        'etage' => $data['etage'] ?? 'Non spécifié',
        'tables' => $data['tables'],
        'chaises' => $data['chaises'],
        'prises' => $data['prises'],
        'etat' => $data['etat'] ?? 'bon',
        'problemes' => $data['problemes'] ?? '',
        'disponible' => $data['disponible'] ?? true
    ]);

    echo json_encode(['success' => true, 'message' => 'Salle mise à jour avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>