<?php
include 'mysqli.php';

// 2. Filter, Sortierung, Suchbegriff und Seitennummer holen
// Liest den Parameter "sort" aus der URL. Falls dieser nicht gesetzt ist, wird als Standardwert 'Title' verwendet.
$sort = $_GET['sort'] ?? 'Title';
// Liest den Parameter "filter" aus der URL. Falls nicht gesetzt, wird der Standardwert 'alle' verwendet.
$filter = $_GET['filter'] ?? 'alle';
// Liest den Parameter "search" aus der URL. Falls nicht gesetzt, wird ein leerer String verwendet.
$search = $_GET['search'] ?? '';
// Liest den Parameter "seite" aus der URL. Falls nicht gesetzt, wird standardmäßig die Seite 1 genutzt.
$seite = $_GET['seite'] ?? 1;
// Definiert die Anzahl der Bücher, die pro Seite angezeigt werden sollen.
$buecher_pro_seite = 12;

// 3. SQL-Query mit Prepared Statements für sichere Filterung
// Erstellt die Grundabfrage, um die Gesamtzahl der Datensätze zu ermitteln.
// Es werden die Tabellen "buecher" und "kategorien" über den JOIN verknüpft.
// "WHERE 1=1" ermöglicht das einfache Anhängen weiterer Bedingungen.
$sql_count = "SELECT COUNT(*) AS total 
              FROM buecher 
              JOIN kategorien ON buecher.kategorie = kategorien.id 
              WHERE 1=1";

// Initialisiert eine leere Zeichenkette für die Parameter-Typen und ein leeres Array für die Parameterwerte.
$param_types = ""; 
$params = [];

// Falls ein Filter gesetzt wurde (und nicht 'alle' ist), wird die Abfrage um die Bedingung für den Zustand erweitert.
if ($filter != 'alle') {
    $sql_count .= " AND zustand = ?";
    // Der Parameter hat den Typ String ("s").
    $param_types .= "s";
    // Fügt den Filterwert dem Array der Parameter hinzu.
    $params[] = $filter;
}

// Falls ein Suchbegriff eingegeben wurde, wird die Abfrage um Bedingungen erweitert, die in den Spalten Title, Autor oder der Kategorie suchen.
if (!empty($search)) {
    $sql_count .= " AND (Title LIKE ? OR Autor LIKE ? OR kategorien.kategorie LIKE ?)";
    // Drei Parameter vom Typ String ("s") werden benötigt.
    $param_types .= "sss";
    // Bereitet den Suchbegriff mit Wildcards für die LIKE-Abfrage vor.
    $search_param = "%$search%";
    // Fügt den vorbereiteten Suchbegriff für Title hinzu.
    $params[] = $search_param;
    // Fügt den vorbereiteten Suchbegriff für Autor hinzu.
    $params[] = $search_param;
    // Fügt den vorbereiteten Suchbegriff für die Kategorie hinzu.
    $params[] = $search_param;
}

// Bereitet das SQL-Statement vor, um SQL-Injektionen zu verhindern.
$stmt = $conn->prepare($sql_count);
// Falls Parameter gesetzt wurden, bindet diese an das Statement.
if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}
//Führt das vorbereitete Statement aus.
//Die Variablen LIMIT und OFFSET werden mit bind_param übergeben.
//die Seitenanzahl mit Paginierung (pagination) organisiert
//Offset sagt der Datenbank, ab welchem Ergebnis sie anzeigen soll
$stmt->execute();
// Holt das Ergebnis der Abfrage.
$result = $stmt->get_result();
// Liest die Gesamtanzahl der Datensätze aus dem Ergebnis aus.
$total = $result->fetch_assoc()['total'];
// Berechnet die Gesamtzahl der Seiten, indem die Gesamtanzahl der Datensätze durch die Anzahl pro Seite geteilt wird und aufgerundet wird.
$gesamt_seiten = ceil($total / $buecher_pro_seite);//ceil : zahl aufrunden

