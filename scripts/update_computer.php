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
    $user_id = $_SESSION['user_id'];

    if (empty($data['computer_id']) || empty($data['etat'])) {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE computers
        SET etat = :etat, ports = :ports, peripheriques = :peripheriques, logiciels = :logiciels
        WHERE id = :computer_id
    ");
    $stmt->execute([
        'computer_id' => $data['computer_id'],
        'etat' => $data['etat'],
        'ports' => $data['ports'],
        'peripheriques' => $data['peripheriques'],
        'logiciels' => $data['logiciels']
    ]);

    echo json_encode(['success' => true, 'message' => 'Ordinateur enregistré avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>