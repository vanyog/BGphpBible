<?php

// този масив описва кои версии на Библията да се показват
$version = array(
'Ort/'=>'Православна Библия 1991',   
'38/'=>'Библия 1940',
//'Tzrg-f/'=>'"Цариградска" Библия',
'Tzrg/'=>'"Цариградски" Нов завет',
'Veren/'=>'Издателство &quot;Верен&quot;',
//'mk/'=>'Македонска Библия 1999',
//'Mac1/'=>'Македонска Библия 1990',
//'Srb/'=>'Сръбска Библия (Дан-Караджич)',
'Ru/'=>'Библията на руски',
'Asv/'=>'American Standard Version',
//'KjvSn/'=>"King James with Strong's numbers"
'KJV/'=>"King James Version",
'Gr/'=>"Старогръцки Нов завет"
);

// този масив описва кои версии на Библията се намират на други сайтове
$on_other_sites = array(
'mk/'=>"http://mkbible.net/biblija/index.php",
'Mac1/'=>"http://mkbible.net/biblija/index.php",
'Srb/'=>"http://mkbible.net/biblija/index.php"
//,'KJV/'=>"http://mkbible.net/biblija/index.php"
);

$default_version='38/'; // Библия, която се отваря по подразбиране
$language='bg';         // Език на интерфейса на сайта
$motranslator=true;     // Дали да се ползват скриптовете motranslator.js
$search_edit_size=40;   // Ширина на текстовото поле за въвеждане на текст за търсене

// функция за определяне езика на версиите на Библията
function version_languege($pth){
switch ($pth){
 case 'Asv/': return 'en0'; break;
 case 'KJV/': return 'en0'; break;
 case 'KjvSn/': return 'en0'; break;
 case 'Mac07/': return 'ma'; break;
 case 'Ru/': return 'ru'; break;
 case 'Gr/': return 'gr'; break;
 default: return 'bg';
}
}

?>
