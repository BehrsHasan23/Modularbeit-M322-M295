<?php

include 'mysqli.php';

// Liest die Buch-ID aus der URL (GET-Parameter 'id'); falls nicht vorhanden, wird 0 verwendet
$buch_id = $_GET['id'] ?? 0;

// Erstellt die SQL-Abfrage, um alle Daten des ausgewählten Buches abzurufen
// Zusätzlich werden über JOINs mit den Tabellen "zustaende" und "kategorien" 
// der Zustandstext (als "zustand_text") und der Kategoriename (als "kategorie_text") ermittelt
$sql = "SELECT buecher.*, 
               zustaende.beschreibung AS zustand_text,
               kategorien.kategorie AS kategorie_text
        FROM buecher
        JOIN zustaende ON buecher.zustand = zustaende.zustand
        JOIN kategorien ON buecher.kategorie = kategorien.id
        WHERE buecher.id = $buch_id";

// Führt die SQL-Abfrage aus und speichert das Ergebnis
$result = $conn->query($sql);

// Holt den Datensatz des Buches als assoziatives Array
$buch = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Setzt den Zeichensatz der Seite -->
    <meta charset="UTF-8">
    <!-- Setzt den Seitentitel dynamisch auf den Buchtitel, geschützt mit htmlspecialchars -->
    <title><?= htmlspecialchars($buch['Title']) ?></title>
    <!-- Bindet das Bootstrap-CSS-Framework ein -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Verlinkt ein externes CSS-Stylesheet -->
    <link rel="stylesheet" href="css.css">
    <!-- Internes CSS zur Gestaltung der Detailseite -->
    <style>
        /* Legt den Hintergrund und die Schriftart für den gesamten Body fest */
        body {
            background: #F9FFE1 !important;
            font-family: 'Arial', sans-serif;
        }
        /* Container-Styling für den Detailbereich */
        .detail-container {
            margin-top: 2rem;
            margin-bottom: 4rem;
            padding: 2rem;
            background: #F9FFE1;
            border-radius: 10px;
        }
        /* Stil für den Buchtitel */
        h1.buch-titel {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-weight: 700;
        }
        /* Stil für Textabschnitte im Buchinformationsbereich */
        .buch-info p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 1rem 0;
        }
        /* Hebt starke Texte im Informationsbereich hervor */
        .buch-info strong {
            color: #34495e;
            font-weight: 600;
        }
        /* Styling für das Buchcover-Bild */
        .buch-cover {
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
            padding: 5px;
        }
        /* Styling für den Beschreibungsteil */
        .beschreibung {
            margin-top: 2rem;
            background: #F9FFE1;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<!-- Header-Bereich mit Navigation -->
<header class="p-3 text-bg-dark">
      <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
          <!-- Navigationsmenü -->
          <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
            <!-- Link zur Startseite -->
            <li><a href="ProjektWebsite.php" class="nav-link px-2 text-white">Home</a></li>
            <!-- Link zur Kontaktseite -->
            <li><a href="Kontakt.php" class="nav-link px-2 text-white">Kontakt</a></li>
            <!-- Link zur Books-Seite -->
            <li><a href="books.php" class="nav-link px-2 text-white">Books</a></li>
          </ul>
          
          <!-- Login- und Sign-up-Buttons -->
          <div class="text-end">
            <!-- Button mit Link zur Login-Seite -->
            <button type="button" class="btn btn-outline-light me-2">
              <a href="login.php" class="nav-link px-2">Login</a>
            </button>
            <!-- Button mit Link zur Sign-up-Seite -->
            <button type="button" class="btn btn-warning">
              <a href="signup.php" class="nav-link px-2 text-white">Sign-up</a>
            </button>
          </div>
        </div>
      </div>
</header>

<!-- Container für den Buchdetailbereich -->
<div class="container detail-container">
    <?php if($buch): ?>
        <div class="row">
            <!-- Bildbereich (linke Spalte) -->
            <div class="col-md-4">
                <!-- Zeigt das Buchcover an -->
                <img src="Bild/book.jpg" class="buch-cover img-fluid" alt="Buchcover">
                <p class="mt-3">
                  <strong class="lead">Zustand:</strong> 
                  <!-- Gibt den Zustand des Buches als Text aus (z. B. Gut, Mittel, Schlecht); falls nicht vorhanden, "Keine Angabe" -->
                  <span class="lead"><?= htmlspecialchars($buch['zustand_text'] ?? 'Keine Angabe') ?></span>
                </p>
                <!-- Button, um zur Bücherübersicht zurückzukehren -->
                <a href="books.php" class="btn btn-primary w-100 mt-3">Zurück zur Übersicht</a>
            </div>
            
            <!-- Textbereich (rechte Spalte) -->
            <div class="col-md-8 buch-info">
                <!-- Buchtitel, geschützt mit htmlspecialchars -->
                <h1 class="buch-titel"><?= htmlspecialchars($buch['Title']) ?></h1>
                
                <!-- Anzeige des Autors -->
                <p>
                    <strong>Autor:</strong> 
                    <span class="autor-name"><?= htmlspecialchars($buch['autor']) ?></span>
                </p>
                <!-- Anzeige des Verfassers -->
                <p>
                    <strong>Verfasser:</strong> 
                    <span class="kategorie-badge"><?= htmlspecialchars($buch['verfasser']) ?></span>
                </p>
                <!-- Anzeige, ob das Buch verkauft wurde -->
                <p>
                    <strong>Verkauft:</strong> 
                    <span class="kategorie-badge"><?= htmlspecialchars($buch['verkauft']) ?></span>
                </p>
                <!-- Anzeige des Katalogs -->
                <p>
                    <strong>Katalog:</strong> 
                    <span class="kategorie-badge"><?= htmlspecialchars($buch['katalog']) ?></span>
                </p>
                <!-- Anzeige der Buchnummer -->
                <p>
                    <strong>Nummer:</strong> 
                    <span class="kategorie-badge"><?= htmlspecialchars($buch['nummer']) ?></span>
                </p>
                <!-- Anzeige der Kategoriebezeichnung -->
                <p>
                    <strong>Kategorien:</strong> 
                    <span class="kategorie-badge"><?= htmlspecialchars($buch['kategorie_text']) ?></span>
                </p>
                <!-- Beschreibung des Buches -->
                <div class="beschreibung">
                    <h5 class="mb-3">Beschreibung:</h5>
                    <!-- Gibt die Buchbeschreibung aus, falls vorhanden, ansonsten einen Standardtext -->
                    <p class="lead"><?= htmlspecialchars($buch['Beschreibung'] ?? 'Keine Beschreibung verfügbar') ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Falls kein Buch gefunden wurde, wird eine Fehlermeldung angezeigt -->
        <div class="alert alert-danger">Buch nicht gefunden</div>
    <?php endif; ?>
</div>

<!-- Footer-Bereich -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Spalte 1: Über uns -->
            <div class="col-md-4">
                <h5>Über uns</h5>
                <p>Wir lieben Bücher und teilen sie mit der Welt.</p>
            </div>
            <!-- Spalte 2: Links -->
            <div class="col-md-4">
                <h5>Links</h5>
                <ul class="list-unstyled">
                    <li><a href="ProjektWebsite.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="Kontakt.php" class="text-white text-decoration-none">Kontakt</a></li>
                    <li><a href="books.php" class="text-white text-decoration-none">Bücher</a></li>
                </ul>
            </div>
            <!-- Spalte 3: Kontaktinformationen -->
            <div class="col-md-4">
                <h5>Kontakt</h5>
                <p>Email: info@buchseite.ch</p>
                <p>Telefon: +41 76 456 89</p>
            </div>
        </div>
        <!-- Horizontale Trennlinie -->
        <hr class="border-light">
        <!-- Copyright-Hinweis -->
        <p class="mb-0">&copy; 2025 Buchseite. Alle Rechte vorbehalten.</p>
    </div>
</footer>
</body>
</html>
