<?php
/**
 * Přihlašovací stránka do administrace.
 *
 * Obsahuje formulář pro zadání uživatelského jména a hesla.
 * Heslo je hashováno na straně klienta (SHA-256) před uložením do cookies.
 */
?>
<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>login</title>
</head>

<body>

    <link rel="stylesheet" type="text/css" href="../main.css">

    <div class="stranka">

        <h1>Login page</h1>

        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br>
        <br>
        <label for="passwd">Password:</label><br>
        <input type="password" id="passwd" name="passwd"><br>
        <br>
        <button type="button" id="loginBtn">Login</button>

    </div>

    <script src="login.js"></script>

</body>

</html>