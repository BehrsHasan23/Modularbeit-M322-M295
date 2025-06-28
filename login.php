<?php
session_start(); 
// Serverseitig: Startet eine Session (eine Art „temporärer Speicher“), 
// die es ermöglicht, Daten (z.B. Login-Status) über mehrere Seitenaufrufe hinweg zu speichern. 
// Jede Session hat eine eindeutige ID, die meist per Cookie im Browser gespeichert wird.

include 'mysqli.php'; 
// Serverseitig: Fügt die Datei 'mysqli.php' ein, in der wahrscheinlich die Datenbankverbindung ($conn) mit MySQLi steht.
// So kann die Datenbank hier benutzt werden, ohne den Verbindungs-Code doppelt schreiben zu müssen.

$error = ''; 
// Serverseitig: Variable zum Speichern von Fehlermeldungen, wenn z.B. Login fehlschlägt.
$benutzername = '';
$password = '';

//HTTP ist die Sprache, mit der Browser und Server im Internet miteinander sprechen und Webseiten austauschen.
// Serverseitig: Prüft, ob das Formular mit der Methode POST abgeschickt wurde.
// POST ist eine HTTP-Methode, bei der Formulardaten sicherer übertragen werden als bei GET.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Holt die Benutzereingaben aus dem POST-Array, entfernt Leerzeichen mit trim()
    $benutzername = trim($_POST['benutzername'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validierung der Eingaben auf Serverseite:
    // Diese Sicherheitsabfrage verhindert zu lange Benutzernamen und zu kurze/lange Passwörter,
    // damit keine fehlerhaften oder bösartigen Daten verarbeitet werden.
    if (strlen($benutzername) > 45) {
        $error = "Benutzername darf maximal 45 Zeichen lang sein.";
    } elseif (strlen($password) < 8 || strlen($password) > 100) {
        $error = "Passwort muss zwischen 8 und 100 Zeichen lang sein.";
    } elseif (empty($benutzername) || empty($password)) {
        $error = "Bitte alle Felder ausfüllen";
    } else {
        // Serverseitig: Bereitet eine SQL-Anfrage vor, um den Benutzer anhand des Benutzernamens zu finden.
        // prepare() und bind_param() schützen vor SQL-Injection (Manipulation von Datenbankabfragen durch bösartigen Code).
        $stmt = $conn->prepare("SELECT id, passwort, admin FROM benutzer WHERE benutzername = ?");
        $stmt->bind_param("s", $benutzername); // "s" bedeutet String – Typ des Parameters
        $stmt->execute(); // Führt die vorbereitete Abfrage aus
        $result = $stmt->get_result(); // Holt das Ergebnis der Abfrage

        // Prüft, ob genau ein Datensatz (Benutzer) gefunden wurde
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc(); // Holt die Daten als assoziatives Array (Schlüssel => Wert)

            // password_verify vergleicht das eingegebene Passwort mit dem gespeicherten Hash in der DB.
            // Passwörter werden nie im Klartext gespeichert, sondern als Hash (eine Art Einweg-Verschlüsselung).
            if (password_verify($password, $user['passwort'])) {
                // Wenn Passwort stimmt, werden wichtige Daten in der Session gespeichert:
                $_SESSION['user_id'] = $user['id']; // Nutzer-ID für spätere Abfragen
                $_SESSION['benutzername'] = $benutzername; // Nutzername für Anzeige/Zugriff
                $_SESSION['is_admin'] = ($user['admin'] == 1); // Ob der Nutzer Adminrechte hat (true/false)

                // Serverseitig: Nach erfolgreichem Login wird der Browser mit einem HTTP-Header auf dashboard.php weitergeleitet.
                header("Location: dashboard.php");
                exit(); // Beendet das Skript sofort, damit kein weiterer Code ausgeführt wird.
            } else {
                $error = "Falsche Anmeldedaten"; // Passwort war falsch
            }
        } else {
            $error = "Benutzer nicht gefunden"; // Kein Benutzer mit diesem Namen gefunden
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <!-- Clientseitig: Einbinden von Bootstrap CSS für das Layout und Design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Clientseitiges CSS: Gestaltung des Login-Containers */
        body {
            background: #F9FFE1;
            min-height: 100vh; /* Mindestens volle Bildschirmhöhe */
        }
        .login-container {
            max-width: 400px;
            margin: 2rem auto; /* Zentriert horizontal mit Abstand oben und unten */
            padding: 2rem;
            background: white;
            border-radius: 8px; /* Runde Ecken */
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* leichter Schatten */
        }
    </style>
</head>
<body>
    <header class="p-3 text-bg-dark">
        <div class="container">
            <!-- Navigation oben -->
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <!-- Clientseitige Links für Navigation -->
                    <li><a href="index.php" class="nav-link px-2 text-white">Home</a></li>
                    <li><a href="Kontakt.php" class="nav-link px-2 text-white">Kontakt</a></li>
                    <li><a href="books.php" class="nav-link px-2 text-white">Books</a></li>
                </ul>

                <div class="text-end">
                    <!-- Login und Sign-up Buttons -->
                    <button type="button" class="btn btn-outline-light me-2">
                        <a href="login.php" class="nav-link px-2">Login</a>
                    </button>
                    <button type="button" class="btn btn-warning">
                        <a href="signup.php" class="nav-link px-2 text-white">Sign-up</a>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if (!empty($error)): ?>
                <!-- Clientseitig: Zeigt eine rote Fehlermeldung an, wenn $error nicht leer ist -->
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Login-Formular: Wird per POST an dieselbe Seite geschickt -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Benutzername</label>
                    <input type="text" 
                           name="benutzername" 
                           class="form-control"
                           value="<?= htmlspecialchars($benutzername) ?>" 
                           required
                           maxlength="45">
                           <!-- Clientseitige Validierung:
                                required = Feld muss ausgefüllt sein
                                maxlength = Maximal 45 Zeichen -->
                </div>

                <div class="mb-3">
                    <label class="form-label">Passwort</label>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           required
                           minlength="8" 
                           maxlength="100">
                           <!-- Clientseitige Validierung: Passwort muss mind. 8 Zeichen haben -->
                </div>

                <button type="submit" class="btn btn-primary w-100">Anmelden</button>
            </form>
        </div>
    </div>

    <!-- Clientseitiges JS: Bootstrap Bundle für interaktive Komponenten -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
