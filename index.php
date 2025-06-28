<!doctype html>
<html lang="de">
<head>
    <!-- Setzt den Zeichensatz auf UTF-8 -->
    <meta charset="utf-8">
    <!-- Stellt die Viewport-Einstellungen für mobile Geräte ein -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Legt den Titel der Seite fest -->
    <title>Startseite - Bücher</title>
    <!-- Bindet das Bootstrap-CSS-Framework ein -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Internes CSS zur Gestaltung der Seite -->
    <style>
        /* Legt den Hintergrund der gesamten Seite fest */
        body {
            background: #F9FFE1;
        }
        /* Gestaltung für Abschnittsüberschriften */
        .section-title {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        /* Gestaltung für einzelne Buch-Boxen */
        .book-item {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 300px; /* Alle Boxen haben dieselbe Mindesthöhe */
        }
        /* Styling für den Titel innerhalb der Buch-Box */
        .book-item h5 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
        }
        /* Styling für den Text innerhalb der Buch-Box */
        .book-item p {
            font-size: 1rem;
            color: #666;
        }
        /* Bildgestaltung innerhalb der Buch-Box */
        .book-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        /* Fügt oben Abstand für den Bereich der beliebten Bücher hinzu */
        .popular-books {
            margin-top: 30px;
        }
        /* Styling für den Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header-Bereich der Seite -->
    <header class="p-3 text-bg-dark">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <!-- Navigationsleiste -->
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <!-- Link zur Startseite -->
                    <li><a href="#" class="nav-link px-2 text-white">Home</a></li>
                    <!-- Link zur Kontaktseite -->
                    <li><a href="Kontakt.php" class="nav-link px-2 text-white">Kontakt</a></li>
                    <!-- Link zur Bücherseite -->
                    <li><a href="books.php" class="nav-link px-2 text-white">Bücher</a></li>
                </ul>
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

    <!-- Abschnitt "Willkommen" -->
    <div class="container mt-5">
        <!-- Hauptüberschrift -->
        <h1 class="text-center">Willkommen bei der Buchwelt!</h1>
        <!-- Einleitender Text -->
        <p class="text-center">Entdecken Sie spannende Bücher, die Sie in eine andere Welt entführen. Wählen Sie aus verschiedenen Kategorien und finden Sie Ihr nächstes Lieblingsbuch.</p>
    </div>

    <!-- Bereich "Beliebte Bücher" -->
    <div class="container popular-books">
        <!-- Überschrift für diesen Bereich -->
        <h2 class="section-title text-center">Beliebte Bücher</h2>
        <div class="row">
            <!-- Buch 1: Ludwig Salvator, Levkosia -->
            <div class="col-md-4 d-flex">
                <div class="book-item w-100">
                    <!-- Bild des Buches -->
                    <img src="Bild/2.jpg" alt="Levkosia">
                    <!-- Titel des Buches -->
                    <h5>Ludwig Salvator, Levkosia</h5>
                    <!-- Kurze Beschreibung -->
                    <p>Eine faszinierende Reise durch die Geschichte und Kultur von Zypern.</p>
                </div>
            </div>
            <!-- Buch 2: Gentil, Voyage -->
            <div class="col-md-4 d-flex">
                <div class="book-item w-100">
                    <!-- Bild des Buches -->
                    <img src="Bild/22.jpg" alt="Voyage">
                    <!-- Titel des Buches -->
                    <h5>Gentil, Voyage</h5>
                    <!-- Kurze Beschreibung -->
                    <p>Abenteuerliche Erzählungen aus verschiedenen Teilen der Welt.</p>
                </div>
            </div>
            <!-- Buch 3: Rossi, Nuovo Atlante -->
            <div class="col-md-4 d-flex">
                <div class="book-item w-100">
                    <!-- Bild des Buches -->
                    <img src="Bild/3.jpg" alt="Nuovo Atlante">
                    <!-- Titel des Buches -->
                    <h5>Rossi, Nuovo Atlante</h5>
                    <!-- Kurze Beschreibung -->
                    <p>Ein neuer Atlas, der die Welt aus einer einzigartigen Perspektive zeigt.</p>
                </div>
            </div>
        </div>
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
                        <!-- Link zur Startseite -->
                        <li><a href="ProjektWebsite.php" class="text-white text-decoration-none">Home</a></li>
                        <!-- Link zur Kontaktseite -->
                        <li><a href="Kontakt.php" class="text-white text-decoration-none">Kontakt</a></li>
                        <!-- Link zur Bücherseite -->
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
