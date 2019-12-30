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

include("_options.php");
include("functions.php");
include("parallel-$language.php");

//print_r($_POST);

$vpth=array_keys($version);   // масив с директориите на библиите
$pth=posted('version',$default_version); // Директорията с файловете на Библията
include_once("hlanguage.php");
$bk=posted('book',1);    // Номер на текущата книга
$ch=posted('chapter',1); // Номер на текущата глава
$vr=posted('verse',1);   // Номер на текущия стих
$gch=$ch; $gvr=$vr;      // "Глобални" номера на текущите глава и стих
include("structure.php");// Зарежда описанието на структурата на Библията
if ($pth!='/') globalize();// Пресмятане на "глобалните" глава и стих
$next_bk=$bk;
$prev_bk=$bk;
$next_ch=$ch;
$prev_ch=$ch;
$next_vr=$vr;
$prev_vr=$vr;
next_prev(); // определя номера на предишния и на следващия стих 
$fnotes=''; // бележки под линия
$findex=0;  // Номер на бележката под линия

start_page(); // начало на страницата

// Показване на стиха от различните преводи
foreach($vpth as $p) if (!in_array($p,array_keys($on_other_sites))) parallel($p);

// Показване на бележките под линия
if ($fnotes) 
echo "\n".'<P>&nbsp;
<hr>
<A NAME="fnotes"></A>'.$fnotes;

echo '</DIV>
<div class="bottom">
'.pbutton().
about_the_project().
nbutton().
'
</div>';

// --------- Функции ----------

function globalize(){
global $bn,$pth,$bk,$ch,$vr,$gch,$gvr;
$apth=a_path($pth);
$dfn="$apth".'_Diff_.txt';
if (file_exists($dfn)){
 $df=file($dfn);
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bn[$bk]==$la[0])&&($ch==$la[1])&&($vr>=$la[2])){
   $gch=$ch+$la[3]; $gvr=$vr+$la[4];
  }
 }
}
}

function parallel($p){
global $pth,$bn,$bk,$version,$gch,$gvr,$hlang;
// преминаване към "локални" номера на глава $ch и стих $vr
$apth=a_path($p);
$enc=version_encoding($apth);
$dfn=$apth.'_Diff_.txt';
$ch=$gch; $vr=$gvr;
if (file_exists($dfn)){
 $df=file($dfn);
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bn[$bk]==$la[0])&&($ch==$la[1])&&($vr>=$la[2])){
   $ch=$gch-$la[3]; $vr=$gvr-$la[4];
  }
 }
}
// определяне "локалния" номер на книгата $bk1
$bn0=file($p."BibleTitles.txt");
$bn1=explode(' ',trim($bn0[0]));
if ($pth!='/') $bk1=array_search( $bn[$bk],array_slice($bn1,1) ) + 1;
else $bk1=array_search( $bk,array_slice($bn1,1) ) + 1;
if (($bk1==1)&&($bk!=1)) // ако няма такава книга
{ $vt=''; $bn3=''; $bk1=1; $vr=''; }
else {
 // определяне индакса на стиха
 $vi=vindex($bk1,$ch,get_structure($bn0,$p))+$vr-1;
 // четене на стиха $vt;
 $pf=fopen($p.'CompactPoint.bin','r');
 $tf=fopen($p.'CompactText.bin','r');
 $hlang->HLanguage(version_languege($p));
 $vt=iconv($enc,'utf-8',read_verse($enc,$pf,$tf,$vi));
 $vt=str_replace('¶','',$vt);
 $bn3=' - '.iconv($enc,'utf-8',$bn0[2*$bn1[0]+$bk1])." $ch:$vr";
}
echo "\n".'<P><B><A HREF="" ONCLICK="BkToBible('
     ."'$p',$bk1,$ch,'$vr'".');return false;">'.$version[$p]."$bn3</B></A>
<P CLASS=\"p0\">$vt".'
<P>&nbsp;';
}

function start_page(){
global $next_bk, $prev_bk, $next_ch, $prev_ch, $next_vr, $prev_vr;
header("Content-Type: text/html; charset=utf-8");
echo '<!DOCTYPE html>
<html lang="bg">

<HEAD>
  <TITLE>Библията на български - php реализация</TITLE>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
  <link rel=stylesheet type="text/CSS" href="php-bible.css">
<script>
function BkToBible(p,b,c,v){
if (v>1) document.forms.b_open.action="index.php#"+v;
document.forms.b_open.version.value=p;
document.forms.b_open.book.value=b;
document.forms.b_open.chapter.value=c;
document.forms.b_open.verse.value=v;
document.forms.b_open.submit();
}
function NextVerse(){
document.forms.b_parallel.version.value="/";
document.forms.b_parallel.book.value="'.$next_bk.'";
document.forms.b_parallel.chapter.value="'.$next_ch.'";
document.forms.b_parallel.verse.value="'.$next_vr.'";
document.forms.b_parallel.submit();
}
function PrevVerse(){
document.forms.b_parallel.version.value="/";
document.forms.b_parallel.book.value="'.$prev_bk.'";
document.forms.b_parallel.chapter.value="'.$prev_ch.'";
document.forms.b_parallel.verse.value="'.$prev_vr.'";
document.forms.b_parallel.submit();
}
</script>
</HEAD>

<BODY>
<FORM NAME="b_open" METHOD="POST" ACTION="index.php">
<INPUT TYPE="HIDDEN" NAME="version" VALUE="">
<INPUT TYPE="HIDDEN" NAME="book" VALUE="">
<INPUT TYPE="HIDDEN" NAME="chapter" VALUE="">
<INPUT TYPE="HIDDEN" NAME="verse" VALUE="">
</FORM>
<div class="bottom">
'.parallel_form().tbuttons().'
</div>
<DIV CLASS="content">
<H1>Библейски паралел (сравнение на преводите)</H1>
';
}

function tbuttons(){
global $prev_verse,$next_verse;
return pbutton().'
'.nbutton();
}


function pbutton(){
global $prev_verse;
return '<input type="BUTTON" value="'.$prev_verse.'" ONCLICK="PrevVerse()">';
}

function nbutton(){
global $next_verse;
return '<input type="BUTTON" value="'.$next_verse.'" class="right" ONCLICK="NextVerse()">';
}

function next_prev(){
global $bn,$next_bk, $prev_bk, $next_ch, $prev_ch, $next_vr, $prev_vr;
if ($bn) $next_bk=$bn[$next_bk];
$gs=file("Bible_Structure.txt");
$gvc=explode(' ',trim($gs[$next_bk-1]));
if ($next_vr<$gvc[$next_ch]) $next_vr++;
else {
 if ($next_ch<$gvc[0]) { $next_vr=1; $next_ch++; }
 else if ($next_bk<77){ $next_ch=1; $next_vr=1; $next_bk++; }
} 
if ($prev_vr>1) $prev_vr--;
else {
 if ($prev_ch>1) { $prev_vr=$gvc[$prev_ch-1]; $prev_ch--; }
 else if ($prev_bk>1){
  $prev_bk--; 
  $gvc=explode(' ',trim($gs[$prev_bk-1])); 
  $prev_ch=$gvc[0]; $prev_vr=$gvc[$prev_ch];
 }
} 
}


?>

</BODY>
</html>
