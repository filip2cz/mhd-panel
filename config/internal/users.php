<?php

if (isset($_POST['oldPassword'])) {
    $currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
    $userFile = dirname(__DIR__) . "/users/" . $currentUser . ".json";

    if ($currentUser && file_exists($userFile)) {
        $userData = json_decode(file_get_contents($userFile), true);
        $oldPassHash = hash('sha256', $_POST['oldPassword']);

        if (isset($userData['passwd']) && $userData['passwd'] === $oldPassHash) {
            if ($_POST['newPassword'] === $_POST['confirmPassword']) {
                $newPassHash = hash('sha256', $_POST['newPassword']);
                $userData['passwd'] = $newPassHash;
                
                file_put_contents($userFile, json_encode($userData, JSON_PRETTY_PRINT));

                echo "<script>
                    alert('Heslo bylo úspěšně změněno.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            } else {
                $msg = "Nová hesla se neshodují.";
                $msgType = "red";
            }
        } else {
            $msg = "Staré heslo není správné.";
            $msgType = "red";
        }
    } else {
        $msg = "Uživatel nenalezen.";
        $msgType = "red";
    }
}
?>

<h2>User settings</h2>

<?php if (isset($msg)) {
    echo "<p style='color: $msgType;'>$msg</p>";
} ?>

<form method="POST" action="" id="passwordForm">
    <div class="form-group">
        <label for="oldPassword">Old password:</label>
        <input type="password" id="oldPassword" name="oldPassword" required class="fullWidthInput">
        <br><br>

        <label for="newPassword">New password:</label>
        <input type="password" id="newPassword" name="newPassword" required class="fullWidthInput">
        <br><br>

        <label for="confirmPassword">Confirm new password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required class="fullWidthInput">
    </div>
    <br>
    <button type="submit">Change password</button>
</form>

<script>
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const oldPwd = document.getElementById('oldPassword');
    const newPwd = document.getElementById('newPassword');
    const confirmPwd = document.getElementById('confirmPassword');

    async function sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    if(oldPwd.value) oldPwd.value = await sha256(oldPwd.value);
    if(newPwd.value) newPwd.value = await sha256(newPwd.value);
    if(confirmPwd.value) confirmPwd.value = await sha256(confirmPwd.value);

    this.submit();
});
</script>

<hr>

<h2>Other user settings</h2>

<?php

$folderPath = dirname(__DIR__) . '/users/';

if (is_dir($folderPath)) {
    $files = glob($folderPath . '*.json');
    if ($files) {
        echo "<ul>";
        foreach ($files as $file) {
            $userData = json_decode(file_get_contents($file), true);
            if ($userData === null)
                continue;

            $username = basename($file, '.json');
            if (isset($_COOKIE['account']) && $username == $_COOKIE['account'])
                continue;

            $password = $userData['passwd'] ?? 'Nezadáno';
            echo "<li><strong>Username:</strong> " . htmlspecialchars($username) . " | <strong>Password hash:</strong> " . htmlspecialchars($password) . "</li>";
        }
        echo "</ul>";
    }
}
?>