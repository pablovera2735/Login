<?php
require 'ConexionPdoEnv.php';
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'on');
date_default_timezone_set('Europe/Madrid');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logemail']) && isset($_POST['logpass'])) {
        $email = $_POST['logemail'];
        $password = $_POST['logpass'];
        $pdo = ConexionPdoEnv::conectar('.env');
        $stmt = $pdo->prepare('SELECT * FROM USUARIOS WHERE CORREO = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['CLAVE'])) {
            session_start();
            $_SESSION['user_id'] = $user['ID'];
            header('Location: index1.html');
            exit();
        } else {
            echo "Credenciales incorrectas. Intenta nuevamente.";
        }
    } else {
        echo "Datos insuficientes para realizar el inicio de sesión.";
    }
} else {
    echo "Acceso no permitido.";
}
?>