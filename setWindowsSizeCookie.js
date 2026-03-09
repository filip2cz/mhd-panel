function setWindowSizeCookie() {
    document.cookie = "window_height=" + window.innerHeight + "; path=/";
    document.cookie = "window_width=" + window.innerWidth + "; path=/";
}

// Zavoláme při načtení a změně velikosti okna
window.onload = setWindowSizeCookie;
window.onresize = setWindowSizeCookie;