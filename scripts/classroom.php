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

    // Requête pour inclure les ordinateurs
    $stmt = $conn->prepare("
        SELECT c.*, COALESCE(json_agg(co.*) FILTER (WHERE co.id IS NOT NULL), '[]') AS computers
        FROM classrooms c
        LEFT JOIN computers co ON c.id = co.classroom_id
        WHERE c.user_id = :user_id
        GROUP BY c.id
    ");
    $stmt->execute(['user_id' => $user_id]);

    $salles = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $salles[] = [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'batiment' => $row['batiment'],
            'etage' => $row['etage'],
            'tables' => $row['tables'],
            'chaises' => $row['chaises'],
            'prises' => $row['prises'],
            'etat' => $row['etat'],
            'problemes' => $row['problemes'],
            'disponible' => $row['disponible'],
            'computers' => json_decode($row['computers'], true) // Convertir le JSON en tableau PHP
        ];
    }

    echo json_encode(['success' => true, 'salles' => $salles], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des salles', 'error' => $e->getMessage()]);
}
?>