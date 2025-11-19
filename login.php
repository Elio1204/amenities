<?php
session_start();

/* ============================================================
   conexión a base principal
   ============================================================ */
$host = "www.keyxad.ar";
$user = "keyxad_adminis";
$pass = "Keyxad2024@";
$db_main = "keyxad_adminis";

/* Conexión a base principal */
$conn_main = new mysqli($host, $user, $pass, $db_main);
if ($conn_main->connect_error) {
    die("Error conectando a keyxad_adminis: " . $conn_main->connect_error);
}

/* ============================================================
  recibir datos desde la URL
   ============================================================ */
$email = $_GET['email'] ?? null;
$clave = $_GET['clave'] ?? null;

if (!$email || !$clave) {
    die("Faltan datos en la URL.");
}

/* ============================================================
    validar email y clave en acceso_propietarios
   ============================================================ */
$sql = "SELECT idcons, iduf, idadmin
        FROM acceso_propietarios
        WHERE email = ? AND clave = ?
        LIMIT 1";

$stmt = $conn_main->prepare($sql);
$stmt->bind_param("ss", $email, $clave);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    die("Email o clave incorrectos.");
}

$datos = $res->fetch_assoc();

$idcons  = $datos['idcons'];
$iduf    = $datos['iduf'];
$idadmin = $datos['idadmin'];


/* ============================================================
Buscar la base correspondiente en administraciones
   ============================================================ */
$sql2 = "SELECT base 
         FROM administraciones
         WHERE idadmin = ?
         LIMIT 1";

$stmt2 = $conn_main->prepare($sql2);
$stmt2->bind_param("i", $idadmin);
$stmt2->execute();
$res2 = $stmt2->get_result();

if ($res2->num_rows !== 1) {
    die("No se encontró la base asociada a esta administración.");
}


$row2 = $res2->fetch_assoc();
$db_consorcio = $row2['base']; // guarda la lectura de la base que lee


echo "idadmin". $idadmin;
echo "<br>";
echo "idcons". $idcons;
echo "<br>";
echo "iduf". $iduf;
echo "<br>";
echo "base: " . $db_consorcio;
echo "<br>";

/* ============================================================
 conexión a la base del consorcio
   ============================================================ */
$conn_consorcio = new mysqli($host, $user, $pass, $db_consorcio);
if ($conn_consorcio->connect_error) {
    die("Error al conectar a la base del consorcio ($db_consorcio): " . $conn_consorcio->connect_error);
}

/* verifica si existe el idcons en la base */
$sql3 = "SELECT idcons FROM consorcios WHERE idcons = ? LIMIT 1";
$stmt3 = $conn_consorcio->prepare($sql3);
$stmt3->bind_param("i", $idcons);
$stmt3->execute();
$res3 = $stmt3->get_result();

if ($res3->num_rows !== 1) {
    die("El idcons no existe en la base del consorcio.");
}

/* ============================================================
   guarda para usar después en sesión
   ============================================================ */
$_SESSION['email'] = $email;
$_SESSION['idcons'] = $idcons;
$_SESSION['iduf'] = $iduf;
$_SESSION['idadmin'] = $idadmin;
$_SESSION['db_consorcio'] = $db_consorcio;

/* ============================================================
   redirige al modulo correspondiente
   ============================================================ */
header("Location: amenities.php");
exit;

?>
