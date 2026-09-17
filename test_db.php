<?php
require_once __DIR__ . "/public_html/includes/config.php";

$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Conexión exitosa a la base de datos</h3>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error en la consulta: " . $conn->error;
}
?>
