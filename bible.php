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

function cite_bible($a,$b,$n,$c,$s){
global $option;
$bible_path=$option['absolute_path'].'/b/38/';
if ($s){ $s=' class="'.$s.'"'; }
// Четене на указателя към началото на първия стих
$pf=fopen($bible_path.'CompactPoint.bin','r');
fseek($pf,$n*4);
$posv=fread($pf,4);
$posv=ord($posv[0])+256*ord($posv[1])+256*256*ord($posv[2])+256*256*256*ord($posv[3]);
fclose($pf);
// Четене на стиховете
$bt="<p$s>".$a.':';
$tf=fopen($bible_path.'CompactText.bin','r');
fseek($tf,$posv);
for ($i=0;$i<$c;$i++){
 $vl=fread($tf,2); $vl=ord($vl[0])+ord($vl[1])*256;
 $vt=decode(fread($tf,$vl));
 $bt=$bt.$b.' '.$vt."<p$s>";
 $b=$b+1;
}
fclose($tf);
return $bt;
}

/*function decode($v){
$a=split('\|',$v);
$r=''; $y=true;
foreach($a as $p){
 if ($y){ $r=$r.$p; } else { $r=$r.'<i>'.$p.'</i>'; }
 $y=!$y;
}
return $r;
}*/

function decode($v){
$r=''; $it=false; $cm=false;
for($i=0;$i<strlen($v);$i++){
 switch ($v[$i]){
 case '|':
  if (!$it){ $r=$r.'<i>';} else { $r=$r.'</i>'; }
  $it=!$it;
  break;
 case '{': $cm=true; break;
 case '}': $cm=false; break;
 case "\r": $r=$r."<BR>"; break;
 default:
  if (!$cm){ $r=$r.$v[$i]; }
}
}
return $r;
}

?>
