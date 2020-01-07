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

// Êëàñ ñúñ çàâèñåùè îò åçèêà ôóíêöèè 
class HLanguage{

var $lc_l = array();
var $uc_l = array();
var $sro = array();
var $id = '';
var $enc = '';

function __construct($hl){
 $this->id=$hl;
 switch ($hl){
  case 'en0': // àíãëèéñêè + ÷èñëà
   $lc='0123456789abcdefghijklmnopqrstuvwxyz';
   $uc='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $this->enc='ISO-8859-1';
   break;
  case 'bg': // áúëãàðñêè
   $lc='àáâãäåæçèéêëìíîïðñòóôõö÷øùúüþÿ';
   $uc='ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÜÞß';
   $this->enc = "windows-1251";
   break;
  case 'ma': // ìàêåäîíñêè
   $lc='àáâãäƒåæç¾è¼êëšìíœîïðñòóôõö÷Ÿø';
   $uc='ÀÁÂÃÄÅÆÇ½È£ÊËŠÌÍŒÎÏÐÑÒÓÔÕÖ×Ø';
   $this->enc = "windows-1251";
   break;
  case 'sec': // ñðúáñêè íà êèðèëèöà
   $lc='àáâãäåæçè¼êëšìíœîïðñòžóôõö÷Ÿø';
   $uc='ÀÁÂÃÄ€ÅÆÇÈ£ÊËŠÌÍŒÎÏÐÑÒŽÓÔÕÖ×Ø';
   $this->enc = "windows-1251";
   break;
  case 'ru': // ðóñêè
   $lc='àáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿ';
   $uc='ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß';
   $this->enc = "windows-1251";
   break;
  case 'gr': // ãðúöêè
   $lc='áâãäåæçèéêëìíîïðñòóôèõö÷øù';
   $uc='ÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑòÓÔÈÕÖ×ØÙ';
   $this->enc = "ISO-8859-1";
   break;
 }
 for($i=0;$i<strlen($lc);$i++){
  $this->lc_l[$uc[$i]]=$lc[$i];
  $this->uc_l[$lc[$i]]=$uc[$i];
  $this->sro[$lc[$i]]=1001+$i;
  $this->sro[$uc[$i]]=1001+$i;
 }
}

function lc_letter($c){
if (array_key_exists($c,$this->lc_l)) return $this->lc_l[$c];
if (array_key_exists($c,$this->uc_l)) return $c;
else return -1;
}

function compare($s1,$s2){
global $enc;
if($enc=='utf-8'){
  $r = strcasecmp($s1,mb_convert_case($s2, MB_CASE_LOWER));
  if($r<0) return -1;
  else if($r>0) return 1;
       else return 0;
}
$n1=strlen($s1); $n=$n1;
$n2=strlen($s2);
if ($n2<$n) $n=$n2; 
$r=0; $i=0;
while (($i<$n)&&($r==0)){
 if ($this->sro[$s1[$i]] < $this->sro[$s2[$i]]) $r=-1;
 else { if ($this->sro[$s1[$i]] > $this->sro[$s2[$i]]) $r=1;
      else $r=0; }
// echo $s1[$i]." ".$s2[$i]." $r<BR>";
 $i++;
}
if (($r==0) && ($n1<$n2)) return -1;
if (($r==0) && ($n1>$n2)) return 1;
return $r;
}

function encode($s){
$r='';
switch ($this->id){
 case 'gr':
  for($i=0;$i<strlen($s);$i++){
   if (array_key_exists($s[$i],$this->uc_l)){
    $r=$r.'&#'.(Ord($s[$i])-0xE1+0x3B1).';';
   }
   else {
    if (array_key_exists($s[$i],$this->lc_l)){
     $r=$r.'&#'.(Ord($s[$i])-0xE1+0x3B1).';';
    }
    else $r=$r.$s[$i];
   }
  }
  return $r;
  break;
 default: return $s;
}
}

} // class HLanguage

$hlang = new HLanguage(version_languege($pth));

?>
