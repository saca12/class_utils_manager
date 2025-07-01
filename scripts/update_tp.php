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
    $tp_nom = trim($data['tp_nom'] ?? '');
    $matiere = trim($data['matiere'] ?? '');
    $salle_id = $data['salle_id'] ? str_replace('salle_', '', $data['salle_id']) : null;
    $logiciels = trim($data['logiciels'] ?? '');
    $schedules = $data['schedules'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!isset($id) || !is_numeric($id) || empty($tp_nom) || empty($matiere) || empty($salle_id) || !is_array($schedules) || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Données invalides ou utilisateur non connecté']);
        exit;
    }

    // Vérifier si le TP existe et appartient à l'utilisateur
    $stmt = $conn->prepare("SELECT salle_id FROM tp WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $old_salle_id = $stmt->fetchColumn();
    if ($old_salle_id === false) {
        echo json_encode(['success' => false, 'message' => 'TP non trouvé ou non autorisé']);
        exit;
    }

    // Vérifier si la nouvelle salle existe
    $stmt = $conn->prepare("SELECT id FROM classrooms WHERE id = :salle_id");
    $stmt->execute(['salle_id' => $salle_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Salle non trouvée']);
        exit;
    }

    // Mettre à jour le TP
    $stmt = $conn->prepare("
        UPDATE tp
        SET tp_nom = :tp_nom, matiere = :matiere, salle_id = :salle_id, logiciels = :logiciels, user_id = :user_id
        WHERE id = :id
    ");
    $stmt->execute([
        'id' => $id,
        'tp_nom' => $tp_nom,
        'matiere' => $matiere,
        'salle_id' => $salle_id,
        'logiciels' => $logiciels,
        'user_id' => $user_id
    ]);

    // Supprimer les anciens horaires
    $stmt = $conn->prepare("DELETE FROM tp_schedules WHERE tp_id = :tp_id");
    $stmt->execute(['tp_id' => $id]);

    // Insérer les nouveaux horaires
    $stmt = $conn->prepare("
        INSERT INTO tp_schedules (tp_id, date, heure_debut, heure_fin)
        VALUES (:tp_id, :date, :heure_debut, :heure_fin)
    ");
    foreach ($schedules as $schedule) {
        $stmt->execute([
            'tp_id' => $id,
            'date' => $schedule['date'],
            'heure_debut' => $schedule['heure_debut'],
            'heure_fin' => $schedule['heure_fin']
        ]);
    }

    // Mettre à jour la disponibilité des salles
    $stmt = $conn->prepare("UPDATE classrooms SET disponible = false WHERE id = :salle_id");
    $stmt->execute(['salle_id' => $salle_id]);

    if ($old_salle_id != $salle_id) {
        $stmt = $conn->prepare("
            UPDATE classrooms
            SET disponible = NOT EXISTS (
                SELECT 1 FROM tp WHERE salle_id = :old_salle_id
            )
            WHERE id = :old_salle_id
        ");
        $stmt->execute(['old_salle_id' => $old_salle_id]);
    }

    echo json_encode(['success' => true, 'message' => 'TP modifié avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}

$conn = null;
?>