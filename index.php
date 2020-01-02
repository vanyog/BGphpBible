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

error_reporting(E_ALL); ini_set('display_errors',1);

include("_options.php");
include("index-$language.php");
include("functions.php");

$pt0=posted('cversion','');                                   // Директория на предишната показвана Библия
$pth=posted('version',cookie_or('version',$default_version)); // Директорията с файловете на показваната Библия
$enc=version_encoding($pth);                                  // Кодировка на показваната Библия
include_once("hlanguage.php");                     // Зарежда обекта със зависещи от езика функции
$bk=posted('book',cookie_or('book',1));       // Номер на текущата книга
$ch=posted('chapter',cookie_or('chapter',1)); // Номер на текущата глава
$vr=posted('verse',cookie_or('verse',0));     // Номер на текущия стих
//die("$pth, $bk, $ch, $vr");
$apth=a_path($pth);           // Абсолютната директория с файловете на Библията
include("structure.php");     // Зарежда описанието на структурата на Библията
$shv=1; // Да се показват цели стихове в резултата от търсенето
$fnotes=''; // Бележки под линия
$findex=0;  // Номер на бележката под линия
$pstyle=para_style($pth);

// Ако се сменя версията, се коригират номерата на стиховете, за да си съответстват по смисъл
if ( ($pth!=$pt0) && in_array($pt0,array_keys($version)) 
     && !in_array($pt0,array_keys($on_other_sites)) 
    ) 
  include("correct.php");

//в случай че данните са изпратени опростено с QUERY_STRING
if (isset($_SERVER['QUERY_STRING'])) get_query();

header("Content-Type: text/html; charset=utf-8");

pagehead(); // Изпращане <HEAD>...</HEAD> частта на страницата

echo '<form name="b_open" action="index.php" method="'.$form_metod.'">
<input type="hidden" name="cversion" value="'.$pth.'">
<input type="button" value="'.$prev_chapter.'" onclick="goprev();">'.
about_version();

// Показване на select елемента за избор на Версия
echo '<select name="version" onchange="changever();">';
foreach($version as $v=>$n){
 echo "\n<option value=\"$v\"";
 if ($v==$pth){ echo " selected"; }
 echo ">$n";
}
echo '</select>';

// Показване на select елемента за избор на книга
echo '<select name="book" onchange="bookchange();">';
for ($i=$bn[0]+1;$i<2*$bn[0]+1;$i++){
 $j=$i-$bn[0];
 $bnames[$i]=trim($bnames[$i]);
 echo "\n<option value=\"$j\"";
 if ($j==$bk){ echo " selected"; } 
 echo '>';
 // Заглавията на книгите в гръцкия Нов завет са с различна кодировка
 if($pth=='Gr/') echo iconv("windows-1253",'utf-8',$bnames[$i]); else
 echo iconv($enc,'utf-8',$bnames[$i]);
}
echo '</select>';

// Показване на select елемента за избор на глава
echo '<select name="chapter" onchange="document.forms[0].submit();">';
for ($i=1;$i<(is_array($vcount[$bk])?count($vcount[$bk]):0);$i++){
 if ($i==$ch){ echo "\n<option selected>"; } else { echo "\n<option>"; }
 echo $i; 
}
echo '</select><input type="submit" value="'.$open_chapter.'"><input type="button" value="'.$next_chapter.'" class="right" onclick="gonext();">
</form>

'.parallel_form();

// Показване на заглавието на текста
if ( is_array($bn) && (count($bn)>21) ){
 if ( ($bk<count($bn)) && ($bn[$bk]==22) ){ echo iconv($enc,'utf-8',"<h1>$word_psalm &nbsp;$ch</h1>"); }
 else {
  if ($bk>$bn[0]) echo "<p>$missing_book";
  else echo "<h1>".iconv($enc,'utf-8',$hlang->encode($bnames[$bk]))."<br>$word_chapter &nbsp;$ch</h1>";
 }
}
else { echo "<P>$missing_files"; }

//Показване на формата за търсене и линк "Тълкувание"
echo search_form("right").'
'.coment_link();

