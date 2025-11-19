<?php
// Iniciar sesión (por si después queremos guardar variables)
session_start();

// Capturar email y clave desde la URL
$email = isset($_GET['email']) ? $_GET['email'] : null;
$clave = isset($_GET['clave']) ? $_GET['clave'] : null;

// Mostrar los valores para probar
echo "<h2>Valores recibidos:</h2>";
echo "Email: " . htmlspecialchars($email) . "<br>";
echo "Clave: " . htmlspecialchars($clave) . "<br>";

// Guardamos en sesión (opcional, pero sirve para después)
$_SESSION['email'] = $email;
$_SESSION['clave'] = $clave;

echo "<hr>";
echo "<h3>Variables guardadas en sesión:</h3>";
echo "Email en sesión: " . ($_SESSION['email'] ?? 'No guardado') . "<br>";
echo "Clave en sesión: " . ($_SESSION['clave'] ?? 'No guardado') . "<br>";
?>
