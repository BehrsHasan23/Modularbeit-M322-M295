<?php
session_start();
require 'config.php';

// Admin-Check (optional, aber empfohlen)
if (!isset($_SESSION['benutzername'])) {
    header("Location: login.php");
    exit;
}

// Prüfen, ob die ID übergeben wurde
if (!isset($_POST['ID']) || !is_numeric($_POST['ID'])) {
    echo "❌ Ungültige Benutzer-ID.";
    exit;
}

$user_id = (int) $_POST['ID'];

// Benutzer löschen
$stmt = $pdo->prepare("DELETE FROM benutzer WHERE ID = ?");
if ($stmt->execute([$user_id])) {
    header("Location: benutzer.php");
    exit;
} else {
    echo "❌ Fehler beim Löschen des Benutzers.";
}
