<?php
/*
BGphpBible - php version of CD Bible project (www.vanyog.com/bible)
Copyright (C) 2006  Vanyo Georgiev <info@vanyog.com>

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

$pth='38/';
include("structure.php");
$language='bg';
include("functions.php");
get_query();

$bk-=39;
//echo "$bk $ch $vr"; die;

$a='';
switch ($bk){
 case 1: $r='gospels'; $f=3; break;
 case 2: $r='gospels'; $f=31; break;
 case 3: $r='gospels'; $f=47; break;
 case 4: $r='gospels'; $f=69; break;
 case 5: $r='vII'; $f=0; if ($ch>12) $f++; break;
 case 6: $r='vII'; $f=29; if ($ch==1) $a='030a.php'; break;
 case 7: $r='vII'; $f=34; if ($ch==1) $a='035a.php'; break;
 case 8: $r='vII'; $f=39; if ($ch==1) $a='040a.php'; break;
 case 9: $r='vII'; $f=42; if ($ch==1) $a='043a.php'; break;
 case 10: $r='vII'; $f=47; break;
 case 11: $r='vII'; $f=48; break;
 case 12: $r='vII'; $f=49; break;
 case 13: $r='vII'; $f=50; if ($ch==1) $a='051a.php'; break;
 case 14: $r='vII'; $f=66; if ($ch==1) $a='066a.php'; break;
 case 15: $r='vII'; $f=82; if ($ch==1) $a='083a.php'; break;
 case 16: $r='vIII'; $f=0; break;
 case 17: $r='vIII'; $f=6; break;
 case 18: $r='vIII'; $f=12; break;
 case 19: $r='vIII'; $f=16; break;
 case 20: $r='vIII'; $f=20; if ($ch==1) $a='021a.php'; break;
 case 21: $r='vIII'; $f=25; break;
 case 22: $r='vIII'; $f=28; if ($ch==1) $a='029a.php'; break;
 case 23: $r='vIII'; $f=34; if ($ch==1) $a='035a.php'; break;
 case 24: $r='vIII'; $f=38; if ($ch==1) $a='039a.php'; break;
 case 25: $r='vIII'; $f=41; break;
 case 26: $r='vIII'; $f=42; break;
 case 27: $r='vIII'; $f=55; if ($ch==1) $a='056a.php'; break;
}

switch ($bk){
 case 3:
  if ($ch==16) $a='062a.php';
  if ($ch>16) $f=$f-1; 
  if ($ch==18) $a='063a.php';
  if ($ch>18) $f=$f-1;
  break;
 case 27:
  if ($ch==22) $f=$f+1;
  break;
}

$f=$f+$ch;

if (!$a) {
 if ($f<10) $a="00$f.php"; else $a="0$f.php";
}

$l='';
if ($vr>1) $l="#v$vr";

header(
//echo(
//'Location: http://'.$_SERVER['HTTP_HOST']."/rigs/$r/$a$l");
"Location: http://trinity-bg.org/rigs/$r/$a$l");

?>
