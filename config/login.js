/**
 * Zpracuje přihlášení uživatele.
 * Načte hodnoty z formuláře, zahashuje heslo a uloží údaje do cookies.
 * Poté přesměruje na index administrace.
 */
async function login() {
    const username = document.getElementById('username').value;
    const passwd = document.getElementById('passwd').value;
    const errorMsg = document.getElementById('errorMsg');

    if (errorMsg) errorMsg.innerText = "";

    const hashedPassword = await hashString(passwd);

    document.cookie = "account=" + username + "; path=/";
    document.cookie = "passwd=" + hashedPassword + "; path=/";

    const response = await fetch("./");
    const text = await response.text();

    if (text.includes("ERROR: bad password or username") || text.includes("error: no password set")) {
        if (errorMsg) errorMsg.innerText = "Incorrect username or password.";
    } else {
        window.location.replace("./");
    }
}

/**
 * Vypočítá SHA-256 hash ze zadaného řetězce.
 *
 * @param {string} inputString Vstupní řetězec (heslo).
 * @return {Promise<string>} Hexadecimální reprezentace hashe.
 */
async function hashString(inputString) {
    const encoder = new TextEncoder();
    const data = encoder.encode(inputString);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);

    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

    return hashHex;
}

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('loginBtn').addEventListener('click', login);
});