<?php
header('Content-Type: application/json'); // Définir le type de réponse comme JSON en premier
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification du token CSRF (à implémenter avec un vrai système)
    // $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    // if (!verifyCsrfToken($csrfToken)) {
    //     $response['message'] = "Requête invalide.";
    //     echo json_encode($response);
    //     exit;
    // }

    try {
        $conn = new PDO("pgsql:host=localhost;port=5432;dbname=esi_class_manager;user=postgres;password=16269758h");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Extraction des données JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $nom = trim($input['nom'] ?? '');
        $prenom = trim($input['prenom'] ?? '');
        $matricule = trim($input['matricule'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $confPassword = trim($input['conf_password'] ?? '');

        // Validation des champs
        if (empty($nom) || empty($prenom) || empty($matricule) || empty($email) || empty($password) || empty($confPassword)) {
            $response['message'] = "Tous les champs sont obligatoires.";
        } elseif (strlen($nom) > 50 || strlen($prenom) > 50) {
            $response['message'] = "Le nom ou prénom ne doit pas dépasser 50 caractères.";
        } elseif (strlen($matricule) < 6 || strlen($matricule) > 20) {
            $response['message'] = "Le matricule doit contenir entre 6 et 20 caractères.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $response['message'] = "Veuillez entrer un email valide (max 100 caractères).";
        } elseif (strlen($password) < 8 || strlen($password) > 50) {
            $response['message'] = "Le mot de passe doit contenir entre 8 et 50 caractères.";
        } elseif ($password !== $confPassword) {
            $response['message'] = "Les mots de passe ne correspondent pas.";
        } else {
            // Vérification de l'unicité
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE matricule = :matricule");
            $stmt->execute(['matricule' => $matricule]);
            if ($stmt->fetchColumn() > 0) {
                $response['message'] = "Ce matricule existe déjà.";
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetchColumn() > 0) {
                    $response['message'] = "Cet email est déjà utilisé.";
                } else {
                    // Hachage et insertion
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (nom, prenom, matricule, email, password) VALUES (:nom, :prenom, :matricule, :email, :password)");
                    $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'matricule' => $matricule, 'email' => $email, 'password' => $hashedPassword]);
                    $response['success'] = true;
                    $response['message'] = "Inscription réussie !";
                    // Initialisation de la session (optionnel, selon votre logique)
                    $_SESSION['isLoggedIn'] = true;
                    $_SESSION['user_id'] = $conn->lastInsertId();
                }
            }
        }

        echo json_encode($response);
    } catch (PDOException $e) {
        $response['message'] = "Erreur de connexion à la base de données.";
        echo json_encode($response);
    } catch (Exception $e) {
        $response['message'] = "Une erreur inattendue s'est produite.";
        echo json_encode($response);
    }
    exit();
}

// Fonction fictive pour CSRF (à implémenter)
function verifyCsrfToken($token) {
    // Logique pour vérifier le token (par exemple, via session)
    return true; // Remplacer par une vérification réelle
}
?>