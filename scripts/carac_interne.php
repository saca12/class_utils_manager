<?php
header('Content-Type: application/json');
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $salle_id = str_replace("salle_", "", $data['salle_id']);
    $marque = $data['marque'];
    $modele = $data['modele'];
    $ram = $data['ram'] ?? null;
    $processeur = $data['processeur'] ?? null;
    $disque_dur = $data['disque_dur'] ?? null;
    $quantite = $data['quantite'] ?? 1;
    $user_id = $data['user_id'] ?? null;

    // Connexion à la base de données (remplacez par vos paramètres)
    $pdo = new PDO('pgsql:host=localhost;dbname=your_db', 'user', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO computers (salle_id, marque, modele, ram, processeur, disque_dur, quantite)
        VALUES (:salle_id, :marque, :modele, :ram, :processeur, :disque_dur, :quantite)
        RETURNING id
    ");
    $stmt->execute([
        'salle_id' => $salle_id,
        'marque' => $marque,
        'modele' => $modele,
        'ram' => $ram,
        'processeur' => $processeur,
        'disque_dur' => $disque_dur,
        'quantite' => $quantite
    ]);

    echo json_encode(['success' => true, 'id' => $stmt->fetchColumn()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>