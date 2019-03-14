# Projektmenedzser modul az EnvientaPlatform -hoz

Ezt a wb applikációt az EnvientaPlatform hívja iframe -ben. Ez az applikáció  futása során  REST API hívással adatokat kér le az EnvientaPlatform -ból .
 (Természetesen a megfelelő paraméterek beállításával és a szükséges API biztositásával máshonnan is hívható)

## Tulajdonságok

-  Projekt feladatok kezelése (új létrehozása, modosítás, törlés),
-  Feladatok áttekinthető vizuális megjelenítése (canbas tábla),
-  Projekt résztvevőinek megjelenítése,
-  Projekt adminisztrátorok kezelése (kijelölés, törlés),
-  Feladat "föggőségek" kezelése (pl. az "x" feladat munkálatai csak akkorkezdhetők meg ha az "y" és "z" feladat már le van zárva)

##Jogosultságok

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

PHP, Javascript, JQuery

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
###Az EnvientaPlatform -ban megvalósítandó Rest API

az API  **apiURL**/<sessionid>/<projectid> http -url -el van hívva.

pl: https:/platform.envienta.org/api/projectinfo/abc....de/ef12.....23

visszadnia "json" mime tipusban egy json stringet kell:
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
{"users":[
["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5","utopszkij"],
["https://www.gravatar.com/avatar/12345e6e2dc8b37f24ddb47dfb7e3eb5","user1j"],
["https://www.gravatar.com/avatar/45670e6e2dc8b37f24ddb47dfb7e3eb5","user2j"],
],					                    ],
"admins":["https://www.gravatar.com/avatar/2c0a0e6e2dc8b37f24ddb47dfb7e3eb5"],
"loggedUser":"https://www.gravatar.com/avatar/45670e6e2dc8b37f24ddb47dfb7e3eb5"
}
```


## Programozó

Fogler Tibor    tibor.fogler@gmail.com

