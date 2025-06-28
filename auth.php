<?php
//Code dient dazu, eine sichere Benutzerverwaltung mit Login und Admin-Zugriff zu ermöglichen
// Einbinden der Konfigurationsdatei, die vermutlich die Datenbankverbindung enthält
require_once 'config.php';

// Funktion zum Einloggen eines Benutzers, die Benutzernamen und Passwort entgegennimmt
function loginUser($username, $password) {
    // Zugriff auf die globale Datenbankverbindung
    global $conn;

    // Vorbereitetes Statement, um SQL-Injektionen zu verhindern
    $stmt = $conn->prepare("SELECT ID, passwort, admin FROM benutzer WHERE benutzername = ?");
    
    // Bindet den Benutzernamen an das Statement, damit er als Parameter übergeben wird
    $stmt->bind_param("s", $username);
    
    // Führt das Statement aus
    $stmt->execute();
    
    // Holt das Ergebnis des Statements
    $result = $stmt->get_result();
    
    // Überprüft, ob genau ein Benutzer mit diesem Benutzernamen gefunden wurde
    if ($result->num_rows === 1) {
        // Holt die Benutzer-Daten als assoziatives Array
        $user = $result->fetch_assoc();
        
        // Überprüft, ob das eingegebene Passwort korrekt ist
        if (password_verify($password, $user['passwort'])) {
            // Generiert eine neue Session-ID, um Session-Hijacking zu vermeiden
            session_regenerate_id(true);
            
            // Setzt die Benutzer-ID in der Session, um den eingeloggten Benutzer zu identifizieren
            $_SESSION['user_id'] = $user['ID'];
            
            // Setzt eine Admin-Flagge in der Session, basierend auf den Benutzerrechten
            $_SESSION['is_admin'] = ($user['admin'] == 1);
            
            // Setzt den Zeitstempel der letzten Aktivität für die Session
            $_SESSION['last_activity'] = time();
            
            // Gibt true zurück, wenn der Login erfolgreich war
            return true;
        }
    }
    
    // Gibt false zurück, wenn der Login fehlschlägt (entweder ungültiger Benutzername oder Passwort)
    return false;
}

// Funktion, die überprüft, ob ein Benutzer als Admin eingeloggt ist
function isAdminLoggedIn() {
    // Überprüft, ob die Session-Variablen gesetzt sind und ob der Benutzer ein Admin ist
    return isset($_SESSION['user_id'], $_SESSION['is_admin']) && $_SESSION['is_admin'];
}

// Funktion, die den Benutzer zur Login-Seite weiterleitet, wenn er nicht als Admin eingeloggt ist
function redirectIfNotAdmin() {
    // Wenn der Benutzer nicht als Admin eingeloggt ist, wird er zur Login-Seite weitergeleitet
    if (!isAdminLoggedIn()) {
        // Weiterleitung zur Login-Seite
        header("Location: ../login.php");
        exit(); // Stoppt das Skript nach der Weiterleitung
    }
}
?>
