<?php
/**
 * Vstupní bod aplikace (splash screen).
 * Zobrazuje IP adresu zařízení a po 10 sekundách přesměrovává na hlavní tabuli.
 */
?>
<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10; url=mhd-tabule.php">
    <title>Přesměrování...</title>
</head>

<body>

    <link rel="stylesheet" type="text/css" href="./main.css">

        <h1>Device info</h1>

        <p>Local IP adress:

            <?php
            // Získání lokální IP adresy serveru
            $local_ip = shell_exec("hostname -I");

            // Vypsání IP adresy na stránku
            echo $local_ip;
            ?>

        </p>

</body>

</html>