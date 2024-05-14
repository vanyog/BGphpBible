<?php
/*
BGphpBible - php version of CD Bible project (www.vanyog.com/bible)
Copyright (C) 2024  Vanyo Georgiev <info@vanyog.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

header("Content-Type: text/html; charset=windows-1251");

// Осигурява zip компресиране на отговора
if(!ob_start("ob_gzhandler")) ob_start();

$pth = $_GET['ver'];
$fn = __DIR__.'/'.$pth.'about.html';

$fc = '';
if(file_exists($fn)) $fc = file_get_contents($fn);

$r = array();
$fb = '';
if(preg_match('/<body>(.*)<\/body>/si', $fc, $r)){
   $bf = str_replace(' src="', " src=\"$pth", $r[1]);
   echo $bf.'<p><button onclick="window.history.back()">Връщане</button>';
}

?>