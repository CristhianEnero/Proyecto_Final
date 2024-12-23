<?php
session_start();
include("connection.php");
$con = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $contraseña = $_POST['contraseña'];

    $sql = "SELECT * FROM administrador WHERE username = '$username' AND contraseña = '$contraseña'";
    $query = mysqli_query($con, $sql);
    $nivel_usuario = mysqli_fetch_assoc($query);

    if ($nivel_usuario) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['nivel_usuario'] = $user['nivel_usuario'];

        // Redirigir según el tipo de usuario
        if ($nivel_usuario['nivel_usuario'] === 'avanzado') {
            header("Location: index.php");
        } else {
            header("Location: secundario.php");
        }
        exit();
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos.'); window.location.href='index.html';</script>";
    }
}
?>
