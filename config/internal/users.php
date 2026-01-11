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
                    alert('Password successfully changed.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            } else {
                $msg = "New passwords do not match.";
                $msgType = "red";
            }
        } else {
            $msg = "Old password is incorrect.";
            $msgType = "red";
        }
    } else {
        $msg = "User not found.";
        $msgType = "red";
    }
}

if (isset($_POST['uploadProfilePic'])) {
    $currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
    $usersDir = dirname(__DIR__) . "/users/";

    if ($currentUser && isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $imageInfo = getimagesize($_FILES['profilePic']['tmp_name']);
        if ($imageInfo !== false) {
            $mime = $imageInfo['mime'];
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

            if (array_key_exists($mime, $allowedTypes)) {
                $ext = $allowedTypes[$mime];
                $targetFile = $usersDir . $currentUser . "." . $ext;

                if (file_exists($usersDir . $currentUser . ".jpg")) unlink($usersDir . $currentUser . ".jpg");
                if (file_exists($usersDir . $currentUser . ".png")) unlink($usersDir . $currentUser . ".png");

                if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetFile)) {
                    echo "<script>alert('Profile picture updated.'); window.location.href = window.location.href;</script>";
                    exit;
                } else {
                    $msgPic = "Error saving file.";
                }
            } else {
                $msgPic = "Invalid file type. Only JPG and PNG allowed.";
            }
        } else {
            $msgPic = "File is not an image.";
        }
    } else {
        $msgPic = "Upload failed.";
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

<hr>

<h3>Profile picture</h3>

<?php if (isset($msgPic)) { echo "<p style='color: red;'>$msgPic</p>"; } ?>

<?php
$currentUser = isset($_COOKIE['account']) ? basename($_COOKIE['account']) : '';
$usersDir = dirname(__DIR__) . "/users/";
$picPath = "";

if (file_exists($usersDir . $currentUser . ".jpg")) {
    $picPath = "/config/users/" . $currentUser . ".jpg";
} elseif (file_exists($usersDir . $currentUser . ".png")) {
    $picPath = "/config/users/" . $currentUser . ".png";
}

if ($picPath) {
    echo "<img src='$picPath?t=" . time() . "' alt='Profile Picture' style='max-width: 150px; max-height: 150px; border-radius: 10px; display: block; margin-bottom: 10px;'>";
}
?>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="file" name="profilePic" accept="image/png, image/jpeg" required>
    <button type="submit" name="uploadProfilePic">Upload picture</button>
</form>

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

            $password = $userData['passwd'] ?? 'Not set';
            echo "<li><strong>Username:</strong> " . htmlspecialchars($username) . " | <strong>Password hash:</strong> " . htmlspecialchars($password) . "</li>";
        }
        echo "</ul>";
    }
}
?>