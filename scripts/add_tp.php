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
    $tp_nom = trim($data['tp_nom'] ?? '');
    $matiere = trim($data['matiere'] ?? '');
    $salle_id = $data['salle_id'] ? str_replace('salle_', '', $data['salle_id']) : null;
    $logiciels = trim($data['logiciels'] ?? '');
    $schedules = $data['schedules'];
    $user_id = $_SESSION['user_id'];

    if (empty($tp_nom) || empty($matiere) || !is_array($schedules) || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Données invalides ou utilisateur non connecté']);
        exit;
    }

    // Vérifier si la salle existe
    $stmt = $conn->prepare("SELECT id FROM classrooms WHERE id = :salle_id");
    $stmt->execute(['salle_id' => $salle_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Salle non trouvée']);
        exit;
    }

    // Insérer le TP
    $stmt = $conn->prepare("
        INSERT INTO tp (user_id, tp_nom, matiere, salle_id, logiciels)
        VALUES (:user_id, :tp_nom, :matiere, :salle_id, :logiciels)
        RETURNING id
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'tp_nom' => $tp_nom,
        'matiere' => $matiere,
        'salle_id' => $salle_id,
        'logiciels' => $logiciels
    ]);
    $tp_id = $stmt->fetchColumn();

    // Insérer les horaires
    $stmt = $conn->prepare("
        INSERT INTO tp_schedules (tp_id, date, heure_debut, heure_fin)
        VALUES (:tp_id, :date, :heure_debut, :heure_fin)
    ");
    foreach ($schedules as $schedule) {
        $stmt->execute([
            'tp_id' => $tp_id,
            'date' => $schedule['date'],
            'heure_debut' => $schedule['heure_debut'],
            'heure_fin' => $schedule['heure_fin']
        ]);
    }

    // Mettre à jour la disponibilité de la salle
    $stmt = $conn->prepare("UPDATE classrooms SET disponible = false WHERE id = :salle_id");
    $stmt->execute(['salle_id' => $salle_id]);

    echo json_encode(['success' => true, 'message' => 'TP ajouté avec succès', 'tp_id' => $tp_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}

$conn = null;
?>