<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Settings</title>
</head>

<body>

    <link rel="stylesheet" type="text/css" href="../main.css">

    <?php

    if (isset($_COOKIE['account']) && isset($_COOKIE['passwd'])) {
        if (file_exists("./users/" . $_COOKIE['account'] . ".json")) {
            $jsonfile = file_get_contents("./users/" . $_COOKIE['account'] . ".json");
            $account = json_decode($jsonfile, true);
            if (isset($account['passwd'])) {
                if ($account['passwd'] == $_COOKIE['passwd']) {
                    require './internal/config.php';
                } else {
                    echo '<h1 style="color: red">';
                    echo "ERROR: bad password or username";
                    echo '</h1>';
                    echo '<meta http-equiv="refresh" content="5;url=./login.php">';
                }
            } else {
                echo "error: no password set";
                echo '<meta http-equiv="refresh" content="5;url=./login.php">';
            }
        } else {
            echo '<h1 style="color: red">';
            echo "ERROR: bad password or username";
            echo '</h1>';
            echo '<meta http-equiv="refresh" content="5;url=./login.php">';
        }
    } else {
        echo '<meta http-equiv="refresh" content="0;url=./login.php">';
    }

    ?>

</body>

</html>