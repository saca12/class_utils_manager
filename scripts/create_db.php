<?php
// Paramètres de connexion
$host = "localhost";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "16269758h"; 

try {
    // Connexion à la base par défaut 'postgres'
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier les privilèges de l'utilisateur
    $stmt = $conn->query("SELECT rolsuper, rolcreatedb FROM pg_roles WHERE rolname = '$user'");
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$role['rolcreatedb']) {
        throw new Exception("L'utilisateur '$user' n'a pas le privilège CREATEDB. Exécutez : ALTER ROLE $user WITH CREATEDB;");
    }

    // Vérifier si la base esi_class_manager existe
    $stmt = $conn->query("SELECT datname FROM pg_database WHERE datname = 'esi_class_manager'");
    $databaseExists = $stmt->fetch();

    if (!$databaseExists) {
        // Créer la base de données
        $conn->exec("CREATE DATABASE esi_class_manager");
    } 

    // Se connecter à la nouvelle base
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=esi_class_manager;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer les tables
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(50) NOT NULL,
            prenom VARCHAR(50) NOT NULL,
            matricule VARCHAR(20) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS classrooms (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL,
            nom VARCHAR(100) UNIQUE NOT NULL,
            batiment VARCHAR(50),
            etage VARCHAR(20),
            tables INT,
            chaises INT,
            prises INT,
            etat VARCHAR(20),
            problemes TEXT,
            disponible BOOLEAN DEFAULT true,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS computers (
            id SERIAL PRIMARY KEY,
            classroom_id INT NOT NULL,
            modele VARCHAR(100) NOT NULL,
            marque VARCHAR(100) NOT NULL,  
            ram INTEGER NOT NULL,
            processeur VARCHAR(100),
            carte_reseau VARCHAR(100),
            ports TEXT,
            peripheriques TEXT,
            logiciels TEXT,
            disque INTEGER,
            os VARCHAR(100) NOT NULL,
            etat VARCHAR(100) NOT NULL,
            quantite INTEGER NOT NULL DEFAULT 1,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP,
            FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS tp (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL,
            tp_nom VARCHAR(255) NOT NULL,
            matiere VARCHAR(255) NOT NULL,
            salle_id INT NOT NULL,
            logiciels TEXT,
            nombre_ordi INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salle_id) REFERENCES classrooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS tp_schedules (
            id SERIAL PRIMARY KEY,
            tp_id INT NOT NULL,
            date DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            FOREIGN KEY (tp_id) REFERENCES tp(id) ON DELETE CASCADE
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS reservations (
            id SERIAL PRIMARY KEY,
            salle_id INT NOT NULL,
            user_id INT,
            date DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            motif VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salle_id) REFERENCES classrooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS consommables (
            id SERIAL PRIMARY KEY,
            classroom_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            quantite INT NOT NULL,
            commentaires TEXT,
            FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
        )
    ");

} catch (PDOException $e) {
    echo "Erreur PDO : " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "<br>";
}

$conn = null;
?>


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

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    if (empty($data['classroom_id']) || empty($data['marque']) || empty($data['modele']) || empty($data['ram']) || empty($data['os']) || empty($data['mac']) || empty($data['etat'])) {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        exit;
    }

    // Check for unique MAC address
    $stmt = $conn->prepare("SELECT COUNT(*) FROM computers WHERE mac = :mac");
    $stmt->execute(['mac' => $data['mac']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Adresse MAC déjà utilisée']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO computers (classroom_id, modele, marque, ram, processeur, carte_reseau, ports, peripheriques, logiciels, disque, os, mac, etat, quantite, user_id)
        VALUES (:classroom_id, :modele, :marque, :ram, :processeur, :carte_reseau, :ports, :peripheriques, :logiciels, :disque, :os, :mac, :etat, :quantite, :user_id)
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
//$conn = null;
?>
