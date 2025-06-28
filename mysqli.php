<?php
$conn = new mysqli("localhost", "rundb", "runpass", "books");
if ($conn->connect_error) die("Fehler: " . $conn->connect_error);
?>