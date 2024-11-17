<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>Smart panel: PID</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta http-equiv="refresh" content="20;url=pocasi.php">
</head>

<body>

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <div class="container">

        <h1>Odjezdy z Šestajovice, Za Stodolami</h1>

        <script>
            var xhr = new XMLHttpRequest();

            // https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards

            // Šestajovice Balkán
            //xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U1613Z1%22%2C%20%22U1613Z2%22%5D%7D&limit=30&minutesAfter=360', true);

            // Šestajovice Za Stodolami
            xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U1500Z1%22%2C%20%22U1500Z2%22%5D%7D&limit=30&minutesAfter=360', true);

            // Bazar
            //xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U18Z1P%22%2C%20%22U18Z1%22%2C%20%22U18Z2P%22%2C%20%22U18Z2%22%5D%7D&limit=30&minutesAfter=360', true);

            xhr.onreadystatechange = function () {
                if (this.readyState !== 4) return;
                if (this.status !== 200) return; // or whatever error handling you want

                var rawResponse = this.responseText;

                // console.log(rawResponse); // Zobraz čistý text v konzoli

                // Výpis JSON do <pre>
                // document.getElementById('testOutput').innerHTML = this.responseText;

                // Parsování dat
                const data = JSON.parse(rawResponse);

                // Výpis
                const result = data[0].map(entry => {
                    const bus = entry.route.short_name;
                    const time = new Date(entry.departure.timestamp_scheduled).toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
                    const delay = entry.departure.delay_seconds;
                    const headsign = entry.trip.headsign;

                    if (delay != null) {
                        delayMinutes = Math.floor(Number(delay) / 60);
                    }

                    if (delay == null || delay == "0") {
                        return `${bus} | ${headsign} | ${time}`;
                    }
                    else {
                        return `${bus} | ${headsign} | ${time} | ${delayMinutes} minut`;
                    }
                }).join("<br>---------------------------------------------------<br>");

                /*
                // Výpis do konzole
                const result = data[0].map(entry => {
                    const bus = entry.route.short_name;
                    const time = new Date(entry.departure.timestamp_scheduled).toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
                    return `${bus} - ${time}`;
                }).join(", ");
                */

                // Zobrazení uživateli
                document.getElementById("busSchedule").innerHTML = result;
            };

            xhr.send();
        </script>

        <div class="output">Číslo | Směr | Čas | Zpoždění</div>
        <div class="output">---------------------------------------------------</div>
        <p class="output" id="busSchedule"></p> <!-- Sem se vypíší data -->

        <pre id="testOutput"></pre>

        <script src="hodiny.js"></script>

        <script>
            // Funkce pro přesměrování na jinou stránku
            document.addEventListener("click", function () {
                window.location.href = "http://localhost/pocasi.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

    </div>

</body>

</html>