// 4. Bücher-Abfrage mit Prepared Statements
// Berechnet den Startwert für die LIMIT-Klausel, basierend auf der aktuellen Seite.
$start = ($seite - 1) * $buecher_pro_seite;

// Erstellt die Grundabfrage, um die Buchdaten inklusive der Kategoriebezeichnung ("kategorie_text") abzurufen.
// Auch hier werden die Tabellen "buecher" und "kategorien" über einen JOIN verbunden.
$sql = "SELECT buecher.*, kategorien.kategorie AS kategorie_text 
        FROM buecher 
        JOIN kategorien ON buecher.kategorie = kategorien.id 
        WHERE 1=1";

// Initialisiert erneut die Variablen für die Parameter-Typen und -Werte.
$param_types = ""; 
$params = [];

// Falls ein Filter gesetzt wurde (nicht 'alle'), wird die Abfrage um die Zustand-Bedingung erweitert.
if ($filter != 'alle') {
    $sql .= " AND zustand = ?";  // Filter für 'zustand'
    $param_types .= "s";         // Typ für 'zustand' ist string
    $params[] = $filter;         // Der tatsächliche Filterwert wird zu den Parametern hinzugefügt
}


// Falls ein Suchbegriff vorhanden ist, wird die Abfrage um Bedingungen erweitert, die in den Spalten Title, Autor oder der Kategorie suchen.
//die Abfrage, um nach einem Suchbegriff in Title, Autor und kategorie zu suchen.
if (!empty($search)) {
    $sql .= " AND (Title LIKE ? OR Autor LIKE ? OR kategorien.kategorie LIKE ?)";
    $param_types .= "sss";
    // Verwendet den bereits vorbereiteten Suchparameter.
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Fügt eine ORDER BY-Klausel hinzu, um die Ergebnisse nach dem im Parameter "sort" angegebenen Kriterium zu sortieren.
// Anschließend wird mittels LIMIT die Anzahl der Ergebnisse begrenzt, die ab dem berechneten Startwert abgerufen werden.
$sql .= " ORDER BY $sort LIMIT ?, ?";
// Fügt zwei Integer-Parameter ("ii") für LIMIT hinzu.
$param_types .= "ii";
// Bindet den Startwert für die Abfrage (Offset) an das Parameter-Array.
$params[] = $start;
// Bindet die Anzahl der Ergebnisse pro Seite an das Parameter-Array.
$params[] = $buecher_pro_seite;

// Bereitet das SQL-Statement für die Bücherabfrage vor.
$stmt = $conn->prepare($sql);
// Bindet alle gesammelten Parameter an das Statement.
$stmt->bind_param($param_types, ...$params);
// Führt das Statement aus.
$stmt->execute();
// Holt das Ergebnis der ausgeführten Abfrage.
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css.css">
    <style>
        .book-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 15px;
            text-align: center;
            margin-bottom: -10px; /* Abstand zwischen den Karten in der Höhe */

        }
        .filter-box {
            background: #F9FFE1;
            padding: 20px;
            border-radius: 2px;
            margin-bottom: 20px;
        }
        body {
            background: #F9FFE1; /* Hintergrundfarbe anpassen */
        }
    </style>
</head>
<body>

<header class="p-3 text-bg-dark">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="index.php" class="nav-link px-2 text-white">Home</a></li>
                <li><a href="Kontakt.php" class="nav-link px-2 text-white">Kontakt</a></li>
                <li><a href="#" class="nav-link px-2 text-secondary">Books</a></li>
            </ul>
            
            <div class="text-end">
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

