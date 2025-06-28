<?php
session_start(); 
// Serverseitig: Session starten, um z.B. Login-Status zu verwalten.
// Jede Session ist ein individueller Speicherplatz pro Nutzer (Browser).

require 'config.php'; 
// Serverseitig: Lädt die Datei config.php, die vermutlich die PDO-Datenbankverbindung ($pdo) enthält.

if (!isset($_SESSION['benutzername'])) {
    // Serverseitig: Prüft, ob ein Benutzer eingeloggt ist, indem die Session-Variable gesetzt ist.
    header("Location: login.php"); 
    // Server sendet an den Browser eine HTTP-Weiterleitung auf die Login-Seite.
    exit(); 
    // Wichtig: Skript sofort beenden, damit kein nachfolgender Code ausgeführt wird.
}

if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) {
    // Serverseitig: Prüft, ob ein Parameter "ID" per GET übergeben wurde und ob dieser eine Zahl ist.
    die("Ungültige Benutzer-ID."); 
    // Wenn nicht, wird das Skript abgebrochen und eine Fehlermeldung ausgegeben.
}

$user_id = (int)$_GET['ID']; 
// Serverseitig: Holt die ID aus der URL (GET) und wandelt sie in eine Ganzzahl (Integer) um 
// — Schutz vor SQL-Injection oder falschen Werten.

$stmt = $pdo->prepare("SELECT * FROM benutzer WHERE ID = ?"); 
// Serverseitig: Bereitet eine SQL-Abfrage vor, um den Benutzer mit der angegebenen ID zu finden. 
// Das Fragezeichen ist ein Platzhalter für späteren Wert.

$stmt->execute([$user_id]); 
// Serverseitig: Führt die vorbereitete Abfrage aus und übergibt den Parameter $user_id.

$user = $stmt->fetch(); 
// Serverseitig: Holt den ersten gefundenen Datensatz (Benutzer) als assoziatives Array.

if (!$user) {
    die("Benutzer nicht gefunden."); 
    // Wenn kein Benutzer mit dieser ID existiert, Skript abbrechen und Meldung zeigen.
}

$error = ""; 
// Serverseitig: Variable, um Fehler-Meldungen (z.B. Passwort stimmt nicht) zu speichern.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Serverseitig: Prüft, ob das Formular abgeschickt wurde (HTTP-Methode POST).

    // Formulardaten auslesen (aus POST):
    $benutzername = $_POST['benutzername'];
    $name = $_POST['name'];
    $vorname = $_POST['vorname'];
    $email = $_POST['email'];
    $admin = isset($_POST['admin']) ? 1 : 0; 
    // Checkbox: Wenn gesetzt, dann 1 (Admin), sonst 0.

    $neues_passwort = $_POST['passwort'];
    $bestaetigung = $_POST['passwort_bestaetigen'];

    if (!empty($neues_passwort)) {
        // Serverseitig: Falls ein neues Passwort eingegeben wurde,

        if ($neues_passwort !== $bestaetigung) {
            $error = "Die Passwörter stimmen nicht überein."; 
            // Passwort-Bestätigung stimmt nicht mit Passwort überein
        } else {
            $passwort_hash = password_hash($neues_passwort, PASSWORD_BCRYPT);
            // Serverseitig: Passwort wird mit BCRYPT gehasht (sicher verschlüsselt).
            //$pdo->prepare(...) bereitet eine SQL-Anfrage vor, bevor Daten eingefügt werden.
            $stmt = $pdo->prepare("UPDATE benutzer SET benutzername=?, name=?, vorname=?, email=?, passwort=?, admin=? WHERE ID=?");
            // Bereitet SQL-Update vor, um alle Daten inklusive Passwort zu aktualisieren.

            $stmt->execute([$benutzername, $name, $vorname, $email, $passwort_hash, $admin, $user_id]);
            // Führt das Update mit den neuen Werten aus.

            header("Location: erfolgreich.php"); 
            // Nach erfolgreichem Update Weiterleitung zur Bestätigungsseite.

            exit(); 
            // Skript beenden, damit kein weiterer Code ausgeführt wird.
        }
    }

    if (empty($neues_passwort)) {
        // Wenn kein neues Passwort gesetzt wurde, aktualisiere nur die anderen Daten.
        //$pdo->prepare(...) bereitet eine SQL-Anfrage vor, bevor Daten eingefügt werden.
        $stmt = $pdo->prepare("UPDATE benutzer SET benutzername=?, name=?, vorname=?, email=?, admin=? WHERE ID=?");
        $stmt->execute([$benutzername, $name, $vorname, $email, $admin, $user_id]);
        // SQL-Update ohne Passwortfeld.

        header("Location: erfolgreich.php"); 
        exit();
    }
}
?>

<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8"> <!-- Zeichensatz: UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <!-- Responsive Design: Damit die Seite auf Mobilgeräten gut aussieht -->

    <title>Benutzer bearbeiten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Clientseitig: Bootstrap CSS für Styling und Layout -->

    <link rel="stylesheet" href="css.css"> 
    <!-- Zusätzlich eigenes CSS -->

    <style>
        /* Clientseitiges CSS Styling für Hintergrund und Container */
        body {
            background: #F9FFE1;
        }
        .kontakt-container {
            max-width: 600px; /* maximale Breite */
            margin: 2rem auto; /* zentriert mit Abstand */
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<div class="kontakt-container bg-light rounded shadow">
    <h1>Benutzer bearbeiten</h1>

    <?php if (!empty($error)): ?>
        <!-- Clientseitig: Fehler-Meldung anzeigen, wenn $error nicht leer ist -->
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3">
        <!-- Formular mit POST-Methode -->
        
        <div class="col-12">
            <label for="benutzername" class="form-label">Benutzername*</label>
            <input type="text" class="form-control" id="benutzername" name="benutzername" 
                   value="<?= htmlspecialchars($user['benutzername']) ?>" required>
            <!-- Pflichtfeld -->
        </div>

        <div class="col-12">
            <label for="name" class="form-label">Nachname*</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="col-12">
            <label for="vorname" class="form-label">Vorname*</label>
            <input type="text" class="form-control" id="vorname" name="vorname" 
                   value="<?= htmlspecialchars($user['vorname']) ?>" required>
        </div>

        <div class="col-12">
            <label for="email" class="form-label">E-Mail</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= htmlspecialchars($user['email']) ?>">
            <!-- Optional -->
        </div>

        <div class="col-12">
            <label for="passwort" class="form-label">Neues Passwort (optional)</label>
            <input type="password" class="form-control" id="passwort" name="passwort">
        </div>

        <div class="col-12">
            <label for="passwort_bestaetigen" class="form-label">Passwort bestätigen</label>
            <input type="password" class="form-control" id="passwort_bestaetigen" name="passwort_bestaetigen">
        </div>

        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" id="admin" name="admin" <?= $user['admin'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="admin">Admin-Rechte</label>
        </div>

        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary btn-lg">Änderungen speichern</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Clientseitiges JavaScript für Bootstrap -->

</body>
</html>