if ( is_array($bn) && (count($bn)>21) ){ // Изпълнява се ако версията съществува

//Пресмятане индекса на първия стих
$vi=vindex($bk,$ch,$vcount);

//Отваряне на файловете с указателите и текста
$pf=fopen($pth.'CompactPoint.bin','r');
$tf=fopen($pth.'CompactText.bin','r');

// Четене и показване на стиховете от текущата глава
$cvc = isset($vcount[$bk][$ch]) ? $vcount[$bk][$ch] : 0;
for ($i=0;$i<$cvc;$i++){
 $vt=read_verse($enc,$pf,$tf,$vi+$i);
 $a=explode('$$',$vt);
 $bf='';
 switch(count($a)){
 case 2: $bf=$a[0]; $vt=$a[1]; break;
 case 3: $bf=$a[0].$a[1]; $vt=$a[2]; break;
 }
 $i1=$i+1;
 echo iconv($enc,'utf-8',$bf);
 $bl='<p '; $ct = "</p>\n";
 if ($vr==$i1){ $bl.='class="averse" id="'.$i1.'">'; }
 else { $bl.='id="'.$i1.'">'; }
 if (!$pstyle && $i1<10) $bl=$bl.'&nbsp; ';
 if(iconv($enc,'utf-8',mb_substr($vt,0,1))=='¶')
      { $bl = '<p class="bigpar"><span'.substr($bl,2); $ct="</span>\n"; $vt = mb_substr($vt,1); }
 else if($pstyle) { $bl = '<span'.substr($bl,2); $ct="</span>\n"; }
 $bl=$bl.'<a href="#" title="'.$word_parallel.
     '" class="prl" onclick="parallel('.$i.','.($vi+$i).');return false;">'.
     $i1.'</a> ';
 if ($vt) echo "$bl".iconv($enc,'utf-8//IGNORE',$vt).$ct;
}

//Зетваряне на файловете с указателите и текста
fclose($tf);
fclose($pf);

if ($fnotes) 
echo "\n".'<P>&nbsp;
<hr>
<a id="fnotes"></a>'.$fnotes;

}

//Показване на долните бутони
echo '<p>&nbsp;</p>
<div class="bottom">
<input type="button" value="'.$prev_chapter.'" onclick="goprev();">
'.about_the_project().'
<input type="BUTTON" value="'.$next_chapter.'" class="right" onclick="gonext();">
<p style="clear:both;"></p>
</div>
';


//--------ФУНКЦИИ-----------

function cookie_or($n, $v){//die(print_r($_COOKIE,true));
if(isset($_COOKIE[$n])) return $_COOKIE[$n];
else return $v;
}

function determinenextandprev(){
global $vcount,$bk,$ch,$nxbk,$nxch,$prbk,$prch;
$prbk=$bk;
$prch=$ch-1;
$nxbk=$bk;
$nxch=$ch+1;
if($ch==1){
  $prbk=$bk-1;
  if ($prbk<1){ $prbk=1; $prch=1; }
  else { $prch=count($vcount[$prbk])-1; }
  $nxbk=$bk;
  $nxch=2;
}
if(empty($vcount[$bk])) return '';
if( ($bk<count($vcount)) && ($ch==(count($vcount[$bk])-1)) ){
  $prbk=$bk;
  $prch=$ch-1;
  $nxbk=$bk+1;
  if ($nxbk>(count($vcount)-1)){ $nxbk=1; }
  $nxch=1;
}
}

