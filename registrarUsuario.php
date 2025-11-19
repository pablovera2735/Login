<?php
require 'ConexionPdoEnv.php';
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'on');
ini_set('upload_max_filesize', '5M');
date_default_timezone_set('Europe/Madrid');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lognick'])) {
        $nickMaxLength = 255;
        $nick = substr($_POST['lognick'], 0, $nickMaxLength);
    } else {
        $nick = null;
    }

    if (isset($_POST['logpass'])) {
        $password = generatePasswordHash($_POST['logpass']);
    } else {
        $password = null;
    }

    if (isset($_POST['logname'])) {
        $nomape = $_POST['logname'];
    } else {
        $nomape = null;
    }

    if (isset($_POST['logemail'])) {
        $emailMaxLength = 255;
        $email = substr($_POST['logemail'], 0, $emailMaxLength);

        // Validar el formato del correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "El formato del correo electrónico no es válido.";
            // Puedes agregar más lógica aquí si es necesario
            exit;
        }
    } else {
        $email = null;
    }

    if ($nick !== null && $nomape !== null) {
        $coordinates = getCoordinatesFromIP();
        $lat = $coordinates['LAT'];
        $lon = $coordinates['LON'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["logphoto"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (file_exists($targetFile)) {
            echo "Lo siento, el archivo ya existe.";
            $uploadOk = 0;
        }

        if ($_FILES["logphoto"]["size"] > 500000) {
            echo "Lo siento, tu archivo es demasiado grande.";
            $uploadOk = 0;
        }

        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($imageFileType, $allowedExtensions)) {
            echo "Lo siento, solo se permiten archivos JPG, JPEG, PNG y GIF.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Lo siento, tu archivo no fue cargado.";
        } else {
            if (move_uploaded_file($_FILES["logphoto"]["tmp_name"], $targetFile)) {
                echo "El archivo " . htmlspecialchars(basename($_FILES["logphoto"]["name"])) . " ha sido cargado.";

                // Optimizar la foto antes de almacenarla
                $optimizedPhoto = optimizePhoto(file_get_contents($targetFile));

                // Insertar datos en la base de datos
                $pdo = ConexionPdoEnv::conectar('.env');
                $stmt = $pdo->prepare('INSERT INTO USUARIOS (NICK, CLAVE, NOMAPE, CORREO, LAT, LON, FOTO) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$nick, $password, $nomape, $email, $lat, $lon, $optimizedPhoto]);

                echo "Usuario registrado exitosamente!";
            } else {
                echo "Lo siento, hubo un error al cargar tu archivo.";
            }
        }
    } else {
        echo "Datos insuficientes para registrar el usuario.";
    }
}

function generatePasswordHash($clave) {
    $options = ['cost' => 12];
    return password_hash($clave, PASSWORD_BCRYPT, $options);
}

function getCoordinatesFromIP() {
    $ipdataAPI = 'https://api.ipdata.co/?api-key=ff81b56be4a8b66caa90925416a10c3405e46c5043c162af5dd5a271';
    $data = json_decode(file_get_contents($ipdataAPI));

    if ($data && isset($data->latitude) && isset($data->longitude)) {
        return ['LAT' => $data->latitude, 'LON' => $data->longitude];
    } else {
        return ['LAT' => null, 'LON' => null];
    }
}

function optimizePhoto($photo) {
    $aPhoto = explode(",", $photo);

    if (count($aPhoto) != 2) {
        // El formato de la imagen no es válido
        return $photo;
    }

    $size = strlen($aPhoto[1]);
    $imageData = base64_decode($aPhoto[1]);

    if ($imageData === false) {
        // La decodificación de la imagen falló
        return $photo;
    }

    $resolution = @getimagesizefromstring($imageData);

    if ($resolution === false) {
        // No se pudo obtener el tamaño de la imagen
        return $photo;
    }

    if ($size > 64000 || $resolution[0] > 300 || $resolution[1] > 300) {
        $newPhoto = new Imagick();
        $newPhoto->readImageBlob($imageData);
        $newPhoto->resizeImage(128, 128, Imagick::FILTER_LANCZOS, 1, 1);
        $photo = $aPhoto[0] . "," . base64_encode($newPhoto);
        $newPhoto->destroy();
    }

    return $photo;
}
?>

