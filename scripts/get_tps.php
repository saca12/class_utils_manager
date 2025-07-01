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

      $user_id = $_SESSION['user_id'] ?? null;
      if (!$user_id) {
          echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
          exit;
      }

      $stmt = $conn->prepare("
          SELECT tp.id, tp.tp_nom, tp.matiere, tp.salle_id, tp.logiciels, tp_schedules.date, tp_schedules.heure_debut, tp_schedules.heure_fin
          FROM tp
          LEFT JOIN tp_schedules ON tp.id = tp_schedules.tp_id
          WHERE tp.user_id = :user_id
      ");
      $stmt->execute(['user_id' => $user_id]);
      $tps = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $formatted_tps = [];
      foreach ($tps as $tp) {
          $tp_id = $tp['id'];
          if (!isset($formatted_tps[$tp_id])) {
              $formatted_tps[$tp_id] = [
                  'id' => $tp_id,
                  'tp_nom' => $tp['tp_nom'],
                  'matiere' => $tp['matiere'],
                  'salle_id' => "salle_" . $tp['salle_id'], // Adapter au format de salles
                  'logiciels' => $tp['logiciels'] ?? 'Aucun',
                  'schedules' => []
              ];
          }
          if ($tp['date']) {
              $formatted_tps[$tp_id]['schedules'][] = [
                  'date' => $tp['date'],
                  'heure_debut' => $tp['heure_debut'],
                  'heure_fin' => $tp['heure_fin']
              ];
          }
      }

      echo json_encode(['success' => true, 'reservations' => array_values($formatted_tps)]);
  } catch (PDOException $e) {
      echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
  }

  $conn = null;
  ?>