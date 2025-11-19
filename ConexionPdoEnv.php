<?php

require 'Entorno.php';
class ConexionPdoEnv
{
    public static function conectar(string $entorno)
    {
        Entorno::setFichero($entorno);
        $opciones = [
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ];
        try {
            $pdo = new PDO(
                "mysql:dbname=" . Entorno::get('BD') . ";host=" . Entorno::get('HOST') . ";",
                Entorno::get('USER'),
                Entorno::get('PASSWORD'),
                $opciones);
        } catch (PDOException $e) {
            echo "[x] Conexion fallida: " . $e->getMessage();
        }
        return $pdo;
    }
}