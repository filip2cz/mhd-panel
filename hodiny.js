// enter some javascript here and it will run
// on every page on this domain (location.host)
// Vytvoření HTML prvku pro zobrazení času
const clockElement = document.createElement('div');
clockElement.id = 'clock';

// Stylování prvku pomocí CSS
clockElement.style.position = 'absolute';
clockElement.style.bottom = '20px';
clockElement.style.border = '1px solid black';
clockElement.style.fontFamily = 'Arial, sans-serif';
clockElement.style.fontSize = '64px';
clockElement.style.color = "white";
clockElement.style.left = '50%'; // Umístění doprostřed horizontálně
clockElement.style.transform = 'translateX(-50%)'; // Posun zpět o polovinu šířky pro přesné vycentrování
clockElement.style.zIndex = '10'; // Zajistí, že bude překrývat ostatní prvky

// Přidání prvku do těla stránky
document.body.appendChild(clockElement);

// Funkce pro aktualizaci času
function updateTime() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    clockElement.textContent = `${hours}:${minutes}:${seconds}`;
}

// Aktualizace času každou sekundu
setInterval(updateTime, 1000);

// Aktualizace času při načtení stránky
updateTime();
