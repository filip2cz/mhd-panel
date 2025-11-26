<?php

$url = 'https://mpvnet.cz/pid/tab/departures';

// Prepare the payload based on the curl --data-raw structure.
// Note: 'StopKey' value is a stringified JSON, so we encode the inner array first.
$innerStopKey = json_encode([
    'cat' => 2,
    'subCat' => 0,
    'stopNum' => 58797,
    'departures' => null
]);

$data = [
    'isDepartures' => true,
    'StopKey' => $innerStopKey
];

// Encode the final payload to JSON
$payload = json_encode($data);

$ch = curl_init();

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    // This handles the --compressed flag (gzip, deflate, br, etc.) automatically
    CURLOPT_ENCODING => '', 
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0',
        'Accept: */*',
        'Accept-Language: cs,en;q=0.5',
        // 'Accept-Encoding' is handled by CURLOPT_ENCODING, but we can leave it explicit if strictly needed
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest',
        'Origin: https://mpvnet.cz',
        'DNT: 1',
        'Sec-GPC: 1',
        'Connection: keep-alive',
        'Referer: https://mpvnet.cz/pid/tab',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'TE: trailers'
    ],
]);

$response = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);

if ($err) {
    // Output error if request fails
    echo "cURL Error #:" . $err;
} else {
    // Output the API response
    echo $response;
}
?>