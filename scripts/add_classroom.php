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

    if (empty($data['nom']) || empty($data['batiment']) || !isset($data['tables']) || !isset($data['chaises']) || !isset($data['prises'])) {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO classrooms (user_id, nom, batiment, etage, tables, chaises, prises, etat, problemes, disponible)
        VALUES (:user_id, :nom, :batiment, :etage, :tables, :chaises, :prises, :etat, :problemes, :disponible)
        RETURNING id
    ");
    $stmt->execute([
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

    $classroom_id = $stmt->fetchColumn();

    if (!empty($data['consommables'])) {
        $stmt = $conn->prepare("
            INSERT INTO consommables (classroom_id, type, quantite, commentaires)
            VALUES (:classroom_id, :type, :quantite, :commentaires)
        ");
        foreach ($data['consommables'] as $consommable) {
            if ($consommable['quantite'] > 0) {
                $stmt->execute([
                    'classroom_id' => $classroom_id,
                    'type' => $consommable['type'],
                    'quantite' => $consommable['quantite'],
                    'commentaires' => $consommable['commentaires'] ?? ''
                ]);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Salle enregistrée avec succès', 'id' => $classroom_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>