<!-- SUCHMASCHINE, FILTER & SORTIERUNG -->
<div class="container mt-4">
    <div class="filter-box">
        <form method="GET">
            <input type="hidden" name="seite" value="1">
            <div class="row g-4">
                <!-- SUCHFELD (Links) -->
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Titel, Autor oder Kategorie" 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <!-- SORTIEREN DROPDOWN -->
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="Title" <?= $sort == 'Title' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="Title DESC" <?= $sort == 'Title DESC' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="Autor" <?= $sort == 'Autor' ? 'selected' : '' ?>>Autor (A-Z)</option>
                        <option value="Autor DESC" <?= $sort == 'Autor DESC' ? 'selected' : '' ?>>Autor (Z-A)</option>
                    </select>
                </div>

                <!-- FILTER DROPDOWN -->
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="alle" <?= $filter == 'alle' ? 'selected' : '' ?>>Alle Zustand</option>
                        <option value="G" <?= $filter == 'G' ? 'selected' : '' ?>>Gut</option>
                        <option value="M" <?= $filter == 'M' ? 'selected' : '' ?>>Mittel</option>
                        <option value="S" <?= $filter == 'S' ? 'selected' : '' ?>>Schlecht</option>
                    </select>
                </div>
                
                <!-- ANWENDEN BUTTON -->
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Anwenden</button>
                </div>
            </div>
        </form>
    </div>

    <!-- BÜCHERLISTE -->
    <div class="row g-5">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="col-md-4">';
                echo '  <div class="book-card">';
                echo '    <a href="detail.php?id='.$row['id'].'" class="text-decoration-none text-dark">';
                echo '      <img src="Bild/Book.jpg" class="book-image w-100" alt="Buchbild">';
                echo '      <h5>Titel: '.$row['Title'].'</h5>';
                echo '      <p>Autor: '.$row['autor'].'</p>';
                echo '      <p>Kategorien: '.$row['kategorie_text'].'</p>';
                echo '    </a>';
                echo '  </div>';
                echo '</div>';
            }
        } else {
            echo '<div class="alert alert-warning">Keine Bücher gefunden</div>';
        }
        ?>
    </div>

    <!-- PAGINIERUNG -->
    <div class="container mt-4">
        <div class="d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                    <?php
                    // Parameter für Pagination-Links (inkl. Suchparameter)
                    //Der Code erstellt eine URL mit Parametern für Filter, Sortierung und Suche, die die angezeigten Ergebnisse steuern.
                    $pagination_params = "&filter=$filter&sort=$sort&search=" . urlencode($search);
                    
                    // Erste Seite
                    echo '<li class="page-item"><a class="page-link" href="?seite=1'.$pagination_params.'">Erste</a></li>';
                    
                    // Vorherige Seite
                    if ($seite > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?seite='.($seite-1).$pagination_params.'">Zurück</a></li>';
                    }
                    
                    // Aktuelle Seite
                    echo '<li class="page-item active"><span class="page-link">Seite '.$seite.'</span></li>';
                    
                    // Nächste Seite
                    if ($seite < $gesamt_seiten) {
                        echo '<li class="page-item"><a class="page-link" href="?seite='.($seite+1).$pagination_params.'">Weiter</a></li>';
                    }
                    
                    // Letzte Seite
                    echo '<li class="page-item"><a class="page-link" href="?seite='.$gesamt_seiten.$pagination_params.'">Letzte</a></li>';
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Spalte 1 -->
            <div class="col-md-4">
                <h5>Über uns</h5>
                <p>Wir lieben Bücher und teilen sie mit der Welt.</p>
            </div>
            <!-- Spalte 2 -->
            <div class="col-md-4">
                <h5>Links</h5>
                <ul class="list-unstyled">
                    <li><a href="ProjektWebsite.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="Kontakt.php" class="text-white text-decoration-none">Kontakt</a></li>
                    <li><a href="books.php" class="text-white text-decoration-none">Bücher</a></li>
                </ul>
            </div>
            <!-- Spalte 3 -->
            <div class="col-md-4">
                <h5>Kontakt</h5>
                <p>Email: info@buchseite.ch</p>
                <p>Telefon: +41 76 456 89</p>
            </div>
        </div>
        <hr class="border-light">
        <p class="mb-0">&copy; 2025 Buchseite. Alle Rechte vorbehalten.</p>
    </div>
</footer>

<?php
// 5. DATENBANKVERBINDUNG SCHLIESSEN
$conn->close();
?>
</body>
</html>
