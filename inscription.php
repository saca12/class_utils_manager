<!DOCTYPE html>
<html lang="fr">
<head>
<!--body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #3c424e;
        padding: 20px;
      }
      h2 {
        color: #80acfe;
      }
      .form-container {
        background-color: rgba(31, 58, 115, 0.2);
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #80acfe;
        max-width: 500px;
        margin: 0 auto;
      }-->
<!--body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #3c424e;
            padding: 20px;
        }
        h2 {
            color: #80acfe;
        }
        .form-container {
            background-color: rgba(31, 58, 115, 0.2);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #80acfe;
            max-width: 500px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-top: 20px;
            color: #eaedf3;
            outline: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="hidden"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
        }
        input[type="submit"] {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #eaedf3;
            border: none;
            color: #a25100;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #d0d3d9;
        }
        .link-container {
            text-align: center;
            margin-top: 20px;
            color: #eaedf3;
        }
        a {
            color: #80acfe;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff4d4d;
            margin-top: 10px;
            text-align: center;
        }
        .success {
            color: #4caf50;
            margin-top: 10px;
            text-align: center;
        }
    </style>-->
    <meta charset="UTF-8" />
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #3c424e;
            padding: 20px;
        }
        h2 {
            color: #80acfe;
        }
        .form-container {
            background-color: rgba(31, 58, 115, 0.2);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #80acfe;
            max-width: 500px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-top: 20px;
            color: #eaedf3;
            outline: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="hidden"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
        }
        input[type="submit"] {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #eaedf3;
            border: none;
            color: #a25100;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #d0d3d9;
        }
        .link-container {
            text-align: center;
            margin-top: 20px;
            color: #eaedf3;
        }
        a {
            color: #80acfe;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff4d4d;
            margin-top: 10px;
            text-align: center;
        }
        .success {
            color: #4caf50;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Inscription</h2>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<p class="error">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p class="success">' . htmlspecialchars($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
        ?>
        <form id="S_inscrire" action="../scripts/inscription.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required />

            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" required />

            <label for="matricule">Matricule:</label>
            <input type="text" id="matricule" name="matricule" required pattern="[A-Za-z0-9]{6,10}" title="Le matricule doit contenir entre 6 et 10 caractères alphanumériques." />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required />

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required />

            <label for="confirm_password">Confirmer le mot de passe:</label>
            <input type="password" id="confirm_password" name="confirm_password" required />

            <input type="submit" value="S'inscrire" />
        </form>

        <div class="link-container">
            <p>Vous avez déjà un compte? <a href="connection.html">Se connecter</a></p>
        </div>
    </div>
    <script>
        document.getElementById('S_inscrire').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matricule = document.getElementById('matricule').value;
            const matriculeRegex = /^[A-Za-z0-9]{6,10}$/;

            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
            }
            if (password.length < 8) {
                event.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
            }
            if (!matriculeRegex.test(matricule)) {
                event.preventDefault();
                alert('Le matricule doit contenir entre 6 et 10 caractères alphanumériques.');
            }
        });
    </script>
