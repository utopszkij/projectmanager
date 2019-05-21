<?php
DEFINE('LNGDEF',1);
DEFINE('WAITING','Várakozik');
DEFINE('CANSTART','Inditható');
DEFINE('ATWORK','Munkában');
DEFINE('CANVERIFY','Ellenőrizendő');
DEFINE('ATVERIFY','Ellenörzés alatt');
DEFINE('CLOSED','Lezárt');
DEFINE('NEWTASK','Új feladat');
DEFINE('OK','Tárol');
DEFINE('YES','Igen');
DEFINE('NO','Nem');
DEFINE('CANCEL','Mégsem');
DEFINE('CLOSE','Bezár');
DEFINE('DELTASK','Feladat törlése');
DEFINE('QUESTION','Kérdés');
DEFINE('BUG','Hiba');
DEFINE('SUGGEST','Javaslat');
DEFINE('OTHER','Egyéb');
DEFINE('TASK','Feladat');
DEFINE('ASSIGN','Felelős');
DEFINE('TITLE','Cím');
DEFINE('DESC','Leírás');
DEFINE('TYPE','Tipus');
DEFINE('STATE','státusz');
DEFINE('REQ','Inditási feltétel');
DEFINE('REQHELP','lezárt task ID-k listája');
DEFINE('INFO','
<ul>
	<li>Projekt vezető szabadon hozzá rendelhet taskokat a projekt tagjaihoz, modosithatja a hozzárendeléseket (kiosztja a feladatot a tagoknak),</li>
	<li>A többi projekt tag, a még máshoz nem rendelt taskokat saját magához rendelheti (vállalkozik a feladat elvégzésére),</li>
	<li>Ha olyan taskot próbálunk "inditható" státuszba tenni aminél az "inditási feltétel"-ben felsorolt taskok mindegyike nincs lezárva akkor figyelmeztetést kapunk</li>
	<li>A taskok egérrel húzhatóak, kattintással megnézhetőek/szerkeszthetőek</li>
</ul>
');
DEFINE('ACCESSDENIED','Hozááférés letiltva');
DEFINE('NOTSTARTING','Az inditási feltételek nem teljesülnek');
DEFINE('TASKNOTFOUND','A feladat nem található az adatbázisban');
DEFINE('WRONGSESSION','SESSION hiba -- lejárt a timelimit?');
DEFINE('MEMBERS','Tagok');
DEFINE('ADMIN','Project adminisztrátor');
?>