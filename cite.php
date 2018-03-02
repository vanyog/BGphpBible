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

$bversion=ifposted("version","38/"); //echo $bversion; die;
$nocite=ifposted("nocite",0);

$tolink=array();

function cite2($start,$count,$text){ // показване на една глава от Библията
// $start - глобалния номер на първи стих от главата
// $count - броя стихове в главата
global $option,$bversion,$nocite,$tolink;
$bt='';
if (!$nocite){
 $text=targeted_text($text);
 if ($bversion=="38/")
 { $start=$start+23144; }
 $bible_path=$option['absolute_path'].'/b/'.$bversion.'/';
 // Четене на указателя към началото на първия стих
 $pf=fopen($bible_path.'CompactPoint.bin','r');
 fseek($pf,$start*4);
 $posv=fread($pf,4);
 $posv=ord($posv[0])+256*ord($posv[1])+256*256*ord($posv[2])+
   256*256*256*ord($posv[3]);
 fclose($pf);
 // Четене на стиховете
 $n=1;
 $tf=fopen($bible_path.'CompactText.bin','r');
 fseek($tf,$posv);
 for ($i=0;$i<$count;$i++){
  $vl=fread($tf,2); $vl=ord($vl[0])+ord($vl[1])*256;
  $vt=cdecode(fread($tf,$vl));
  if (in_array($n,$tolink))
  {
//   $bt=$bt."\n".'<a name="v'.$n.'"></a>';
   $l1='<a href="#c'.$n.'"><b>';
   $l2='</b></a>';
  }
  else { $l1=''; $l2=''; }
  $bt=$bt."\n".'<p class="ntcite"><a name="v'.$n.'"></a><sup>'.$l1.$n.$l2.'</sup> '.$vt;
  $n++;
 }
 fclose($tf);
 $bt=$bt."<br>&nbsp;";
}
return cite_form().$bt.$text;
}

function targeted_text($text){
global $tolink;
$tl=strlen($text);
$fr='<p>Ст. ';
$frl=strlen($fr);
$j=0; $n=''; $k=0; $p=0; $r='';
for($i=0; $i<$tl; $i++){
 $c=$text[$i];
 if ($c==$fr[$j]){ $j++; }
 else { $j=0; }
 if ($j==$frl){ $k=$i; $j=0; }
 if (($k>0)&&($i>$k)) {
  if (($c<"0")||($c>"9")){
   $tolink[]=$n;
   $r=$r.substr($text,$p,$i-strlen($n)-$frl-$p).
   "\n".'<p><a name="c'.$n.'"></a><a href="#v'.$n.'">Ст. '.$n.'</a>';
   $k=0; $n=''; $p=$i;
  }
  else { $n=$n.$c; }
 }
}
$r=$r.substr($text,$p,$tl-$p);
return $r;
}

function cite_form(){
global $nocite;
$c=''; if ($nocite){ $c=" checked"; }
return '

<script type="text/javascript">
function ChangeVersion(){
 f=document.forms.ntform;
 f.nocite.checked=false;
 document.cookie="nocite=0;";
 document.cookie="version="+f.version.value+";path=/;";
 f.submit();
}
function ChangeShowing(){
 f=document.forms.ntform;
 if (f.nocite.checked){ document.cookie="nocite=1;"; }
 else { document.cookie="nocite=0;"; }
 f.submit();
}
</script>
<form name="ntform" method="POST" action="'.$_SERVER['PHP_SELF'].'">
<p class="ntcite" align="right">
 <input type="CHECKBOX" name="nocite" value="1"
 onclick="ChangeShowing();"'.$c.'> 
Без цитат. Цитат от: 
<select name="version" onchange="ChangeVersion();">
'.fsoption("38/").'Ревизираното издание
'.fsoption("Tzrg/").'Цариградското издание
</select>. 
</form>
';
}

function fsoption($v){
global $bversion;
$s='';
if ($bversion==$v){ $s=" selected"; }
return "<option value=\"$v\"$s>";
}

function ifposted($k,$v){
if ((isset($_POST[$k]) && $_POST[$k]))
{ return $_POST[$k]; }
else {
 if ((isset($_COOKIE[$k]) && $_COOKIE[$k]))
 { return $_COOKIE[$k]; }
 else { return $v; }
}
}

function cdecode($v){
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
if ($it){ $r=$r.'</i>';}
return $r;
}

?>
