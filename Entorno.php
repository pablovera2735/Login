<?php

class Entorno
{
    public static $ENV = [];
    public static $PATH = '';

    public static function setFichero(string $fichero): void
    {
        self::$PATH = __DIR__ . "/".$fichero;
    }

    public static function get(string $vbleEntorno): string
    {
        if (file_exists(self::$PATH)) {
            $lineas = explode("\n", file_get_contents(self::$PATH));
            foreach ($lineas as $linea) {
                list($variable, $valor) = explode("=", $linea);
                self::$ENV[$variable] = trim($valor);
            }
        }
        $result = "";
        if (isset(self::$ENV[$vbleEntorno])) {
            $result = (string)self::$ENV[$vbleEntorno];

        }
        return $result;
    }
}