<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10; url=mhd-tabule.php">
    <title>Přesměrování...</title>
</head>

<body>

    <link rel="stylesheet" type="text/css" href="./main.css">

    <h1>Connection check</h1>

    <script>
        // Define the URLs for redirection
        const onlinePage = './mhd-tabule.php';
        const offlinePage = './index.php';

        // Function to verify real internet connectivity, not just local network connection
        async function checkInternetConnection() {
            // First, check the browser's built-in status (fast but unreliable)
            if (!navigator.onLine) {
                return false;
            }

            // The browser says we are online, but it might just be a local router connection.
            // Let's do a real network request to 1.1.1.1 to verify actual internet access.
            try {
                // We add a timestamp to the URL to bypass the browser cache completely.
                // We use 'no-cors' mode so the browser doesn't block the request due to security rules.
                await fetch('https://1.1.1.1/?cachebuster=' + new Date().getTime(), {
                    mode: 'no-cors',
                    cache: 'no-store'
                });

                // If the fetch succeeds without throwing an error, we are truly online
                return true;
            } catch (error) {
                // A network error occurred (e.g., DNS resolution failed, timeout), meaning no internet
                return false;
            }
        }

        // Execute the check and redirect the user
        checkInternetConnection().then(isOnline => {
            if (isOnline) {
                window.location.href = onlinePage;
            } else {
                window.location.href = offlinePage;
            }
        });
    </script>

</body>

</html>