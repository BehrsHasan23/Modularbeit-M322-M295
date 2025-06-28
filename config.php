<?php
/*Der Code sorgt dafür, dass du mit deiner Datenbank kommunizieren kannst 
und Benutzer-Daten über Sessions verfolgt werden können.*/

// Prüfen, ob schon eine Session gestartet wurde (um Fehler zu vermeiden)
// session_status() liefert den aktuellen Status der Session zurück
// PHP_SESSION_NONE bedeutet: Es wurde noch keine Session gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Session starten (wichtig für Login-Daten etc.)
}

// Versuch, eine Verbindung zur Datenbank aufzubauen (mit PDO)
try {
    $pdo = new PDO(
        // Verbindungszeichenfolge (DSN): Hier wird die Datenbank definiert
        // mysql:host=localhost;dbname=books;charset=utf8
        // - mysql: DB-Treiber (MySQL-Datenbank)
        // - host=localhost: DB liegt auf dem gleichen Server
        // - dbname=books: Name der Datenbank
        // - charset=utf8: Zeichensatz für Datenübertragung (UTF-8 ist Standard)
        'mysql:host=localhost;dbname=books;charset=utf8',

        // Benutzername für die DB-Verbindung
        'rundb',

        // Passwort für die DB-Verbindung
        'runpass',

        // Array mit Optionen, die das Verhalten von PDO steuern
        [//Zeigt Fehler als Ausnahme an.
            // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            // Hier wird das Attribut "Fehlermodus" gesetzt
            // PDO::ATTR_ERRMODE ist eine Konstante, die für "Fehlerbehandlungsmodus" steht
            // PDO::ERRMODE_EXCEPTION bedeutet: PDO soll bei Fehlern eine Exception (Fehlerausnahme) werfen,
            // damit wir diese im try/catch Block abfangen können und der Code nicht einfach abstürzt.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            //Holt Daten als Namen-Wert-Array (z.B. $row['benutzername']).

            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            // Dieses Attribut legt fest, wie Ergebnisse von Datenbankabfragen zurückgegeben werden.
            // PDO::FETCH_ASSOC sagt, dass die Daten als "assoziatives Array" zurückgegeben werden.
            // Das bedeutet: Man greift später auf Spaltenwerte über Spaltennamen zu, z.B. $row['benutzername']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    // Falls die DB-Verbindung fehlschlägt, wird dieser Fehler abgefangen
    // $e->getMessage() gibt die Fehlermeldung aus
    // die Ausführung wird mit die() gestoppt und eine Fehlermeldung ausgegeben
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>
