# Projektmenedzser modul az EnvientaPlatform -hoz

Ezt a wb applikációt az EnvientaPlatform hívja iframe -ben. Ez az applikáció  futása során  REST API hívással adatokat kér le az EnvientaPlatform -ból .
 (Természetesen a megfelelő paraméterek beállításával és a szükséges API biztositásával máshonnan is hívható)

## Tulajdonságok

-  Projekt feladatok kezelése (új létrehozása, modosítás, törlés),
-  Feladatok áttekinthető vizuális megjelenítése (canbas tábla),
-  Projekt résztvevőinek megjelenítése,
-  Projekt adminisztrátorok kezelése (kijelölés, törlés),
-  Feladat "föggőségek" kezelése (pl. az "x" feladat munkálatai csak akkorkezdhetők meg ha az "y" és "z" feladat már le van zárva)
-  Töbnnyelvü kezelő felület támogatás,
-  Testreszabható megjelenés,
-  Több kliens egyidejű munkájának támogatása (5 másodpercenként ellenörzi az adatbázis változásokat és szükség esetén frissiti a képernyőt)

## Jogosultságok

**A project adminisztrátorok lehetőségei:**

- Feladatok áttekinthető vizuális megjelenítése (canbas tábla),
- projekt tagjainak megtekintése,
- új feladatok felvitele,,
- feladatok modosítása (beleértve a felelőshöz rendelést),
- feladatok törlése,
- további projekt adminisztrátorok kijelölése,
- projekt adminisztrátori jogok megvonása

**A projekt (nem adminisztrátor) tagjainak lehetőségei:**
 
- Feladatok áttekinthető vizuális megjelenítése (canbas tábla),
- projekt tagjainak megtekintése,
- a még felelős nélküli  feladatokat magához rendelheti (elvállalja a feladat végrehajtását),
- a hozzá rendelt feladatok stáruszának módosítása,
- projekt adminisztrátorok megtekintése

**A többi látogató lehetőségei:**

- Feladatok áttekinthető vizuális megjelenítése (canbas tábla),
- projekt tagjainak megtekintése,
- projekt adminisztrátorok megtekintése

## A feladatok adatai
- feladat automatikusan képzödő azonosító száma,
- feladat rövid megnevezése,
- feladat szöveges leírása,
- tipus (kérdés, javaslat, hiba, egyéb),
- státusz (várakozik, inditható, munkában, ellenörizendő, ellenörzés alatt, lezárt),
- inditási feltétel (azon feladatok azonosítóinak a listája, melyeknek lezárt állapotban kell lenniük ahoz, hogy ez a feladat megkezdhető legyen)


## Programnyelvek, keret rendszerek

- PHP 7.0.33+, 
- Javascript, 
- JQuery 1.12.4+, 

Csak az unittestek számára:

- phpunit 6.5.14+
- Nodejs 8.9.4+
- mocha 6.0.2+ nodejs modul
- mocha-jsdom 2.0.0+  nodejs modul
- mocha-rcov-reporter 1.3.0+ node.js modul 
- jquery 3.3.1+ nodejs modul 
- jscover 1.0.0+ nodejs modul


A repo tartalmaz mysql interface-t (database.php), de ez jelenleg nincs használva. Az adat tárolás most json Text fileokban van megoldva.

## Licensz

GNU/GPL

## Élő demó

http://szeszt.tk/projektmanager

## Hívása az EnvientaPlatform -ból

HTML:
```
<h2>{{projectTitle}}</h2>
<iframe id="ifrmProjectManager" src="" width="1240" height="850"></iframe>
```

JavaScript:
```
var sessionId = "{{ $request->session(); }}";
var projectId = "........";
var apiURL = "https://platform.envienta.org/api/projectinfo";.
$('#ifrmProjectmanager').src = "https://szeszt.tk/projectmanager/app.php"+
"?callerapiurl="+apiURL+
"&sessionid="+sesionId+"&projectid="+projectId;

```
Opcionális további URL paraméterek:
```
&lng=hu vagy &lng=en
&css=cssFileURL
```

###Az EnvientaPlatform -ban megvalósítandó Rest API

az API  **apiURL**/ **sessionid** / **projectid**    http -url -el van hívva.

pl: https:/platform.envienta.org/api/projectinfo/abc....de/ef12.....23

visszadnia "json" content tipusban egy json stringet kell:
```
{"users":[[avatarURL, nickName], ....],
  "admins":[avatarURL],
  "loggedUser":avatarURL
}

```
 Ahol:
 **avatarURL**: string user gravatar vagy facebook profilképre mutató
  URL

**nicName** : string user nick neve

**admins**: array of string  project adminisztrátorok avatarURL -jei

**loggedUser**: string a bejelentkezett felhasználó avatarURL -je, vagy üres string

Példa:
```
<?php
header('Content-Type: json');
echo '
{"users":[
["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5","utopszkij"],
["https://www.gravatar.com/avatar/12345e6e2dc8b37f24ddb47dfb7e3eb5","user1j"],
["https://www.gravatar.com/avatar/45670e6e2dc8b37f24ddb47dfb7e3eb5","user2j"],
],					                    ],
"admins":["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5"],
"loggedUser":"https://www.gravatar.com/avatar/45670e6e2dc8b37f24ddb47dfb7e3eb5"
}
';
?>
```

## Unit tesztek
```
cd repoRoot
./tests/test.sh

```
## SonarCloud kód minőség ellenörzés 
telepitve kell lennie a sonarclod kliensnek az /usr/local/sbin/sonar könyvtárba
```
cd repoRoot
./tests/sonar.sh
```
Utolsó ellenörzés eredménye:

https://sonarcloud.io/dashboard?id=projectmanager

## Telepitése web szerverre
A szerver documentroot-ba:
framework.php, style.css, .htaccess (rename a htaccess.txt -t), app.php  fileok,

controllers, images, js, langs, models, projects, views alkönyvtárak

## Programozó

Fogler Tibor    tibor.fogler@gmail.com

