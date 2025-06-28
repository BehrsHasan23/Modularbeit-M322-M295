<?php
// Verbindung zur Datenbank
include 'dateiname.php';

// Admin-Daten
$username = "admin";
$password = "passwort123"; // Ändern Sie dies!
$hash = password_hash($password, PASSWORD_DEFAULT);

// Admin in Datenbank einfügen
$stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hash);

if ($stmt->execute()) {
    echo "Admin wurde erstellt!<br><br>";
    echo "<strong>Benutzername:</strong> admin<br>";
    echo "<strong>Passwort:</strong> passwort123";
} else {
    echo "Fehler: " . $conn->error;
}

$stmt->close();
$conn->close();
?>