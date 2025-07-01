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

    $user_id = $_SESSION['user_id'];
    $periode = $_GET['periode'] ?? 'semaine';
    $room_id = $_GET['roomId'] ?? null;

    $stats = [
        'total_salles' => 0,
        'salles_disponibles' => 0,
        'salles_reservees' => 0,
        'total_tps' => 0,
        'total_ordinateurs' => 0,
        'occupation_rate' => 0,
        'hours_used' => 0,
        'tp_hours' => 0,
        'most_used_room' => '-',
        'most_used_hours' => 0,
        'pc_usage' => 0,
        'daily_data' => [],
        'comparison_data' => [],
        'material_data' => [] // Ajout de material_data
    ];

    // Dates
    switch ($periode) {
        case 'mois':
            $date_start = date('Y-m-01');
            $date_end = date('Y-m-t');
            break;
        case 'trimestre':
            $mois = date('m');
            $debut = floor(($mois - 1) / 3) * 3 + 1;
            $date_start = date("Y-" . str_pad($debut, 2, '0', STR_PAD_LEFT) . "-01");
            $date_end = date("Y-m-t", strtotime("+2 months", strtotime($date_start)));
            break;
        case 'annee':
            $date_start = date('Y-01-01');
            $date_end = date('Y-12-31');
            break;
        default:
            $date_start = date('Y-m-d', strtotime('monday this week'));
            $date_end = date('Y-m-d', strtotime('sunday this week'));
    }

    $params = ['user_id' => $user_id, 'date_start' => $date_start, 'date_end' => $date_end];
    $room_condition = $room_id ? "AND c.id = :room_id" : '';
    if ($room_id) $params['room_id'] = $room_id;

    // Récupérer les salles
    $stmt = $conn->prepare("SELECT id, nom FROM classrooms WHERE user_id = :user_id ORDER BY nom");
    $stmt->execute(['user_id' => $user_id]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats['total_salles'] = count($rooms);

    // Salles disponibles
    $stmt = $conn->prepare("SELECT COUNT(*) FROM classrooms WHERE disponible = true AND user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $stats['salles_disponibles'] = (int)$stmt->fetchColumn();
    $stats['salles_reservees'] = $stats['total_salles'] - $stats['salles_disponibles'];

    // TPs, ordinateurs
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT tp.id) FROM tp LEFT JOIN classrooms c ON tp.salle_id = c.id WHERE tp.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $stats['total_tps'] = (int)$stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantite), 0) FROM computers WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $stats['total_ordinateurs'] = (int)$stmt->fetchColumn();

    // Heures utilisées
    $stmt = $conn->prepare("SELECT COALESCE(SUM(EXTRACT(EPOCH FROM (ts.heure_fin - ts.heure_debut))/3600),0) FROM tp LEFT JOIN tp_schedules ts ON tp.id = ts.tp_id LEFT JOIN classrooms c ON tp.salle_id = c.id WHERE tp.user_id = :user_id AND ts.date BETWEEN :date_start AND :date_end $room_condition");
    $stmt->execute($params);
    $stats['hours_used'] = round((float)$stmt->fetchColumn(), 2);
    $stats['tp_hours'] = $stats['hours_used'];

    // Salle la plus utilisée
    $stmt = $conn->prepare("SELECT c.nom, COALESCE(SUM(EXTRACT(EPOCH FROM (ts.heure_fin - ts.heure_debut))/3600), 0) AS hours FROM classrooms c LEFT JOIN tp ON c.id = tp.salle_id AND tp.user_id = :user_id LEFT JOIN tp_schedules ts ON tp.id = ts.tp_id WHERE c.user_id = :user_id AND ts.date BETWEEN :date_start AND :date_end $room_condition GROUP BY c.nom ORDER BY hours DESC LIMIT 1");
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['most_used_room'] = $row['nom'] ?? '-';
    $stats['most_used_hours'] = isset($row['hours']) ? round((float)$row['hours'], 2) : 0;

    // PC usage
    $stmt = $conn->prepare("SELECT COALESCE(SUM(comp.quantite * EXTRACT(EPOCH FROM (ts.heure_fin - ts.heure_debut))/3600), 0) FROM computers comp LEFT JOIN classrooms c ON comp.classroom_id = c.id LEFT JOIN tp ON c.id = tp.salle_id AND tp.user_id = :user_id LEFT JOIN tp_schedules ts ON tp.id = ts.tp_id WHERE comp.user_id = :user_id AND ts.date BETWEEN :date_start AND :date_end $room_condition");
    $stmt->execute($params);
    $pc_hours = (float)$stmt->fetchColumn();
    $total_possible_pc_hours = $stats['total_ordinateurs'] * 24 * ((strtotime($date_end) - strtotime($date_start)) / 86400);
    $stats['pc_usage'] = $total_possible_pc_hours > 0 ? round(($pc_hours / $total_possible_pc_hours) * 100, 2) : 0;

    // daily_data
    $daily_data = [];
    $days = [];
    $d = strtotime($date_start);
    while ($d <= strtotime($date_end)) {
        $days[] = date('Y-m-d', $d);
        $d = strtotime('+1 day', $d);
    }
    foreach ($rooms as $room) {
        if ($room_id && $room['id'] != $room_id) continue;
        $room_data = [
            'room' => $room['nom'],
            'days' => array_map(fn($x) => date('D d M', strtotime($x)), $days),
            'hours' => []
        ];
        foreach ($days as $day) {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(EXTRACT(EPOCH FROM (ts.heure_fin - ts.heure_debut))/3600), 0) FROM tp LEFT JOIN tp_schedules ts ON tp.id = ts.tp_id LEFT JOIN classrooms c ON tp.salle_id = c.id WHERE tp.user_id = :user_id AND ts.date = :day AND c.id = :room_id");
            $stmt->execute([
                'user_id' => $user_id,
                'day' => $day,
                'room_id' => $room['id']
            ]);
            $room_data['hours'][] = ['date' => $day, 'hours' => round((float)$stmt->fetchColumn(), 2)];
        }
        $daily_data[] = $room_data;
    }
    $stats['daily_data'] = $daily_data;

    // comparison_data
    $stmt = $conn->prepare("SELECT c.nom, COALESCE(SUM(EXTRACT(EPOCH FROM (ts.heure_fin - ts.heure_debut))/3600), 0) as hours FROM classrooms c LEFT JOIN tp ON c.id = tp.salle_id AND tp.user_id = :user_id LEFT JOIN tp_schedules ts ON tp.id = ts.tp_id AND ts.date BETWEEN :date_start AND :date_end WHERE c.user_id = :user_id GROUP BY c.nom ORDER BY c.nom");
    $stmt->execute(['user_id' => $user_id, 'date_start' => $date_start, 'date_end' => $date_end]);
    $stats['comparison_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // material_data (exemple simulé - à adapter selon vos données réelles)
    $months = [];
    $m = strtotime($date_start);
    while ($m <= strtotime($date_end)) {
        $month = date('Y-m', $m);
        $months[] = $month;
        $m = strtotime('+1 month', $m);
    }
    $material_data = [];
    foreach ($months as $month) {
        $month_data = ['month' => $month, 'rooms' => []];
        foreach ($rooms as $room) {
            if ($room_id && $room['id'] != $room_id) continue;
            // Simuler le nombre d'ordinateurs (remplacez par une vraie requête SQL)
            $stmt = $conn->prepare("SELECT COALESCE(SUM(quantite), 0) FROM computers WHERE classroom_id = :room_id AND user_id = :user_id AND created_at <= :month_end");
            $month_end = date('Y-m-t', strtotime($month));
            $stmt->execute(['room_id' => $room['id'], 'user_id' => $user_id, 'month_end' => $month_end]);
            $computers = (int)$stmt->fetchColumn();
            $month_data['rooms'][] = ['room' => $room['nom'], 'computers' => $computers];
        }
        $material_data[] = $month_data;
    }
    $stats['material_data'] = $material_data;

    echo json_encode(['success' => true, 'stats' => $stats]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
$conn = null;
?>