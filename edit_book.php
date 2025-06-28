<?php 
session_start(); 
// Startet die Session. Das ist nötig, um auf Sessiondaten wie z.B. Login-Daten zuzugreifen.

require 'config.php'; 
// Lädt die Datei config.php, die meist die Datenbank-Verbindung ($pdo) enthält.

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['benutzername'])) {
    // Wenn kein Benutzername in der Session ist, bedeutet das: nicht eingeloggt
    header("Location: login.php"); 
    // Weiterleitung auf die Login-Seite
    exit; 
    // Skript sofort beenden, damit kein nachfolgender Code ausgeführt wird
}

$benutzername = $_SESSION['benutzername']; 
// Benutzernamen aus der Session holen, um ihn weiter zu verwenden

// Adminrechte des Benutzers überprüfen
$stmt = $pdo->prepare("SELECT admin FROM benutzer WHERE benutzername = :benutzername");
// Bereitet eine SQL-Anfrage vor, um aus der Tabelle 'benutzer' zu prüfen, ob der Benutzer ein Admin ist.

$stmt->execute([':benutzername' => $benutzername]);
// Führt die Abfrage aus und ersetzt den Platzhalter ":benutzername" mit dem tatsächlichen Benutzernamen

$user = $stmt->fetch();
// Holt das Ergebnis als Array (z.B. ['admin' => 1])

if (!$user || !$user['admin']) {
    // Wenn kein Datensatz gefunden wurde ODER admin = 0 (kein Admin)
    header("Location: nur_fuer_admins.php"); 
    // Weiterleitung auf eine Seite, die nur Admins sehen dürfen
    exit; 
}

// Buch-ID aus der URL holen und in Integer umwandeln
$book_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
// Prüft, ob die URL einen Parameter "id" hat. Falls ja, wird er als ganze Zahl gespeichert.
// Falls nein, wird 0 gesetzt.

// Wenn die Buch-ID gültig ist (größer als 0)
if ($book_id > 0) {
    // Prüfen, ob das Formular abgeschickt wurde (Methode POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Prüfen, ob der Lösch-Button gedrückt wurde
        if (isset($_POST['delete'])) {
            // SQL-Abfrage zum Löschen des Buchs
            $sql = "DELETE FROM buecher WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$book_id])) {
                header('Location: change_successful.php'); // Erfolgreich gelöscht
                exit;
            } else {
                header('Location: change_failed.php'); // Fehler beim Löschen
                exit;
            }
        } else {
            // Wenn kein Löschen, dann wird das Buch aktualisiert

            // Daten aus dem Formular holen
            $Title = $_POST['Title'];
            $autor = $_POST['autor'];
            $kategorie = $_POST['kategorie'];
            $nummer = $_POST['nummer'];
            $zustand = $_POST['zustand'];

            // SQL-Update vorbereiten (Platzhalter ? für Sicherheit)
            $sql = "UPDATE buecher SET Title = ?, autor = ?, kategorie = ?, nummer = ?, zustand = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            // Update ausführen mit Werten aus dem Formular und der Buch-ID
            if ($stmt->execute([$Title, $autor, $kategorie, $nummer, $zustand, $book_id])) {
                header('Location: change_successful.php'); // Erfolgreich aktualisiert
                exit;
            } else {
                header('Location: change_failed.php'); // Fehler beim Aktualisieren
                exit;
            }
        }
    } else {
        // Wenn noch kein POST (Formular nicht abgeschickt), Daten aus DB holen, um Formular vorzubelegen
        $sql = "SELECT Title, autor, kategorie, nummer, zustand FROM buecher WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if ($book) {
            // Buchdaten in Variablen speichern, damit sie im Formular angezeigt werden können
            $Title = $book['Title'];
            $autor = $book['autor'];
            $kategorie = $book['kategorie'];
            $nummer = $book['nummer'];
            $zustand = $book['zustand'];
        } else {
            echo "Buch nicht gefunden."; // Kein Buch mit dieser ID in der DB
            exit;
        }
    }
} else {
    echo "Ungültige Buch-ID."; // ID nicht gesetzt oder ungültig
    exit;
}
?>



<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buch bearbeiten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css.css">
    <style>
      body {
        background: #F9FFE1;
      }
      .kontakt-container {
        max-width: 600px;
        margin: 2rem auto;
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
      <h1>Buch bearbeiten</h1>

      <form method="post" class="row g-3">
        <div class="col-12">
          <label for="Title" class="form-label">Titel*</label>
          <input type="text" class="form-control" id="Title" name="Title" value="<?= htmlspecialchars($Title) ?>" required>
        </div>
        <div class="col-12">
          <label for="autor" class="form-label">Autor*</label>
          <input type="text" class="form-control" id="autor" name="autor" value="<?= htmlspecialchars($autor) ?>" required>
        </div>
        <div class="col-12">
          <label for="kategorie" class="form-label">Kategorie*</label>
          <input type="text" class="form-control" id="kategorie" name="kategorie" value="<?= htmlspecialchars($kategorie) ?>" required>
        </div>
        <div class="col-12">
          <label for="nummer" class="form-label">Nummer*</label>
          <input type="text" class="form-control" id="nummer" name="nummer" value="<?= htmlspecialchars($nummer) ?>" required>
        </div>
        <div class="col-12">
          <label for="zustand" class="form-label">Zustand*</label>
          <input type="text" class="form-control" id="zustand" name="zustand" value="<?= htmlspecialchars($zustand) ?>" required>
        </div>

        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
        </div>
    </form>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>

