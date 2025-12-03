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
        <button type="button" onclick="login()">Login</button>

        <script>

            async function login() {
                const username = document.getElementById('username').value;
                const passwd = document.getElementById('passwd').value;

                const hashedPassword = await hashString(passwd);

                document.cookie = "account=" + username + "; path=/";
                document.cookie = "passwd=" + hashedPassword + "; path=/";

                window.location.replace("./");
            }

            async function hashString(inputString) {
                const encoder = new TextEncoder();
                const data = encoder.encode(inputString);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);

                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

                return hashHex;
            }

        </script>

    </div>

</body>

</html>