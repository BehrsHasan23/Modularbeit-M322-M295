<?php
session_start();

// Zugriffskontrolle
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// DB-Verbindung
$conn = new mysqli("localhost", "rundb", "runpass", "books");
if ($conn->connect_error) {
    die("Datenbankfehler: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F9FFE1;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="navbar-brand">Admin Dashboard</span>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Angemeldet als: <?= htmlspecialchars($_SESSION['benutzername']) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Inhalt -->
<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">📚 Bücher</h5>
                    <a href="manage_books.php" class="btn btn-primary mt-auto">Verwalten</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">👤 Benutzer</h5>
                    <a href="benutzer.php" class="btn btn-primary mt-auto">Verwalten</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">⚙️ System</h5>
                    <a href="logout.php" class="btn btn-danger mt-auto">Abmelden</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
