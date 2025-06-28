<?php
require 'config.php'; // Verbindet das Skript mit der Datei config.php, die die Datenbankverbindung (PDO) enthält

// Anzahl der Einträge, die pro Seite angezeigt werden sollen (für Paginierung)
$items_per_page = 8; // 8 Bücher pro Seite anzeigen

// Aktuelle Seite aus URL auslesen, wenn nicht vorhanden, dann 1
// (int) konvertiert in ganze Zahl, max(1, ...) verhindert, dass Seitenzahl kleiner als 1 wird
$current_page    = max(1, (int)($_GET['seite'] ?? 1));

// Offset berechnen, also wie viele Einträge übersprungen werden müssen
// Für die Datenbankabfrage: z.B. Seite 2 überspringt 8 Einträge (1. Seite)
$offset          = ($current_page - 1) * $items_per_page;

// Suchbegriff aus der URL holen, falls vorhanden (sonst leerer String)
// Null-Koaleszenz-Operator ?? bedeutet: wenn $_GET['search'] nicht gesetzt, dann ''
$search = $_GET['search'] ?? '';

try {
    // SQL-Anweisung vorbereiten, um Bücher inkl. Kategorie zu holen
    // SQL_CALC_FOUND_ROWS: Zählt die Gesamtanzahl der Treffer ohne LIMIT
    // JOIN kategorien ON buecher.kategorie = kategorien.id verbindet Tabellen (relational)
    // WHERE ... LIKE :search sucht nach Titeln mit dem Suchbegriff
    // LIMIT :offset, :limit bestimmt Paginierung (ab wo und wie viele Datensätze)

    /*pdo -> prepare : Bereitet eine SQL-Abfrage vor, damit man 
    später sicher Werte einfügen kann (Schutz vor SQL-Injection).*/
    $stmt = $pdo->prepare(
        "SELECT SQL_CALC_FOUND_ROWS buecher.*, kategorien.kategorie AS kategorie_name
         FROM buecher
         JOIN kategorien ON buecher.kategorie = kategorien.id
         WHERE buecher.Title LIKE :search
         LIMIT :offset, :limit"
    );
    // Bind Value bindet einen konkreten Wert direkt an einen Platzhalter im SQL-Statement – also den Wert jetzt sofort
    // :search mit Wildcards versehen (z.B. '%Harry%') für LIKE-Suche
    // PDO::PARAM_STR = Datenbankparameter als String übergeben

    /*Der Code bindet Such- und Paginierungswerte (Suchbegriff, Startposition, Anzahl) 
    sicher in eine vorbereitete SQL-Abfrage ein und führt sie dann aus. */

    $stmt->bindValue(':search',  "%$search%", PDO::PARAM_STR);
//panierung die zwei unten
    // Offset (Startposition) als Integer an die Abfrage binden
    $stmt->bindValue(':offset',  $offset, PDO::PARAM_INT);

    // Limit (Anzahl der Einträge) als Integer binden
    $stmt->bindValue(':limit',   $items_per_page, PDO::PARAM_INT);

    // SQL-Abfrage ausführen
    $stmt->execute();

    // Alle Ergebnisse als Array holen (jede Zeile als assoziatives Array)
    $books = $stmt->fetchAll();

    // Gesamtanzahl aller passenden Einträge abfragen (ohne LIMIT)
    // FOUND_ROWS() ist eine MySQL-Funktion, die die Anzahl der Zeilen aus vorheriger Abfrage zurückgibt
    $total       = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn(); // fetchColumm Holt einen einzelnen Wert aus der Datenbank 

    // Anzahl der Seiten berechnen (aufrunden mit ceil)
    $total_pages = ceil($total / $items_per_page);

} catch (PDOException $e) {
    // Fehler beim Zugriff auf Datenbank abfangen und ausgeben (z.B. Verbindungsfehler)
    die("Datenbankfehler: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Buchverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #F9FFE1; }
        .book-table { background: white; border-radius: 10px; }
        .zustand-badge { min-width: 70px; }
    </style>
</head>
<body>
<header class="p-3 text-bg-dark mb-4">
  <div class="container d-flex justify-content-between align-items-center">
    <h3 class="text-white m-0">Buchverwaltung</h3>
    <a href="dashboard.php" class="btn btn-outline-light">Zurück zum Dashboard</a>
  </div>
</header>

    <div class="container py-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="input-group">
                    <!-- Suchfeld für Bücher -->
                    <input type="text" 
                           name="search" 
                           class="form-control"
                           placeholder="Bücher suchen..."
                           value="<?= htmlspecialchars($search) ?>"> <!-- htmlspecialchars schützt vor XSS
                           XSS ist, wenn böse Leute schädlichen Code in eine Webseite schreiben, um anderen zu schaden. -->
                    <button class="btn btn-primary">Suchen</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="buch_erstellen.php" class="btn btn-success">Neues Buch</a>
            </div>
        </div>

        <div class="book-table p-4 shadow">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Titel</th>
                        <th>Autor</th>
                        <th>Kategorie</th>
                        <th>Nummer</th>
                        <th>Zustand</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['Title']) ?></td> <!-- Titel des Buches -->
                        <td><?= htmlspecialchars($book['autor']) ?></td> <!-- Autor des Buches -->
                        <td><?= htmlspecialchars($book['kategorie_name']) ?></td> <!-- Name der Kategorie -->
                        <td><?= htmlspecialchars($book['nummer']) ?></td> <!-- Buchnummer -->
                        <td>
                            <span class="badge zustand-badge bg-<?= match($book['zustand']) {
                                // Match-Ausdruck für die Farbe des Zustands (PHP 8+)
                                'G' => 'success',  // Gut = grün
                                'M' => 'warning',  // Mittel = gelb/orange
                                'S' => 'danger',   // Schlecht = rot
                                default => 'secondary' // Andere Zustände = grau
                            } ?>">
                                <?= htmlspecialchars($book['zustand']) ?> <!-- Zustand als Text -->
                            </span>
                        </td>
                        <td>
                            <a href="edit_book.php?id=<?= $book['id'] ?>" 
                               class="btn btn-sm btn-primary">Bearbeiten</a>
                            <form method="post" 
                                  action="delete_book.php" 
                                  class="d-inline"
                                  onsubmit="return confirm('Buch wirklich löschen?')">
                                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="container mt-4">
        <div class="d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                <?php
                    // Parameter für Links mit Suchbegriff, damit Suche bei Navigation erhalten bleibt
                    //& steht hier für den zweiten oder weiteren URL-Parameter in einem Link.
                    /*Damit Sonderzeichen (wie Leerzeichen, Umlaute) sicher in der URL übertragen werden.
                    Zum Beispiel wird "rote Bücher" zu "rote+B%C3%BCcher"*/
                    $pagination_params = "&search=" . urlencode($search);

                    // Link zur ersten Seite
                    echo '<li class="page-item"><a class="page-link" href="?seite=1' . $pagination_params . '">Erste</a></li>';

                    // Link zur vorherigen Seite, wenn Seite > 1
                    if ($current_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?seite=' . ($current_page-1) . $pagination_params . '">Zurück</a></li>';
                    }

                    // Anzeige der aktuellen Seite (nicht als Link)
                    echo '<li class="page-item active"><span class="page-link">Seite ' . $current_page . '</span></li>';

                    // Link zur nächsten Seite, wenn noch weitere Seiten existieren
                    if ($current_page < $total_pages) {
                        echo '<li class="page-item"><a class="page-link" href="?seite=' . ($current_page+1) . $pagination_params . '">Weiter</a></li>';
                    }

                    // Link zur letzten Seite
                    echo '<li class="page-item"><a class="page-link" href="?seite=' . $total_pages . $pagination_params . '">Letzte</a></li>';
                ?>
                </ul>
            </nav>
        </div>
    </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
