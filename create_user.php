<?php
session_start(); 
// Session starten oder fortsetzen, um Benutzerdaten wie Loginstatus zu speichern und nutzen zu können.

require 'config.php'; 
// Einbinden der Datei mit der Datenbankverbindung ($pdo). So können wir SQL-Abfragen machen.

// Zugriffskontrolle: Nur eingeloggte Admins dürfen Benutzer erstellen
if (!isset($_SESSION['benutzername'])) {
    // Prüfen, ob ein Benutzer eingeloggt ist (Session-Variable existiert)
    // Falls nicht, weiterleiten zur Login-Seite, da nur angemeldete Nutzer weiter dürfen
    header("Location: login.php");
    exit; // Skript hier beenden, damit keine weiteren Zeilen ausgeführt werden
}

$benutzername = $_SESSION['benutzername']; 
// Den eingeloggten Benutzernamen aus der Session in eine Variable speichern

// Prüfen, ob eingeloggter Benutzer ein Admin ist
$stmt = $pdo->prepare("SELECT admin FROM benutzer WHERE benutzername = :benutzername"); 
// Bereite eine SQL-Abfrage vor, um das Feld 'admin' für den Benutzer zu holen
// Verwendung eines benannten Parameters (:benutzername) schützt vor SQL-Injection

$stmt->execute([':benutzername' => $benutzername]);
// Führe die Abfrage aus und übergebe den Wert für den Platzhalter :benutzername

$user = $stmt->fetch();
// Hole das Ergebnis als assoziatives Array
// Falls Benutzer gefunden, enthält $user['admin'] den Wert (z.B. 1 für Admin, 0 für normaler User)

// Prüfen, ob der Benutzer Adminrechte besitzt
if (!$user || !$user['admin']) {
    // Wenn kein Datensatz gefunden wurde (kein Benutzer) oder der Benutzer kein Admin ist,
    // dann umleiten auf eine Erfolgsseite oder eine Seite, die den Zugriff verweigert
    header("Location: erfolgreich.php");
    exit(); // Skript beenden, kein Zugriff
}

// Maximale und minimale Feldlängen definieren als Konstanten
define('MAX_BENUTZERNAME', 45);
define('MAX_NAME', 100);
define('MAX_VORNAME', 100);
define('MAX_EMAIL', 255);
define('MAX_PASSWORT', 72);  // Technische Grenze für bcrypt-Hash
define('MIN_PASSWORT', 8);   // Sicherheitsanforderung: Passwort mindestens 8 Zeichen

// Variablen für Fehlermeldungen und Erfolgsmeldungen vorbereiten
$error = "";
$success = "";

// Variablen zum Vorbefüllen der Formularfelder initialisieren
$benutzername_input = '';
$name = '';
$vorname = '';
$email = '';
$admin_flag = 0; // 0 = kein Admin, 1 = Admin

