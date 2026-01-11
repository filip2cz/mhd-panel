# Programátorská dokumentace - MHD Panel

Tento dokument popisuje architekturu, konfiguraci a fungování aplikace MHD Panel, která slouží jako informační tabule pro zobrazení odjezdů hromadné dopravy, počasí a dalších informací.

## Přehled architektury

Aplikace je postavena na technologii **PHP** a běží na webovém serveru (např. Apache). Frontend je tvořen HTML/CSS/JS a je navržen pro zobrazení v režimu kiosku (full-screen prohlížeč), s volitelnou možností dotykového displeje.

### Hlavní komponenty

1.  **Jádro aplikace (`mhd-tabule.php`)**: Řídí zobrazení hlavní tabule.
2.  **Modul počasí (`pocasi.php`)**: Získává data o teplotě z externích zdrojů.
3.  **Modul mapy (`mhd-mapa.php`)**: Zobrazuje interaktivní mapu.
4.  **Konfigurace (`config.json`)**: Ukládá nastavení aplikace.

## Popis souborů

### `index.php`

Vstupní bod aplikace. Stránka, co se zobrazí při spuštění panelu, kde se pro potřeby ladění nachází aktuální ip adresa serveru.

### `mhd-tabule.php`
Hlavní vstupní bod aplikace.
*   **Načítání konfigurace**: Čte soubor `config.json`.
*   **Responzivita**: Pomocí cookies `window_height` a `window_width` vypočítává proměnnou `$mhdLimit` (počet řádků odjezdů), aby se obsah vešel na obrazovku bez scrollování.
*   **Routing datových zdrojů**: Na základě URL v `mhdUrl` rozhoduje, zda načíst:
    *   `pid-public-departs.php` (pro Golemio API/PID)
    *   ~~`mpvnet.php` (pro MPVNet)~~ (comming soon)
*   **Refresh**: Obsahuje JavaScript `setInterval` pro automatické obnovení stránky dle parametru `refreshTime`.
*   **Include**: Zahrnuje pod-moduly `missing-person.php` a `footer.php`.

### `pocasi.php`
Zajišťuje získání aktuální teploty.
*   **Funkce `ziskejTeplotu($url)`**:
    *   Přijímá URL zdroje počasí.
    *   Podporuje parsování JSON z **Open-Meteo** (`api.open-meteo.com`).
    *   Podporuje scraping HTML z **Meteo-Počasí** (`meteo-pocasi.cz`) pomocí `DOMDocument` a `DOMXPath` (hledá třídy `status_meteo_text` a `svalue`).
*   **Failover**: Pokud stahování nebo parsování selže (blok `try-catch`), funkce rekurzivně volá sama sebe s dalším zdrojem v poli `$weatherSources`.

### `mhd-mapa.php`
Zobrazuje mapu v celoobrazovkovém režimu.
*   Obsahuje `<iframe>` s URL definovanou v `mapUrl`.
*   Obsahuje meta tag pro automatické přesměrování zpět na `mhd-tabule.php` po 300 sekundách (5 minutách).

### `config.json`
Konfigurační soubor ve formátu JSON.
**Důležité:** Tento soubor nesmí být veřejně přístupný z webu (viz `readme.md`).

| Klíč | Popis |
| --- | --- |
| `mhdUrl` | URL API pro odjezdy (Golemio nebo MPVNet). |
| `mhdApiKey` | API klíč (pokud je vyžadován, např. Golemio). |
| `zastavka` | Název zastávky zobrazený v hlavičce. |
| `refreshTime` | Interval obnovení stránky v sekundách. |
| `weatherUrl` | Pole řetězců s URL adresami služeb pro počasí. |
| `mapUrl` | URL adresa mapy. |
| `enableMap` | Povolení zobrazení mapy (0/1). |
| `missingPerson` | Povolení modulu pohřešovaných osob ("true"/"false"). |

### Adresář `config/`
Obsahuje chráněnou sekci pro nastavení aplikace přes webové rozhraní.
*   **`index.php`**: Řídí přístup do administrace.
    *   Ověřuje existenci a správnost přihlašovacích údajů uložených v cookies (`account`, `passwd`).
    *   Porovnává SHA-256 hash hesla s údaji v `users/`.
    *   Pokud je autorizace úspěšná, vkládá obsah ze souboru `internal/config.php`. V opačném případě přesměruje na `login.php`.
*   **`login.php`**: Přihlašovací stránka. Zpracovává vstup uživatele a nastavuje autentizační cookies.
*   **`style.css`**: Styly specifické pro administrační rozhraní.

#### Podadresář `config/internal/`
*   **`config.php`**: Obsahuje formulář a logiku pro úpravu souboru `config.json`. Tento soubor je načítán (`require`) uvnitř `index.php` pouze po úspěšném ověření.

#### Podadresář `config/users/`
*   Ukládá data uživatelů ve formátu JSON.
*   Název souboru odpovídá uživatelskému jménu (např. `admin.json`).
*   Struktura přiložená v souboru `admin.json.example`

## Závislosti

*   **PHP Rozšíření**:
    *   `curl`: Pro HTTP požadavky na API.
    *   `xml`: Pro práci s DOMDocument (scraping počasí).
    *   `mbstring`: Pro práci s řetězci.
*   **Externí API**:
    *   Golemio (PID) nebo MPVNet (odjezdy).
    *   Open-Meteo nebo Meteo-Počasí (teplota).

## Bezpečnost

*   **Ochrana konfigurace**: Soubor `config.json` obsahuje citlivé údaje (API klíče). Webový server musí být nakonfigurován tak, aby odmítal přímé požadavky na tento soubor.
*   **XSS**: Výstupy proměnných do HTML jsou ošetřeny pomocí `htmlspecialchars()` (např. v `pocasi.php`, `mhd-tabule.php`).

## Klientská část

*   **JavaScript**:
    *   `panel.js`: Obsahuje logiku pro frontend.
    *   Inline skripty v `mhd-tabule.php` řeší refresh a přesměrování při kliknutí.
*   **CSS**:
    *   `main.css`: Styly pro tmavý režim (`data-bs-theme="dark"`) a layout.
