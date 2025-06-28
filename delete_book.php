<?php
session_start(); 
// Session starten oder fortsetzen, damit wir Benutzerdaten (z.B. Loginstatus) aus der Session verwenden können.
// Ohne session_start() gibt es keinen Zugriff auf $_SESSION-Daten.

require 'config.php'; 
// Datenbankverbindung einbinden. Die Datei enthält in der Regel die $pdo-Variable für PDO-Datenbankzugriff.
// So können wir später SQL-Anfragen an die DB stellen.

// Nur eingeloggte Benutzer dürfen fortfahren
if (!isset($_SESSION['benutzername'])) {
    // Prüfen, ob in der Session ein Benutzername existiert -> heißt: Benutzer ist eingeloggt
    // Wenn nicht eingeloggt, dann weiterleiten zur Login-Seite, damit sich der Nutzer zuerst anmelden muss
    header("Location: login.php");
    exit; 
    // exit stoppt die weitere Ausführung, damit kein Code mehr ausgeführt wird (wichtig nach header-Redirect)
}

// Prüfen, ob der Benutzer Adminrechte hat
$stmt = $pdo->prepare("SELECT admin FROM benutzer WHERE benutzername = ?"); 
// Bereite eine SQL-Anfrage vor, die das Feld 'admin' aus der Tabelle 'benutzer' abruft, für den eingeloggten Benutzernamen.
// Die Verwendung von Prepared Statements schützt vor SQL-Injection (wichtige serverside Validierung).

$stmt->execute([$_SESSION['benutzername']]); 
// Führe die vorbereitete Anfrage aus und übergebe den Benutzernamen als Parameter.
// Der Platzhalter "?" wird durch den Wert aus $_SESSION sicher ersetzt (SQL-Injection verhindert).

$user = $stmt->fetch(); 
// Hole das Ergebnis als assoziatives Array.
// $user enthält jetzt z.B. ['admin' => 1] oder false, falls Benutzer nicht gefunden.

// Prüfen, ob Benutzer existiert und ob Adminrechte vorhanden sind
if (!$user || !$user['admin']) {
    // Falls kein Benutzer gefunden ($user == false) oder 'admin' Feld ist false (kein Admin)
    // dann weiterleiten auf eine Seite, die nur Admins erlaubt ('nur_fuer_admins.php')
    header("Location: nur_fuer_admins.php"); 
    exit; // Auch hier: script beenden nach Weiterleitung
}

// Prüfen, ob das Formular per POST gesendet wurde
// und ob eine gültige Buch-ID mitgeschickt wurde (überprüfung auf Vorhandensein und Zahlenwert)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    // $_SERVER['REQUEST_METHOD'] ist 'POST' → es wurde ein Formular abgeschickt (wichtig für sichere Datenverarbeitung)
    // isset($_POST['id']) stellt sicher, dass die Buch-ID gesendet wurde
    // is_numeric() validiert, dass die ID wirklich eine Zahl ist (Vermeidung von ungültigen Eingaben und Angriffen)
    
    $book_id = (int)$_POST['id']; 
    // Typumwandlung in Integer (Ganzzahl), damit nur Zahlenwerte weiterverarbeitet werden.
    // Diese serverseitige Validierung ist wichtig, um SQL-Injection und Fehler zu vermeiden.

    // Buch aus der Datenbank löschen
    $stmt = $pdo->prepare("DELETE FROM buecher WHERE id = ?");
    // Prepared Statement für Löschanfrage vorbereiten, "?" als Platzhalter für ID
    // Prepared Statements schützen vor SQL-Injection, da Eingaben nicht direkt in die SQL-Anweisung geschrieben werden.

    if ($stmt->execute([$book_id])) {
        // Wenn die Löschanfrage erfolgreich ausgeführt wurde
        // Weiterleitung zur Verwaltungsseite mit Erfolgsanzeige (?delete=success)
        header("Location: manage_books.php?delete=success");
        exit;
    } else {
        // Wenn das Löschen nicht erfolgreich war (z.B. DB-Fehler)
        // Weiterleitung mit Fehleranzeige (?delete=fail)
        header("Location: manage_books.php?delete=fail");
        exit;
    }
} else {
    // Falls die Bedingungen (POST, gültige ID) nicht erfüllt sind, z.B. direkte Zugriffe oder falsche Daten
    // Debug-Ausgabe aller POST-Daten, um in der Entwicklungsphase Fehler leichter zu finden
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    die("❌ Ungültige Buch-ID."); 
    // Skript wird beendet mit Fehlermeldung
    // In der Produktion sollte man stattdessen eine freundlichere Fehlermeldung oder Redirect verwenden
}