</body>
</html>
<!--#error-message {
        color: #d32f2f;
        background-color: #ffebee;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ef9a9a;
        margin-bottom: 10px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        text-align: center;
        max-width: 300px;
        margin-left: auto;
        margin-right: auto;
        display: none;
      }
      #error-message.visible {
        display: block;
      }
      .form-container {
        max-width: 320px; /* Légèrement plus large pour inclure les marges */
        margin: 20px auto; /* Centrage avec marge */
        padding: 10px;
        background-color: #ffffff; /* Fond blanc pour contraste */
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombre légère */
      }

      label {
        display: block;
        margin: 5px 0 2px;
      }

      button {
        width: 100%;
        padding: 10px;
        background-color: #1976d2;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }
      button:hover {
        background-color: #1565c0;
      }
      a {
        color: #1976d2;
        text-decoration: none;
        margin: 0 5px;
      }
      a:hover {
        text-decoration: underline;
      }








      ////////////////////////////////
       <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #3c424e;
        margin: 0;
        color: #ffffff;
      }

      .head {
        background-color: #525b52;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #1f3a73;
      }

      .logo {
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .logo-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #1f3a73;
      }

      .logo-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .logo-text {
        color: #80acfe;
        font-size: 20px;
        font-weight: bold;
      }

      .navigate {
        display: flex;
        align-items: center;
        gap: 30px;
      }

      .nav-links {
        display: flex;
        list-style: none;
        gap: 30px;
      }

      .nav-links li {
        color: #ffffff;
        cursor: pointer;
        padding: 12px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
      }

      .nav-links li:hover,
      .nav-links li.active {
        background-color: rgba(31, 58, 115, 0.5);
        transform: translateY(-2px);
      }

      .login-btn {
        color: white;
        background-color: #a25100;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
      }

      .login-btn:hover {
        background-color: #c26202;
        transform: translateY(-2px);
      }

      .user-profile {
        position: relative;
        display: none;
      }

      .profile-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #a25100;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
        transition: all 0.3s ease;
      }

      .profile-btn:hover {
        background-color: #c26202;
        transform: scale(1.1);
      }

      .content-container {
        padding: 30px;
        max-width: 1400px;
        margin: 0 auto;
        background-color: #525b52;
        border-radius: 10px;
        margin-top: 20px;
        border: 1px solid #1f3a73;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
      }

      .page-title {
        color: #80acfe;
        font-size: 32px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 40px;
        border-bottom: 2px solid #1f3a73;
        padding-bottom: 10px;
      }

      /* Section Home */
      .home-content {
        display: none;
        text-align: center;
      }

      .home-content.active {
        display: block;
      }

      .welcome-message {
        font-size: 24px;
        margin-bottom: 20px;
        color: #efe9e9;
      }

      .home-image {
        width: 100%;
        max-width: 800px;
        height: auto;
        border-radius: 10px;
        border: 2px solid #1f3a73;
        margin: 20px auto;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
      }

      /* Section Salles et Travaux Pratiques */
      .salles-section,
      .tp-section {
        display: none;
      }

      .salles-section.active,
      .tp-section.active {
        display: block;
      }

      .salle-grid,
      .tp-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
      }

      .salle-item {
        background-color: rgba(239, 233, 233, 0.1);
        border-radius: 16px;
        overflow: hidden;
        width: 300px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        border: 1px solid #1f3a73;
        position: relative;
      }

      .salle-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
      }

      .salle-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }

      .salle-info {
        padding: 20px;
      }

      .salle-name {
        font-size: 20px;
        font-weight: bold;
        color: #80acfe;
        margin-bottom: 10px;
      }

      .salle-description {
        color: #efe9e9;
        margin-bottom: 15px;
      }

      .salle-spec {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
      }

      .salle-status {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        margin-bottom: 10px;
        text-align: center;
      }

      .available {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
      }

      .unavailable {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
      }

      .salle-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
      }

      .action-btn {
        padding: 8px 15px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
      }

      .edit-btn {
        background-color: #1f3a73;
        color: white;
      }

      .edit-btn:hover {
        background-color: #2a4a8f;
      }

      .delete-btn {
        background-color: #721c24;
        color: white;
      }

      .delete-btn:hover {
        background-color: #8f242e;
      }

      .reserve-btn {
        background-color: #28a745;
        color: white;
        width: 100%;
        margin-top: 10px;
      }

      .reserve-btn:hover {
        background-color: #218838;
      }

      .reserve-btn:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
      }

      .add-salle-card {
        background-color: rgba(31, 58, 115, 0.2);
        border: 2px dashed #1f3a73;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 300px;
        height: 300px;
        border-radius: 16px;
      }

      .add-salle-card:hover {
        background-color: rgba(31, 58, 115, 0.4);
        border-color: #80acfe;
      }

      .add-icon {
        font-size: 48px;
        color: #80acfe;
        margin-bottom: 15px;
      }

      .add-text {
        color: #80acfe;
        font-size: 18px;
        font-weight: 500;
      }

      /* Modal pour modifier une salle */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        justify-content: center;
        align-items: center;
      }

      .modal-content {
        background-color: #525b52;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        border: 1px solid #1f3a73;
      }

      .modal-title {
        color: #80acfe;
        margin-bottom: 20px;
        font-size: 24px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #80acfe;
      }

      .form-group input,
      .form-group textarea,
      .form-group select {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #1f3a73;
        background-color: #3c424e;
        color: white;
      }

      .form-group textarea {
        min-height: 100px;
      }

      .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 20px;
      }

      .modal-btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 500;
      }

      .cancel-btn {
        background-color: #6c757d;
        color: white;
      }

      .save-btn {
        background-color: #a25100;
        color: white;
      }

      .save-btn:hover {
        background-color: #c26202;
      }

      /* Styles pour les onglets */
      .tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #1f3a73;
      }

      .tab {
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 6px 6px 0 0;
        margin-right: 5px;
        background-color: rgba(31, 58, 115, 0.3);
        transition: all 0.3s ease;
        color: #ffffff;
        font-weight: 500;
      }

      .tab.active {
        background-color: rgba(31, 58, 115, 0.7);
        color: #80acfe;
        font-weight: bold;
      }

      .tab:hover:not(.active) {
        background-color: rgba(31, 58, 115, 0.5);
      }

      @media (max-width: 768px) {
        .head {
          flex-direction: column;
          gap: 20px;
          padding: 20px;
        }

        .nav-links {
          flex-direction: column;
          gap: 10px;
          width: 100%;
          text-align: center;
        }

        .salle-grid,
        .tp-grid {
          grid-template-columns: 1fr;
        }
      }
    </style>











    ///////////////
    <div class="head">
      <div class="logo">
        <div class="logo-img">
          <img
            src="./images/esi.jpg.webp"
            alt="ESI Logo"
            onerror="this.style.display='none'"
          />
        </div>
        <div class="logo-text">Ecole Supérieure d'Informatique</div>
      </div>
      <div class="navigate">
        <nav class="nav-bar">
          <ul class="nav-links">
            <li class="home active" data-page="home">Home</li>
            <li class="classroom-manager" data-page="classroom_manager">
              Classroom Manager
            </li>
            <li>
              <a
                href="Statistique.html"
                style="text-decoration: none; color: inherit"
                >Statistics</a
              >
            </li>
            <li>
              <a href="About.html" style="text-decoration: none; color: inherit"
                >About</a
              >
            </li>
          </ul>
        </nav>
        <button class="login-btn" id="loginBtn">Login</button>
        <div class="user-profile" id="userProfile">
          <button class="profile-btn" id="profileBtn"></button>
        </div>
      </div>
    </div>

    <div class="content-container">
      <!-- Page d'accueil -->
      <div class="home-content active" id="homeContent">
        <h1 class="page-title">ESI CLASS UTILS MANAGER</h1>
        <div class="welcome-message">
          <p>
            Gérez efficacement vos salles de travaux pratiques avec notre
            système intégré.
          </p>
          <p>
            Réservez, planifiez et optimisez l'utilisation de vos espaces
            d'apprentissage.
          </p>
        </div>
        <img src="images/classroom.jpg" alt="Salle M1" class="home-image" />
      </div>

      <!-- Gestionnaire de salles -->
      <div
        class="classroom-manager-content"
        id="classroomContent"
        style="display: none"
      >
        <h1 class="page-title">Gestion des Salles de TP</h1>

        <div class="tabs">
          <div class="tab active" data-tab="salles">Salles</div>
          <div class="tab" data-tab="tp">Travaux Pratiques</div>
        </div>

        <!-- Section des salles -->
        <div class="salles-section" id="sallesTab">
          <div class="salle-grid" id="salleGrid">
            <!-- Les salles seront insérées ici dynamiquement -->
            <div class="add-salle-card" id="addSalleBtn">
              <div class="add-icon">+</div>
              <div class="add-text">Ajouter Salle</div>
            </div>
          </div>
        </div>

        <!-- Section des travaux pratiques -->
        <div class="tp-section" id="tpTab">
          <h2>Liste des Travaux Pratiques</h2>
          <div class="tp-grid" id="tpGrid">
            <!-- Bouton pour ajouter un TP -->
            <div class="add-salle-card" id="addTpBtn">
              <div class="add-icon">+</div>
              <div class="add-text">Ajouter TP</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour modifier une salle -->
    <div class="modal" id="editModal">
      <div class="modal-content">
        <h2 class="modal-title">Modifier la salle</h2>
        <form id="editForm">
          <div class="form-group">
            <label for="edit-nom">Nom de la salle</label>
            <input type="text" id="edit-nom" name="edit-nom" required />
          </div>
          <div class="form-group">
            <label for="edit-description">Description</label>
            <textarea
              id="edit-description"
              name="edit-description"
              rows="3"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="edit-capacite">Capacité</label>
            <input
              type="number"
              id="edit-capacite"
              name="edit-capacite"
              min="1"
              required
            />
          </div>
          <div class="form-group">
            <label for="edit-ordinateurs">Nombre d'ordinateurs</label>
            <input
              type="number"
              id="edit-ordinateurs"
              name="edit-ordinateurs"
              min="0"
            />
          </div>
          <div class="form-group">
            <label for="edit-disponible">Disponibilité</label>
            <select id="edit-disponible" name="edit-disponible">
              <option value="true">Disponible</option>
              <option value="false">Indisponible</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" class="modal-btn cancel-btn" id="cancelEdit">
              Annuler
            </button>
            <button type="submit" class="modal-btn save-btn">
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>