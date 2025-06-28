<?php
//Validierung bedeutet ganz einfach: Prüfen, ob die Eingabe richtig ist.
// Startet eine neue oder bestehende Session, um auf Session-Daten zugreifen zu können
session_start();

// Bindet die Datei ein, in der die Verbindung zur Datenbank (PDO) definiert ist
require 'config.php';

// Holt die Benutzer-ID aus der Session, falls vorhanden, ansonsten wird "1" als Standardwert verwendet
$default_verfasser = $_SESSION['user_id'] ?? 1;

// Prüft, ob ein Formular per POST gesendet wurde und ob POST-Daten vorhanden sind
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST)) {

    // Liest und überprüft Pflichtfelder aus dem Formular
    //wenn empty nicht null ist dann wird der als int katalog genommen wenn nicht dann null
    $katalog   = !empty($_POST['katalog']) ? (int)$_POST['katalog'] : null;         // Katalognummer
    $nummer    = !empty($_POST['nummer']) ? (int)$_POST['nummer'] : null;           // Buchnummer
    $Title     = $_POST['Title'] ?? '';                                             // Titel des Buches
    $autor     = $_POST['autor'] ?? '';                                             // Autor des Buches
    $kategorie = !empty($_POST['kategorie']) ? (int)$_POST['kategorie'] : null;     // Kategorie-ID
    $zustand   = $_POST['zustand'] ?? '';                                           // Zustand des Buches

    // Optionale Felder mit Standardwerten
    $verkauft  = !empty($_POST['verkauft']) ? (int)$_POST['verkauft'] : 0;          // 0 = nicht verkauft, 1 = verkauft
    $kaufer    = !empty($_POST['kaufer']) ? (int)$_POST['kaufer'] : null;           // Käufer-ID
    $verfasser = !empty($_POST['verfasser']) ? (int)$_POST['verfasser'] : $default_verfasser; // Verfasser-ID

    try {
        // Bereitet das SQL-INSERT-Statement mit Platzhaltern vor
        // $pdo->prepare Bereite eine SQL-Anweisung zur sicheren Ausführung vor.
        $stmt = $pdo->prepare("INSERT INTO buecher 
            (katalog, nummer, Title, autor, kategorie, zustand, verkauft, kaufer, verfasser) 
            VALUES 
            (:katalog, :nummer, :Title, :autor, :kategorie, :zustand, :verkauft, :kaufer, :verfasser)");

        // Bindet die Formularwerte an die Platzhalter im SQL-Statement
        // Gibt an, dass der gebundene SQL-Parameter ein String ist (Textwert).
        // Wird bei bindParam() oder bindValue() verwendet, um den Datentyp festzulegen.
        // Der Typ hilft PDO, den Wert korrekt an die Datenbank zu übergeben.
        /*PDO::PARAM_STR gibt an, dass der Wert ein String (also Text) ist. 
        Das braucht PHP, um den Wert korrekt an die Datenbank zu übergeben, 
        wenn man bindParam() oder bindValue() verwendet. */
        $stmt->bindParam(':katalog',   $katalog,   PDO::PARAM_INT);
        $stmt->bindParam(':nummer',    $nummer,    PDO::PARAM_INT);
        $stmt->bindParam(':Title',     $Title,     PDO::PARAM_STR);
        $stmt->bindParam(':autor',     $autor,     PDO::PARAM_STR);
        $stmt->bindParam(':kategorie', $kategorie, PDO::PARAM_INT);
        $stmt->bindParam(':zustand',   $zustand,   PDO::PARAM_STR);
        $stmt->bindParam(':verkauft',  $verkauft,  PDO::PARAM_INT);
        $stmt->bindParam(':kaufer',    $kaufer,    PDO::PARAM_NULL); // Achtung: PARAM_NULL wird ignoriert, wenn $kaufer kein NULL ist
        $stmt->bindParam(':verfasser', $verfasser, PDO::PARAM_INT);

        // Führt das SQL-Statement aus
        if ($stmt->execute()) {
            // Erfolgreich: Setzt eine Session-Nachricht und leitet zur Bücherverwaltung weiter
            $_SESSION['success'] = "Buch erfolgreich erstellt!";
            header("Location: manage_books.php");
            exit();
        }
    } catch (PDOException $e) {
        // Fehlerbehandlung: Speichert die Fehlermeldung in einer Variable und loggt sie
        $error = "Datenbankfehler: " . $e->getMessage();
        error_log($error); //Diese Zeile schreibt den Inhalt der Variablen $error in das Fehlerprotokoll des Servers.
    }
}
?>


<!DOCTYPE html>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Buch erstellen</title>

    <!-- Bootstrap-Stylesheet von CDN einbinden -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Eigene CSS-Stile für das Layout -->
    <style>
        body { background-color: #F9FFE1; }
        .form-container {
            max-width: 800px;
            background-color: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-label { font-weight: 500; }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<!-- Kopfzeile mit Titel und Link zur Bücherliste -->
<header class="p-3 mb-4" style="background-color: #2b2f32;">
    <div class="container d-flex justify-content-between align-items-center">
        <h3 class="text-white m-0">Buch erstellen</h3>
        <a href="manage_books.php" class="btn btn-outline-light">Zurück zur Bücherliste</a>
    </div>
</header>

<main>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="form-container">
                    <h1>Neues Buch erfassen</h1>

                    <!-- Falls ein Fehler vorliegt, wird er hier angezeigt -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <!-- Formular zum Erfassen eines neuen Buches -->
                    <form method="post">

                        <!-- Erste Zeile: Katalognummer, Buchnummer, Kategorie -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="katalog" class="form-label">Katalog Nr.</label>
                                <input type="number" class="form-control" id="katalog" name="katalog" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nummer" class="form-label">Buch Nr.</label>
                                <input type="number" class="form-control" id="nummer" name="nummer" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kategorie" class="form-label">Kategorie</label>
                                <select class="form-control" id="kategorie" name="kategorie" required>
                                    <option value="">Bitte wählen...</option>
                                    <!-- Kategorienauswahl -->
                                    <option value="1">1 – Alte Drucke, Bibeln ...</option>
                                    <option value="2">2 – Geographie und Reisen</option>
                                    <!-- ... weitere Kategorien -->
                                    <option value="14">14 – Old Books</option>
                                </select>
                            </div>
                        </div>

                        <!-- Titel -->
                        <div class="mb-3">
                            <label for="Title" class="form-label">Titel</label>
                            <input type="text" class="form-control" id="Title" name="Title" required maxlength="100">
                        </div>

                        <!-- Autor -->
                        <div class="mb-3">
                            <label for="autor" class="form-label">Autor</label>
                            <input type="text" class="form-control" id="autor" name="autor" required maxlength="100">
                        </div>

                        <!-- Zweite Zeile: Zustand, Verkauft, Käufer -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="zustand" class="form-label">Zustand</label>
                                <select class="form-select" id="zustand" name="zustand" required>
                                    <option value="M">M (Mittel)</option>
                                    <option value="S">S (Schlecht)</option>
                                    <option value="G">G (Gut)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="verkauft" class="form-label">Verkauft (0/1)</label>
                                <input type="number" class="form-control" id="verkauft" name="verkauft" min="0" max="1" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kaufer" class="form-label">Käufer ID</label>
                                <input type="number" class="form-control" id="kaufer" name="kaufer" min="0">
                            </div>
                        </div>

                        <!-- Verfasser -->
                        <div class="row">
                            <div class="col-12 text-left">
                                <label for="verfasser" class="form-label">Verfasser ID</label>
                                <input type="number" class="form-control" id="verfasser" name="verfasser"
                                       value="<?php echo htmlspecialchars($default_verfasser); ?>" min="0" required>
                            </div>
                        </div>

                        <!-- Absenden-Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                Buch speichern
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
