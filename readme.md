# mhd-panel

## Je třeba, aby soubor config.json nebyl přístupný přes webový server
Otestujte tuto skutečnost tak, že ve vašem prohlížeči, který vidí na server, zkusíte http://adresaServeru/config.json a pokud se soubor načte, je třeba provést příslušné nastavení webového serveru, aby nebylo možné soubor zobrazit. V opačném případě může dojít k odhalení vašeho nastavení serveru, včetně klíče pro přístup k datům třetích stran.

## Kiosk mode
`chromium --kiosk "https://www.example.com"`

## Pokud nefunguje získání dat přes php (projevuje se bílou obrazovkou, protože php neví, co má dělat, protože nezná curl)

nainstalovat potřebné php moduly:

`sudo apt install php-curl php-xml php-mbstring`

přidat do /etc/php/[verzephp]/apache2/php.ini text:

```
extension=curl
extension=xml
```

po každé takové změně restartovat apache server:

`sudo systemctl restart apache2`

## Odkaz na dokumentaci api, co je používáno

https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards
