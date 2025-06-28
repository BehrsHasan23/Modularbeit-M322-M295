<?php

// Startet eine neue oder bestehende Session
// Sessions dienen zur Speicherung von Benutzerdaten zwischen HTTP-Anfragen (serverseitig)
session_start();

// Lädt die Datei 'config.php', die üblicherweise die Datenbankverbindung enthält
require 'config.php';

// Zugriffsschutz: Nur eingeloggte Benutzer mit Adminrechten dürfen fortfahren
// $_SESSION['benutzername'] muss gesetzt sein UND $_SESSION['is_admin'] muss true sein
if (!isset($_SESSION['benutzername']) || !($_SESSION['is_admin'] ?? false)) {
    // Wenn Bedingung nicht erfüllt: Umleitung zur Loginseite
    header("Location: login.php");
    exit; // Beendet die weitere Ausführung des Scripts
}

// ---------- PAGINATION (SEITENBLÄTTERUNG) & SUCHE ----------

// Legt fest, wie viele Benutzer pro Seite angezeigt werden sollen
$items_per_page = 8;

// Liest die aktuelle Seite aus der URL (GET-Parameter 'seite') aus, Standard ist 1
$current_page = max(1, (int)($_GET['seite'] ?? 1)); // max stellt sicher, dass Seite nie < 1 ist

// Berechnet den Datenbank-Offset: Wie viele Datensätze übersprungen werden sollen
$offset = ($current_page - 1) * $items_per_page;

// Liest den Suchbegriff aus der URL (GET-Parameter 'search') aus, trim entfernt Leerzeichen
$search = trim($_GET['search'] ?? '');

// ---------- DATEN AUS DER DATENBANK LADEN ----------
try {
    // Vorbereitung der SQL-Abfrage mit Platzhaltern
    // SQL_CALC_FOUND_ROWS erlaubt später das Zählen aller Ergebnisse ohne LIMIT
    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM benutzer WHERE benutzername LIKE :search OR email LIKE :search LIMIT :offset, :limit");

    // :search wird mit dem Suchbegriff ersetzt. % steht für beliebige Zeichen (LIKE ist unscharfe Suche)
    $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR); // PDO::PARAM_STR = Stringwert

    // Offset: Wo soll die Datenbank anfangen zu lesen?
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT); // PDO::PARAM_INT = Ganzzahl

    // Limit: Wie viele Datensätze pro Seite?
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);

    // Führt das vorbereitete Statement aus
    $stmt->execute();

    // Holt alle gefundenen Benutzer als Array (assoziativ)
    $users = $stmt->fetchAll();

    // Holt die Gesamtanzahl aller Treffer ohne LIMIT
    // Führt die SQL-Abfrage "SELECT FOUND_ROWS()" aus, um die Anzahl der gefundenen Zeilen der letzten Abfrage zu ermitteln,
    // holt den Wert aus der ersten Spalte der Ergebniszeile, wandelt ihn in eine ganze Zahl (Integer) um
    // und speichert das Ergebnis in der Variable $total.

    $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

    // Berechnet die Gesamtanzahl der Seiten (nach oben aufgerundet)
    $total_pages = (int)ceil($total / $items_per_page);

} catch (PDOException $e) {
    // Falls ein Fehler bei der Datenbankabfrage auftritt, Script abbrechen und Fehlermeldung anzeigen
    die("Datenbankfehler: " . $e->getMessage());
}

// ---------------------------------------------
// Hinweis: PDO (PHP Data Objects) ist eine sichere und flexible Methode,
// um mit einer Datenbank zu kommunizieren. Es unterstützt vorbereitete Statements,
// wodurch SQL-Injections verhindert werden können (wichtig für Sicherheit).
// ---------------------------------------------
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Benutzerverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #F9FFE1; }
        .user-table { background: white; border-radius: 10px; }
        .admin-badge { min-width: 70px; }
    </style>
</head>
<body>
<header class="p-3 text-bg-dark mb-4">
  <div class="container d-flex justify-content-between align-items-center">
    <h3 class="text-white m-0">Benutzerverwaltung</h3>
    <a href="dashboard.php" class="btn btn-outline-light">Zurück zum Dashboard</a>
  </div>
</header>
    <div class="container py-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="input-group" method="get" action="">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Benutzername oder E‑Mail suchen…"
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary">Suchen</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="create_user.php" class="btn btn-success">Neuen Nutzer</a>
            </div>
        </div>

        <div class="user-table p-4 shadow">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Benutzername</th>
                        <th>Name</th>
                        <th>Vorname</th>
                        <th>E‑Mail</th>
                        <th>Admin</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr> <!-- tabelle zeile -->
                        <td><?= htmlspecialchars($u['benutzername']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['vorname']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <!-- Dieses kleine Code-Stück zeigt einen farbigen Badge (kleines Label) an,
                                der anzeigt, ob der Benutzer Admin-Rechte hat ("Ja" in grün) oder nicht ("Nein" in grau).
                                 succes = grün und secondary = grau also für ja oder nein und Sie sind Farbklassen von Bootstrap-->
                            <span class="badge admin-badge bg-<?= $u['admin'] ? 'success' : 'secondary' ?>">
                                <?= $u['admin'] ? 'Ja' : 'Nein' ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_user.php?ID=<?= $u['ID'] ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                            <form method="post"
                                action="delete_user.php"
                                class="d-inline"
                                onsubmit="return confirm('Nutzer wirklich löschen?')">
                                <input type="hidden" name="ID" value="<?= $u['ID'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Keine Benutzer gefunden.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>



            <!--
            "disabled" macht den Link inaktiv (nicht anklickbar).

            urlencode() macht Text URL-sicher, z.B. für Leerzeichen oder Sonderzeichen.
            -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <li class="page-item<?= $current_page === 1 ? ' disabled' : '' ?>">
                            <a class="page-link" href="?seite=1&search=<?= urlencode($search) ?>">Erste</a>
                        </li>
                        <li class="page-item<?= $current_page <= 1 ? ' disabled' : '' ?>">
                            <a class="page-link" href="?seite=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>">Zurück</a>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">Seite <?= $current_page ?> / <?= $total_pages ?></span>
                        </li>
                        <li class="page-item<?= $current_page >= $total_pages ? ' disabled' : '' ?>">
                            <a class="page-link" href="?seite=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>">Weiter</a>
                        </li>
                        <li class="page-item<?= $current_page === $total_pages ? ' disabled' : '' ?>">
                            <a class="page-link" href="?seite=<?= $total_pages ?>&search=<?= urlencode($search) ?>">Letzte</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