// Prüfen, ob Formular abgeschickt wurde (HTTP POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzereingaben holen und mit trim() Leerzeichen am Anfang/Ende entfernen
    $benutzername_input = trim($_POST['benutzername'] ?? ''); // ?? '' sorgt für leeren String, falls Feld fehlt
    $name               = trim($_POST['name'] ?? '');
    $vorname            = trim($_POST['vorname'] ?? '');
    $email              = trim($_POST['email'] ?? '');
    $passwort           = $_POST['passwort'] ?? '';
    $passwort_bestaetigen = $_POST['passwort_bestaetigen'] ?? '';
    $admin_flag         = isset($_POST['admin']) ? 1 : 0; 
    // Checkbox für Adminrechte: wenn gesetzt, dann 1, sonst 0

    $errors = []; // Array zum Sammeln von Fehlermeldungen

    // Pflichtfelder prüfen - serverseitige Validierung
    if (empty($benutzername_input)) $errors[] = "Benutzername ist erforderlich.";
    if (empty($name))               $errors[] = "Nachname ist erforderlich.";
    if (empty($vorname))            $errors[] = "Vorname ist erforderlich.";
    if (empty($passwort))           $errors[] = "Passwort ist erforderlich.";

    // Maximale und minimale Längen prüfen - wichtige serverseitige Validierung
    if (strlen($benutzername_input) > MAX_BENUTZERNAME) $errors[] = "Benutzername zu lang (max. " . MAX_BENUTZERNAME . " Zeichen).";
    if (strlen($name) > MAX_NAME)                       $errors[] = "Nachname zu lang (max. " . MAX_NAME . " Zeichen).";
    if (strlen($vorname) > MAX_VORNAME)                 $errors[] = "Vorname zu lang (max. " . MAX_VORNAME . " Zeichen).";
    if (strlen($email) > MAX_EMAIL)                     $errors[] = "E-Mail zu lang (max. " . MAX_EMAIL . " Zeichen).";
    if (strlen($passwort) > MAX_PASSWORT)               $errors[] = "Passwort zu lang (max. " . MAX_PASSWORT . " Zeichen).";
    if (strlen($passwort) < MIN_PASSWORT)               $errors[] = "Passwort muss mindestens " . MIN_PASSWORT . " Zeichen lang sein.";

    // Validierung: Passwort und Bestätigung müssen gleich sein
    if ($passwort !== $passwort_bestaetigen)            $errors[] = "Die Passwörter stimmen nicht überein.";

    // Wenn E-Mail ausgefüllt ist, prüfen, ob das Format gültig ist
    //FILTER_VALIDATE_EMAIL prüft, ob ein Text eine gültige E-Mail-Adresse ist.
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Ungültige E-Mail-Adresse.";

    // Nur wenn keine Fehler aufgetreten sind, Benutzer anlegen
    if (empty($errors)) {
        // Prüfen, ob Benutzername bereits in der Datenbank existiert
        $check = $pdo->prepare("SELECT ID FROM benutzer WHERE benutzername = ?");
        $check->execute([$benutzername_input]);

        if ($check->fetch()) {
            // Benutzername existiert bereits - Fehlermeldung hinzufügen
            $errors[] = "Benutzername existiert bereits.";
        } else {
            // Passwort sicher mit bcrypt hashen (serverseitige Sicherheitsmaßnahme)
            $passwort_hash = password_hash($passwort, PASSWORD_BCRYPT);

            // SQL-Anweisung zum Einfügen eines neuen Benutzers vorbereiten (Prepared Statement)
            $stmt = $pdo->prepare("
                INSERT INTO benutzer (benutzername, name, vorname, email, passwort, admin)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // SQL ausführen mit den eingegebenen und verarbeiteten Werten
            if ($stmt->execute([$benutzername_input, $name, $vorname, $email, $passwort_hash, $admin_flag])) {
                // Erfolg: Benutzer wurde angelegt
                $success = "✅ Benutzer wurde erfolgreich erstellt.";

                // Formularfelder zurücksetzen, damit die Form sauber bleibt für neuen Eintrag
                $benutzername_input = $name = $vorname = $email = '';
                $admin_flag = 0;
            } else {
                // Datenbankfehler beim Einfügen
                $errors[] = "❌ Datenbankfehler beim Erstellen des Benutzers.";
            }
        }
    }

    // Wenn Fehler vorhanden, in eine HTML-formatierte Fehlermeldung umwandeln
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>



<!doctype html>
<!-- Dokumenttyp definieren: HTML5 -->

<html lang="de">
<!-- Start des HTML-Dokuments, Sprache ist Deutsch -->

  <head>
  <!-- Kopfbereich: Metainformationen und Ressourcen für die Seite -->

    <meta charset="utf-8">
    <!-- Zeichencodierung auf UTF-8 setzen, unterstützt alle Sonderzeichen -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Damit die Seite auf mobilen Geräten korrekt skaliert wird -->

    <title>Benutzer erstellen</title>
    <!-- Seitentitel, der im Browser-Tab angezeigt wird -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS von CDN einbinden für fertige Styles und Responsive Design -->

    <style>
      /* Eigene CSS-Regeln für das Aussehen */

      body {
        background: #F9FFE1;
        /* Hintergrundfarbe des gesamten Körpers der Seite */
      }

      .kontakt-container {
        max-width: 600px;
        /* Maximale Breite des Containers, damit das Formular nicht zu breit wird */

        margin: 2rem auto;
        /* Außenabstand oben und unten 2rem, links/rechts automatisch zentriert */

        padding: 20px;
        /* Innenabstand rundherum für Abstand vom Rand */
      }

      h1 {
        text-align: center;
        /* Überschrift zentriert */

        margin-bottom: 2rem;
        /* Abstand unter der Überschrift */

        color: #2c3e50;
        /* Textfarbe der Überschrift */
      }

      .required:after {
        content: " *";
        /* Nach Elementen mit Klasse "required" wird ein Sternchen eingefügt */

        color: red;
        /* Sternchen in rot, um Pflichtfeld anzuzeigen */
      }

      .password-info {
        font-size: 0.8rem;
        /* Kleinere Schriftgröße für Info-Text unter Passwortfeld */

        color: #6c757d;
        /* Graue Schriftfarbe für dezente Anzeige */
      }
    </style>

  </head>

  <body>
  <!-- Hauptbereich der Seite -->

  <header class="p-3 text-bg-dark mb-4">
  <!-- Kopfbereich der Seite mit Bootstrap-Klassen:
       p-3 = Padding rundherum 1rem,
       text-bg-dark = dunkler Hintergrund mit weißem Text,
       mb-4 = Margin Bottom 1.5rem -->

    <div class="container d-flex justify-content-between align-items-center">
    <!-- Bootstrap Container zentriert Inhalt und gibt fixen Rand;
         d-flex = Flexbox Layout,
         justify-content-between = Elemente links und rechts verteilt,
         align-items-center = vertikal zentriert -->

      <h3 class="text-white m-0">Benutzer erstellen</h3>
      <!-- Überschrift im Header mit weißer Schrift und ohne Margin -->

      <a href="benutzer.php" class="btn btn-outline-light">Zurück zum Benutzer</a>
      <!-- Link-Button mit hell umrandetem Stil, führt zurück zur Benutzerliste -->
    </div>

  </header>

    <div class="kontakt-container bg-light rounded shadow">
    <!-- Container für das Formular mit Hintergrund hell, abgerundeten Ecken und Schatten -->

      <h1>Benutzer erstellen</h1>
      <!-- Hauptüberschrift -->

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <!-- PHP-Block: Falls Fehler vorliegen, werden diese hier in rotem Bootstrap-Alert angezeigt.
             htmlspecialchars schützt vor XSS-Angriffen -->
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <!-- PHP-Block: Erfolgsmeldung in grünem Alert, wenn Benutzer erfolgreich erstellt -->
      <?php endif; ?>

      <form method="post" class="row g-3" onsubmit="return validatePassword()">
      <!-- Formular, das per POST an dieselbe Seite gesendet wird
           row g-3 = Bootstrap Grid mit Abstand zwischen Feldern
           onsubmit ruft JS-Funktion zur Passwortvalidierung auf und kann Absenden verhindern -->

        <div class="col-12">
          <label for="benutzername" class="form-label required">Benutzername</label>
          <!-- Label für Eingabefeld, mit rotem Stern (Klasse required) -->

          <input type="text" class="form-control" id="benutzername" name="benutzername" 
                 value="<?= htmlspecialchars($benutzername_input) ?>" 
                 maxlength="<?= MAX_BENUTZERNAME ?>" required>
          <!-- Texteingabe für Benutzername
               value mit PHP vorbefüllt (HTML-sicher gemacht)
               maxlength für maximale Zeichenanzahl
               required für browserseitige Pflichtfeldvalidierung -->
        </div>

        <div class="col-12">
          <label for="name" class="form-label required">Nachname</label>
          <input type="text" class="form-control" id="name" name="name" 
                 value="<?= htmlspecialchars($name) ?>" 
                 maxlength="<?= MAX_NAME ?>" required>
          <!-- Nachname-Eingabe wie oben -->
        </div>

        <div class="col-12">
          <label for="vorname" class="form-label required">Vorname</label>
          <input type="text" class="form-control" id="vorname" name="vorname" 
                 value="<?= htmlspecialchars($vorname) ?>" 
                 maxlength="<?= MAX_VORNAME ?>" required>
          <!-- Vorname-Eingabe -->
        </div>

        <div class="col-12">
          <label for="email" class="form-label">E-Mail</label>
          <input type="email" class="form-control" id="email" name="email" 
                 value="<?= htmlspecialchars($email) ?>" 
                 maxlength="<?= MAX_EMAIL ?>">
          <!-- Email-Eingabe mit Typ "email" (Browser prüft Format automatisch) -->
        </div>

        <div class="col-12">
          <label for="passwort" class="form-label required">Passwort</label>
          <input type="password" class="form-control" id="passwort" name="passwort" 
                 maxlength="<?= MAX_PASSWORT ?>" minlength="<?= MIN_PASSWORT ?>" required>
          <!-- Passwort-Eingabe (versteckt)
               minlength für minimale Länge
               maxlength für maximale Länge
               required für Pflichtfeld -->
          <div class="password-info">Mindestens <?= MIN_PASSWORT ?> Zeichen</div>
          <!-- Hinweis unter dem Passwortfeld -->
        </div>

        <div class="col-12">
          <label for="passwort_bestaetigen" class="form-label required">Passwort bestätigen</label>
          <input type="password" class="form-control" id="passwort_bestaetigen" name="passwort_bestaetigen" 
                 maxlength="<?= MAX_PASSWORT ?>" minlength="<?= MIN_PASSWORT ?>" required>
          <!-- Passwortbestätigung wie oben -->
        </div>

        <div class="form-check col-12">
          <input class="form-check-input" type="checkbox" id="admin" name="admin" <?= $admin_flag ? 'checked' : '' ?>>
          <!-- Checkbox, um Adminrechte zu vergeben.
               PHP prüft, ob admin_flag gesetzt ist und macht dann checkbox checked -->

          <label class="form-check-label" for="admin">Admin-Rechte</label>
          <!-- Beschriftung für Checkbox -->
        </div>

        <div class="col-12 text-center">
          <button type="submit" class="btn btn-success btn-lg">Benutzer erstellen</button>
          <!-- Großer grüner Button zum Absenden des Formulars -->
        </div>

      </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap JavaScript Bundle (für interaktive Komponenten, Dropdowns, Modals etc.) -->

    <script>
      function validatePassword() { /*document.getElementById holt ein HTML-Element
         anhand seiner ID, damit man damit in JavaScript arbeiten kann. */


        // Funktion, die beim Absenden des Formulars ausgeführt wird (clientseitige Validierung)

        const password = document.getElementById('passwort').value;
        // Wert des Passwortfeldes holen

        const confirmPassword = document.getElementById('passwort_bestaetigen').value;
        // Wert des Passwort-Bestätigungsfeldes holen

        if (password.length < <?= MIN_PASSWORT ?>) {
          alert('Passwort muss mindestens <?= MIN_PASSWORT ?> Zeichen lang sein');
          // Warnung anzeigen, wenn Passwort zu kurz ist

          return false;
          // Absenden verhindern
        }

        if (password !== confirmPassword) {
          alert('Die Passwörter stimmen nicht überein');
          // Warnung, wenn Passwort und Bestätigung nicht übereinstimmen

          return false;
          // Absenden verhindern
        }

        return true;
        // Validierung OK, Formular absenden
      }
    </script>

  </body>
</html>
