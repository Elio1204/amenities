<?php
session_start();

/* ============================================
   1. Protección de sesión
   ============================================ */
if (!isset($_SESSION['db_consorcio'])) {
    die("No hay sesión activa.");
}

$db_consorcio = $_SESSION['db_consorcio'];
$idcons = $_SESSION['idcons'];
$idameni = $_GET['idameni'] ?? null;

if (!$idameni) {
    die("Falta el ID del amenity.");
}

/* ============================================
   2. Conectar a la base del consorcio
   ============================================ */
$host = "www.keyxad.ar";
$user = "keyxad_adminis";
$pass = "Keyxad2024@";

$conn = new mysqli($host, $user, $pass, $db_consorcio);
if ($conn->connect_error) {
    die("Error conectando a base del consorcio: " . $conn->connect_error);
}

/* ============================================
   3. Obtener los días del amenity
   ============================================ */
$sql = "SELECT dia, desde, hasta, precio, observaciones
        FROM amenities_dias
        WHERE idameni = ?
        ORDER BY dia ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idameni);
$stmt->execute();
$res = $stmt->get_result();

$dias_semana = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes",
    6 => "Sábado",
    7 => "Domingo"
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario del Amenity</title>
</head>
<body>

<h2>Disponibilidad del Amenity</h2>
<p><strong>ID Amenity:</strong> <?php echo $idameni; ?></p>
<hr>

<?php
if ($res->num_rows == 0) {
    echo "<p>No hay horarios cargados para este amenity.</p>";
} else {

    while ($fila = $res->fetch_assoc()) {

        $dia_num = $fila['dia'];
        $dia_nombre = $dias_semana[$dia_num] ?? "Día $dia_num";

        echo "<p><strong>$dia_nombre:</strong> ";
        echo substr($fila['desde'], 0, 5) . " a " . substr($fila['hasta'], 0, 5);
        
        if (!empty($fila['precio'])) {
            echo " — Precio: $" . number_format($fila['precio'], 0, ',', '.');
        }

        if (!empty($fila['observaciones'])) {
            echo " — Nota: " . $fila['observaciones'];
        }

        echo "</p>";
    }

}
?>

</body>
</html>