function pagehead(){
global $on_other_sites,$version,$pth,$bk,$ch,$vr,$vcount,$nxbk,$nxch,$prbk,$prch,$cookie_message;
determinenextandprev();
echo '<!DOCTYPE html>
<html lang="bg">

<HEAD>
  <TITLE>Библията на български - php реализация</TITLE>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:type" content="article">
  <meta property="fb:app_id" content="1350744361603908">
  <meta property="og:url" content="http://vanyog.com/bible/php">
  <meta property="og:image" content="http://vanyog.com/bible/php/images/chetveroevangelie.jpg">
  <meta property="og:title" content="Библия - онлайн на български и др. езици">
  <meta property="og:description" content="Библията; търсене в Библията; php скриптове с отворен код за представяне на Библията върху отдалечен или локален сървър.">
  <link rel="shortcut icon" sizes="192x192" href="images/web-icon-1.png">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
  <META name="keywords" content="Библия,Библията,търсене,онлайн,преводи на български,руски,английски,македонски,сръбски">
  <META name="description" content="Библията на български и други езици; търсене в Библията; php скриптове с отворен код за представяне на Библията върху отдалечен или локален сървър.">
  <link rel=stylesheet type="text/CSS" href="php-bible.css">
  <script src="js/cookies.js"></script>

<script>
var cookie_message = "'.$cookie_message.'";
var El=[];
if(typeof opera == "undefined" && document.all)
  Browser = "IE";
else
  Browser = "";

for(var i=0; i<150; i++)
 El[El.length] = document.createElement("option");

bibleChapters=Array(';
$c=count($vcount);
for($i=1;$i<=$c;$i++){
 echo $vcount[$i][0];
 if ($i<$c){ echo ','; }
}
echo ');

function bookchange(){
 si = document.forms[0].book.selectedIndex;
 sr = document.forms[0].chapter.options.length;
 sm = bibleChapters[si];
 bchl = document.forms[0].chapter;
 if(sm == sr) { return; }
 if(sm > sr) {
  for(i=sr; i<sm; i++){
   if(Browser == "IE") 
     El[i].innerText = i + 1;
   else 
     El[i].text = i + 1;
   bchl.appendChild(El[i]);
  }
 }
 if(sm < sr) {
  for(i=sr-1; i>sm-1; i--){
   bchl.removeChild(bchl.options[i]);
  }
 }
}

function gonext(){
cookie_set("bscrollY",0);
document.b_open.book.selectedIndex="'.($nxbk-1).'";
document.b_open.book.value="'.($nxbk).'";
document.b_open.chapter.selectedIndex='.($nxch-1).';
document.b_open.submit();
}

function goprev(){
cookie_set("bscrollY",0);
document.b_open.book.selectedIndex="'.($prbk-1).'";
document.b_open.book.value="'.($prbk).'";
bookchange();
document.b_open.chapter.selectedIndex='.($prch-1).';
document.b_open.submit();
}

function changever(){
';
foreach($on_other_sites as $k=>$v){
echo 'if (document.b_open.version.value=="'.$k.'") document.b_open.action="'.$v.'";
else ';
}
echo 'document.b_open.action="index.php";
document.b_open.submit();
}

function parallel(v,i){
document.b_parallel.verse.value=v+1
document.b_parallel.index.value=i
document.b_parallel.submit();
}

cookie_set("version","'.$pth.'");
cookie_set("book","'.$bk.'");
cookie_set("chapter","'.$ch.'");
cookie_set("verse","'.$vr.'");

function onBodyScroll(e){
   cookie_set("bscrollY",e.scrollY);
}

var max_sh = 0;
function page_move(el,ev){
    var dh = document.body.clientHeight;
    var ch = ev.pageY;
    var wh = window.innerHeight;
    var sh = window.scrollY;
    if(sh>max_sh) max_sh = sh;
    if(ch-sh<wh/4) window.scrollTo(0, sh - wh + 10);
    if(ch-sh>wh*3/4) window.scrollTo(0, sh + wh - 10);
    var sh1 = window.scrollY;
    if(sh1>0 && sh1==sh) gonext();
    if(max_sh>0 && sh1==0 && sh1==sh) goprev();
}

</script>

</HEAD>

<body onscroll="onBodyScroll(this);" onclick="page_move(this,event);"><div id="all_page">
';
}

function coment_link(){
global $pth,$bk,$bnames,$bn,$ch;
$r='';
if ( (($pth=='38/')   && ($bk>38) && ($bk<=$bn[0]))
  || (($pth=='Tzrg/') && ($bk>0) && ($bk<=$bn[0]))
   )
{
 $r='<p><a href="coment.php?'.
   rawurlencode(
    trim($bnames[$bk+2*$bn[0]])." $ch:1"
   ).
  '">Тълкувание</a>&nbsp;&nbsp;';
}
return $r;
}

?>
<div>
<script>
var bscrollY = cookie_value("bscrollY");
window.scrollTo(0, cookie_value("bscrollY"));
</script>
</body>
</html>
