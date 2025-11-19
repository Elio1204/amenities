<?php
session_start();

if (!isset($_SESSION['idcons'], $_SESSION['iduf'], $_SESSION['db_consorcio'])) {
    die("No hay sesión activa. Volvé a loguearte.");
}

$idcons       = $_SESSION['idcons'];
$iduf         = $_SESSION['iduf'];
$db_consorcio = $_SESSION['db_consorcio'];

$host = "www.keyxad.ar";
$user = "keyxad_adminis";
$pass = "Keyxad2024@";

$conn = new mysqli($host, $user, $pass, $db_consorcio);
if ($conn->connect_error) {
    die("Error conectando a la base del consorcio ($db_consorcio): " . $conn->connect_error);
}

/* Nombre del consorcio */
$sql = "SELECT nombre FROM consorcios WHERE idcons = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idcons);
$stmt->execute();
$res = $stmt->get_result();
$nombre_consorcio = ($res->num_rows === 1) ? $res->fetch_assoc()['nombre'] : "(No encontrado)";

/* Definir variables usadas en la vista para evitar 'undefined' */
$consorcio_nombre = $nombre_consorcio;
$unidad_nombre = !empty($iduf) ? "UF " . $iduf : "(UF desconocida)";

/* Leer amenities */
$sql_amenities = "SELECT idameni, nombre, ubicacion, terminos FROM amenities WHERE habilitado = 1 AND idcons = ?";
$stmt2 = $conn->prepare($sql_amenities);
$stmt2->bind_param("i", $idcons);
$stmt2->execute();
$res_amenities = $stmt2->get_result();

/* Helper para elegir ícono Font Awesome */
function amenity_icon_class($text) {
    $t = mb_strtolower($text);

    if (strpos($t, 'piscin') !== false) return "fa-swimming-pool";
    if (strpos($t, 'gim') !== false) return "fa-dumbbell";
    if (strpos($t, 'quinch') !== false || strpos($t, 'parrilla') !== false) return "fa-fire";
    if (strpos($t, 'salon') !== false || strpos($t, 'salón') !== false) return "fa-people-group";
    if (strpos($t, 'laund') !== false || strpos($t, 'lav') !== false) return "fa-soap";
    if (strpos($t, 'cancha') !== false || strpos($t, 'fut') !== false) return "fa-futbol";
    if (strpos($t, 'play') !== false || strpos($t, 'jueg') !== false) return "fa-child";

    return "fa-circle-question"; // default
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Amenities</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-[#FF0000] to-[#FFA200] min-h-screen font-sans text-gray-900">

<!-- Toolbar -->
<header class="sticky top-0 bg-white/30 backdrop-blur-md border-b border-white/20 shadow-sm p-4">
  <div class="flex justify-between items-center">
    <h1 class="text-xl font-bold flex items-center gap-2 text-white">
      <i class="fa-solid fa-building"></i> Amenities
    </h1>
    <span class="text-sm text-white/80">Unidad: <?php echo htmlspecialchars($unidad_nombre); ?></span>
  </div>
</header>

<!-- Contenido -->
<main class="container mx-auto px-4 py-10 space-y-8">

  <!-- Bienvenida -->
  <div class="bg-white/80 rounded-xl p-6 shadow-md">
    <h2 class="text-lg font-semibold mb-1">Bienvenido <?php echo htmlspecialchars($unidad_nombre); ?></h2>
    <p class="text-sm text-gray-700">
      Estás consultando los servicios del consorcio <strong><?php echo htmlspecialchars($consorcio_nombre); ?></strong>.
    </p>
  </div>

  <!-- Cards -->
  <div class="grid md:grid-cols-2 gap-6">
    <?php
    if ($res_amenities && $res_amenities->num_rows >= 1) {
        while ($row = $res_amenities->fetch_assoc()) {
            $idameni = $row['idameni'];
            $nombre = htmlspecialchars($row['nombre']);
            $ubicacion = htmlspecialchars($row['ubicacion']);
            $terminos = htmlspecialchars($row['terminos']);
            $icon_class = amenity_icon_class($nombre . ' ' . $ubicacion);

            echo '<div class="bg-white rounded-xl shadow-lg p-6 flex gap-4 items-start hover:shadow-xl transition">';
            echo '  <div class="text-[#FF0000] text-xl"><i class="fa-solid '.$icon_class.'"></i></div>';
            echo '  <div class="flex-1">';
            echo "    <h3 class=\"text-lg font-bold mb-1\">{$nombre}</h3>";
            echo "    <p class=\"text-sm text-gray-600 mb-3\"><i class=\"fa-solid fa-location-dot\"></i> {$ubicacion}</p>";
            echo '    <div class="flex gap-2 flex-wrap">';
            echo '      <button class="px-3 py-1.5 rounded-full bg-gray-100 text-sm text-gray-800 hover:bg-gray-200 transition"><i class="fa-solid fa-calendar-days"></i> Calendario</button>';
            echo '      <button class="px-3 py-1.5 rounded-full bg-[#FF0000] text-sm text-white hover:bg-[#cc0000] transition"><i class="fa-solid fa-check"></i> Reservar</button>';
            echo '      <button class="btn-terminos px-3 py-1.5 rounded-full bg-gray-100 text-sm text-gray-800 hover:bg-gray-200 transition" ';
            echo ' data-terminos="'.$terminos.'" onclick="openModal(this)">';
            echo ' <i class="fa-solid fa-file-contract"></i> Términos</button>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center p-6 rounded-xl bg-white/80 shadow-md">No hay amenities disponibles en este momento.</div>';
    }
    ?>
  </div>
</main>

<!-- Footer -->
<footer class="text-center py-4 text-sm text-white/80">
  © 2025 Consorcio · Todos los derechos reservados
</footer>

<!-- Modal -->
<div id="terminosModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
    <h2 class="text-lg font-bold mb-3 flex items-center gap-2 text-[#FF0000]">
      <i class="fa-solid fa-file-contract"></i> Términos y Condiciones
    </h2>
    <!-- Scroll interno + interpretación de HTML -->
    <div id="modalTerminosTexto" class="text-sm text-gray-700 leading-relaxed max-h-[300px] overflow-y-auto pr-2"></div>
    <div class="mt-6 flex justify-end">
      <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition" onclick="closeModal()">Cerrar</button>
    </div>
  </div>
</div>


<script>
function openModal(button) {
  const terminos = button.getAttribute('data-terminos');
  document.getElementById('modalTerminosTexto').innerHTML = terminos; // interpreta HTML
  const modal = document.getElementById('terminosModal');
  const content = document.getElementById('modalContent');
  modal.classList.remove('hidden');
  setTimeout(() => {
    content.classList.remove('scale-95','opacity-0');
    content.classList.add('scale-100','opacity-100');
  }, 50);
}

function closeModal() {
  const modal = document.getElementById('terminosModal');
  const content = document.getElementById('modalContent');
  content.classList.remove('scale-100','opacity-100');
  content.classList.add('scale-95','opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}
</script>

</body>