<?php
// Configurar la zona horaria
date_default_timezone_set('America/Lima');

// Configuración de la base de datos
$host = 'localhost';
$username = 'root'; // Cambia según tu configuración
$password = ''; // Cambia según tu configuración
$database = 'usu2'; // Cambia por tu base de datos

// Crear conexión
$con = new mysqli($host, $username, $password, $database);

// Verificar conexión
if ($con->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión: " . $con->connect_error]);
    die();
}

// Función para escapar caracteres especiales
function escapeSql($value)
{
    global $con;
    if (is_null($value)) {
        return 'NULL';
    }
    return "'" . $con->real_escape_string($value) . "'";
}

// Función para generar respaldo en formato SQL
function generarBackupSQL()
{
    global $con;

    // Consulta para obtener todos los registros de la tabla users
    $query = "SELECT * FROM users";
    $result = $con->query($query);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "No hay datos para respaldar en la tabla users."]);
        return;
    }

    // Obtener los nombres de las columnas
    $columnas = [];
    while ($field = $result->fetch_field()) {
        $columnas[] = "`" . $field->name . "`";
    }
    $columnasEscapadas = implode(', ', $columnas);

    // Iniciar contenido del archivo SQL
    $sqlContent = "-- Respaldo de la tabla users\n";
    $sqlContent .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlContent .= "INSERT INTO `users` ($columnasEscapadas) VALUES\n";

    // Crear las filas de inserción
    $valores = [];
    while ($row = $result->fetch_assoc()) {
        $fila = [];
        foreach ($row as $value) {
            $fila[] = escapeSql($value);
        }
        $valores[] = "(" . implode(', ', $fila) . ")";
    }
    $sqlContent .= implode(",\n", $valores) . ";\n";

    // Crear la carpeta de respaldo si no existe
    $backupPath = 'C:\\xampp\\htdocs\\Trabajo\\Backup';
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0777, true);
    }

    // Generar el nombre del archivo
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = "$backupPath\\backup_users_$timestamp.sql";

    // Guardar el contenido en el archivo
    file_put_contents($backupFile, $sqlContent);
    echo json_encode(["success" => true, "message" => "Respaldo generado exitosamente en: $backupFile"]);
}

// Generar respaldo manualmente al acceder a este archivo
if (php_sapi_name() !== 'cli') {
    generarBackupSQL();
}

// Programar respaldo automático cada minuto (para CLI o cron jobs)
if (php_sapi_name() === 'cli') {
    while (true) {
        generarBackupSQL();
        sleep(60); // Esperar 1 minuto
    }
}
?>