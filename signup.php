<?php
session_start();

// 1. Datenbankverbindung
$conn = new mysqli("localhost", "rundb", "runpass", "books");
if ($conn->connect_error) {
    die("Datenbankfehler: " . $conn->connect_error);
}

$error = '';
$vorname = '';
$nachname = '';
$benutzername = '';
$email = '';
$admin = 0; // Standard: kein Admin

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eingabewerte holen
    $vorname = trim($_POST['vorname'] ?? '');
    $nachname = trim($_POST['nachname'] ?? '');
    $benutzername = trim($_POST['benutzername'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Prüfen, ob der Admin-Checkbox gesetzt wurde
    if (isset($_POST['admin']) && $_POST['admin'] == 'on') {
        $admin = 1; // Admin-Status setzen
    }

    if (empty($vorname) || empty($nachname) || empty($benutzername) || empty($email) || empty($password)) {
        $error = "Bitte alle Felder ausfüllen";
    } else {
        // Passwort verschlüsseln
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Überprüfen, ob der Benutzername bereits existiert
        $stmt = $conn->prepare("SELECT id FROM benutzer WHERE benutzername = ?");
        $stmt->bind_param("s", $benutzername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Benutzername ist bereits vergeben";
        } else {
            // Neuen Benutzer in der Datenbank speichern
            $stmt = $conn->prepare("INSERT INTO benutzer (vorname, nachname, benutzername, email, passwort, admin) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $vorname, $nachname, $benutzername, $email, $hashed_password, $admin);

            if ($stmt->execute()) {
                $_SESSION['benutzername'] = $benutzername;
                header("Location: change_successful.php"); // Weiterleitung zur Bestätigungsseite
                exit();
            } else {
                $error = "Fehler bei der Registrierung. Versuche es später noch einmal.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrierung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #F9FFE1;
            min-height: 100vh;
        }
        .signup-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header class="p-3 text-bg-dark">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="ProjektWebsite.php" class="nav-link px-2 text-secondary">Home</a></li>
                    <li><a href="Kontakt.php" class="nav-link px-2 text-white">Kontakt</a></li>
                    <li><a href="books.php" class="nav-link px-2 text-white">Books</a></li>
                </ul>

                <div class="text-end">
                    <button type="button" class="btn btn-outline-light me-2"><a href="login.php" class="nav-link px-2">Login</a></button>
                    <button type="button" class="btn btn-warning"><a href="signup.php" class="nav-link px-2 text-white">Sign-up</a></button>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="signup-container">
            <h2 class="text-center mb-4">Registrieren</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Vorname</label>
                    <input type="text" 
                           name="vorname" 
                           class="form-control"
                           value="<?= htmlspecialchars($vorname) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nachname</label>
                    <input type="text" 
                           name="nachname" 
                           class="form-control"
                           value="<?= htmlspecialchars($nachname) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Benutzername</label>
                    <input type="text" 
                           name="benutzername" 
                           class="form-control"
                           value="<?= htmlspecialchars($benutzername) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">E-Mail</label>
                    <input type="email" 
                           name="email" 
                           class="form-control"
                           value="<?= htmlspecialchars($email) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Passwort</label>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" 
                           name="admin" 
                           class="form-check-input">
                    <label class="form-check-label">Als Administrator registrieren</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Registrieren</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
