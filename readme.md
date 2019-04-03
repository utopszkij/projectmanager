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
- JQuery 1.12.1+, 

A repo tartalmaz mysql interface-t (database.php), de ez jelenleg nincs használva. Az adat tárolás most json Text fileokban van megoldva.

## Használt külső szolgáltatások, erőforrások:
- jquery.com    (V 1.12.1)

Az unittestek, forrás kód kezelés, és a kód minőség ellenörzéshez:

- phpunit 6.5.14+
- Nodejs 8.9.4+
- npm 6.9+
- mocha 6.0.2+ nodejs modul
- mocha-jsdom 2.0.0+  nodejs modul
- mocha-rcov-reporter 1.3.0+ node.js modul 
- jquery 3.3.1+ nodejs modul 
- jscover 1.0.0+ nodejs modul
- sonarcloud kliens
- github kliens

- github.com
- travis-ci.org
- sonarcloud.io

## Felesztői környezet kialakítása
- eclipse telepítése
	lásd: https://www.ics.uci.edu/~pattis/common/handouts/pythoneclipsejava/eclipsepython%20oxygen.html
- github fiók létrehozása a https://github.com -on (sign up klick)
- github kliens telepítése, konfigurálása
	lásd: https://help.github.com/en/desktop/getting-started-with-github-desktop/installing-github-desktop
- travis fiók létrehozása a https://travis-ci.org -on a github bejelentkezés segitségével (sign up with GitHub klick), github hozzáférés engedélyezése
	  lásd: https://docs.travis-ci.com/user/tutorial/
- sonarcloud fiók létrehozása, a https://sonarcloud.io -n a github bejelentkezés segitségével (login klick)
- ennek a reponak a klonozása a saját gépre, tests/sonar-orig.sh másolása a másolat neve: tests/sonar.sh 
  (eclipse /git repositories/clone/github, majd a file/open projects from file system)
- a saját gépen lévő repo publikásáa a saját github fiokba
  (új projekt létrehozása a github web felülten, majd a saját gépen git remote add,  git add ., git commit, git push)
- travisban bekapcsolni a megfelelő github repo kezelését (My repositores + klick)
- sonarcloudban új projekt létrehozás, manuális beállítással, a kapott project key-t a képernyöről beirni a saját repoban lévő tests/test.sh -ba a Dsonar.login= -hoz. a sonacloud project beállításainál megadni a coverage report fájlok pontos elérési utvonalait és fájl neveit. (Administration/General/PHP és Administration/General/Javascript klick)

A tests/...Test.php valamint a tests/...Test.js fájlok az unittest definiciók.


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
```
cd repoRoot
./tests/sonar.sh
```
Utolsó ellenörzés eredménye:

https://sonarcloud.io/dashboard?id=utopszkij-projectmanager

## Telepitése web szerverre
A szerver documentroot-ba:
framework.php, app.php, index.html, style.css, .htaccess (htaccess.txt átnevezve),  fileok,

controllers, images, js, langs, models, projects, views alkönyvtárak,

a projects alkönyvtár és tartalma legyen irható a web szerver és a php számára.

## Programozó

Fogler Tibor    tibor.fogler@gmail.com

