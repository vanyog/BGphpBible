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
$apth=a_path($pth);           // Абсолютната директория с файловете на Библията
include("structure.php");     // Зарежда описанието на структурата на Библията
$shv=1; // Да се показват цели стихове в резултата от търсенето
$fnotes=''; // Бележки под линия
$findex=0;  // Номер на бележката под линия
// Флаг за вид на текста за екранен четец
$sreader = isset($_GET['listen']) && ($_GET['listen']=='on');
$pstyle=para_style($pth);

// Ако се сменя версията, се коригират номерата на стиховете, за да си съответстват по смисъл
if ( ($pth!=$pt0) && in_array($pt0,array_keys($version)) 
     && !in_array($pt0,array_keys($on_other_sites)) 
    ) 
  include("correct.php");

//в случай че данните са изпратени опростено с QUERY_STRING
if (isset($_SERVER['QUERY_STRING'])) get_query();

header("Content-Type: text/html; charset=utf-8");

// Осигурява zip компресиране на отговора
if(!ob_start("ob_gzhandler")) ob_start();

pagehead(); // Изпращане <HEAD>...</HEAD> частта на страницата

if(!$sreader){

echo '<nav id="navigation" onclick="navClicked(event)"><p class="f">
<button id="navButton" onclick="toggleNav(event)">&#9776;</button>
<span style="float:left;"><label for="contrast">'.$word_contrast.'</label> 
<input type="range" id="contrast" oninput="contrastInput()" onchange="contrastChanged()" min="140" max="255">
</span></p>
<form name="b_open" action="index.php" method="'.$form_metod.'">
<input type="hidden" name="cversion" value="'.$pth.'">
';

// Показване на select елемента за избор на Версия
echo '<p><label for="slt">'.$bible_translation.'</label><select name="version" id="slt" onchange="changever();">';
foreach($version as $v=>$n){
 echo "\n<option value=\"$v\"";
 if ($v==$pth){ echo " selected"; }
 echo ">$n";
}
echo '</select></p>';

// Показване на select елемента за избор на книга
echo '<p><label for="slb">'.$word_book.'</label><select name="book" id="slb" onchange="bookchange();">';
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
echo '</select></p>
';

// Показване на select елемента за избор на глава
echo '<p><label for="slch">'.$word_chapter.':</label> <select name="chapter" id="slch" onchange="document.forms[0].submit();">';
for ($i=1;$i<(is_array($vcount[$bk])?count($vcount[$bk]):0);$i++){
 if ($i==$ch){ echo "\n<option selected>"; } else { echo "\n<option>"; }
 echo $i; 
}
if(!isset($vcount[$bk])) echo "\n<option selected>1";
echo '</select></p>
<p><input type="submit" value="'.$open_chapter.'"></p>
</form>

'.parallel_form();

//Показване на формата за търсене и линк "Тълкувание"
echo '<p>'.prev_chapter_link().' &nbsp; 
'.next_chapter_link().'</p>
'.search_form().'
'.coment_link().'
'.audio($pth, $bk, $ch).'
<p>
<button title="'.$decrease_font.'" onclick="decreaseFont();">A<sup>-</sup></button>
<button title="'.$increase_font.'" onclick="increaseFont();">A<sup>+</sup></button>
<button title="'.$dark_mode.'" onclick="toggleDarkMode();" id="modeBtn">&#x1F317;</button>
<button title="'.$full_reload.'" onclick="document.location.reload(true);">&#8635;</button>
'.about_version().'</p>
</nav>
';

}

if($sreader) echo '<button onclick="selectAllText();" id="sallb">Select</button>'."\n";

echo '<div id="all_page">
';

// Съставяне и показване на заглавието на текста
if ( is_array($bn) && (count($bn)>21) ){
 $h1 = '';
 if ( ($bk<count($bn)) && ($bn[$bk]==22) ){ $h1 = "$word_psalm &nbsp;$ch"; }
 else {
  if ($bk>$bn[0]) echo "<p>$missing_book";
  else $h1 =  iconv($enc,'utf-8',$hlang->encode($bnames[$bk]))." - <span>$word_chapter &nbsp;$ch</span>";
 }
 if($h1){
   echo "<h1 id=\"h1\">$h1</h1>\n";
 }
}
else { echo "<p>$missing_files</p>"; }

if ( is_array($bn) && (count($bn)>21) ){ // Изпълнява се ако версията съществува

//Пресмятане индекса на първия стих
$vi=vindex($bk,$ch,$vcount);

//Отваряне на файловете с указателите и текста
$pf=fopen($pth.'CompactPoint.bin','r');
$tf=fopen($pth.'CompactText.bin','r');

// Четене и показване на стиховете от текущата глава
$cvc = isset($vcount[$bk][$ch]) ? $vcount[$bk][$ch] : 0; // Брой стихове в текущата глава
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
 $bl='<p '; 
 $ct = "</p>\n";
 if ($vr==$i1){ $bl.='class="averse" id="'.$i1.'">'; } // Оцветяване на текущия стих
 else { $bl.='id="'.$i1.'">'; }
 if (!$pstyle && $i1<10) $bl=$bl.'&nbsp; ';
 if(iconv($enc,'utf-8',mb_substr($vt,0,1))=='¶')
      { $bl = '<p class="bigpar"><span'.substr($bl,2); $ct="</span>\n"; $vt = mb_substr($vt,1); }
 else if($pstyle) { $bl = '<span'.substr($bl,2); $ct="</span>\n"; }
 // Номер на стиха
 if(!$sreader) $bl=$bl.'<a href="#" title="'.$word_parallel.
     '" class="prl" onclick="parallel('.$i.','.($vi+$i).');return false;">'.
     $i1.'</a> ';
 if ($vt) echo "$bl".iconv($enc,'utf-8//IGNORE',$vt).$ct;
}

//Зетваряне на файловете с указателите и текста
fclose($tf);
fclose($pf);

// Показване на бележки под линия
if ($fnotes && !$sreader) 
   echo "\n".'<hr>
<div class="bottom" id="mbtns">
'.prev_chapter_link().'
'.next_chapter_link().'
<p style="clear:both;"></p>
</div>
<hr id="fnotes">'.$fnotes;

if($sreader)
   echo "\n".'
<p><br><a href="index.php?cversion='.$pt0.'&version='.$pth.'&book='.$bk.'&chapter='.$ch.'">Тази глава в нормален вид</a></p>
<p style="clear:both;"></p>';

}

//Показване на долните бутони
if(!$sreader) echo '<p>&nbsp;</p>
<hr>
<div class="bottom">
'.prev_chapter_link().'
'.about_the_project().'
'.next_chapter_link().'
<p style="clear:both;"></p>
</div>
';


//--------ФУНКЦИИ-----------

function cookie_or($n, $v){
if(isset($_COOKIE[$n]) && (is_numeric($v)===is_numeric($_COOKIE[$n]))) return $_COOKIE[$n];
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
if( ($bk<count($vcount)) && ($ch==(count($vcount[$bk])-1))  ){
  $prch=$ch-1;
  $nxbk=$bk+1;
  if ($nxbk>(count($vcount)-1)){ $nxbk=1; }
  $nxch=1;
}
}

function pagehead(){
global $on_other_sites, $version, $pth, $bk, $ch, $vr, $vcount, $nxbk, $nxch, $prbk, $prch, 
       $cookie_message, $image;
determinenextandprev();
echo '<!DOCTYPE html>
<html lang="bg">
<HEAD>
  <TITLE>'.$version[$pth].' - проект BGphpBible</TITLE>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:type" content="article">
  <meta property="fb:app_id" content="1350744361603908">
  <meta property="og:url" content="http://vanyog.com'.$_SERVER['REQUEST_URI'].'">
  <meta property="og:image" content="http://vanyog.com/bible/php/';
if(isset($image[$pth])) echo $pth.$image[$pth];
else echo 'images/chetveroevangelie.jpg';
$js  = file_get_contents($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/js/cookies.js");
$css = file_get_contents($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/php-bible.css");
echo '">
  <meta property="og:title" content="'.$version[$pth].' - проект BGphpBible">
  <meta property="og:description" content="Библията; търсене в Библията; php скриптове с отворен код за представяне на Библията върху отдалечен или локален сървър.">
  <link rel="shortcut icon" sizes="192x192" href="images/web-icon-1.png">
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
  <META name="keywords" content="Библия,Библията,търсене,онлайн,преводи на български,руски,английски,македонски,сръбски">
  <META name="description" content="Библията на български и други езици; търсене в Библията; php скриптове с отворен код за представяне на Библията върху отдалечен или локален сървър.">
  <link rel="manifest" href="site.webmanifest">
  <meta name="theme-color" content="#ffffff">
  <style>'.$css.'</style>
  <script>'."\n".$js.'</script>

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

var no_move = false;

// Отваряне на следваща глава
function gonext(){
cookie_set("bscrollY",0);
document.b_open.book.selectedIndex="'.($nxbk-1).'";
document.b_open.book.value="'.($nxbk).'";
document.b_open.chapter.selectedIndex='.($nxch-1).';
document.b_open.submit();
}

// Отваряне на предна глава
function goprev(){
cookie_set("bscrollY",0);
document.b_open.book.selectedIndex="'.($prbk-1).'";
document.b_open.book.value="'.($prbk).'";
bookchange();
document.b_open.chapter.selectedIndex='.(($prch<1)?0:$prch-1).';
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

// Отваря сравняването на стихове при кликване върху номер на стих
function parallel(v,i){
document.b_parallel.verse.value=v+1
document.b_parallel.index.value=i
document.b_parallel.submit();
}

cookie_set("version","'.$pth.'");
cookie_set("book","'.$bk.'");
cookie_set("chapter","'.$ch.'");

var max_sh = 0;
var isDblClick = true;

// Превъртане на страницата
function page_move(ev){
    if(isDblClick) return;
    var ch = ev.pageY;
    var dh = document.body.clientHeight;
    var wh = window.innerHeight;
    var sh = window.scrollY;
    if(sh>max_sh) max_sh = sh;
    var dd = document.getElementById("h1");
    dd = dd.offsetHeight + 10;
    // Кликване в горната четвърт на екрана - движение нагоре
    if(ch-sh<wh/4) window.scrollTo(0, sh - wh + dd);
    // Кликване в долната четвърт на екрана - движение наадолу
    if(ch-sh>wh*3/4) window.scrollTo(0, sh + wh - dd);
}

function chapter_change(sh){
    var sh1 = window.scrollY;
    if(sh1>0 && sh1==sh) gonext();
    if(max_sh>0 && sh1==0 && sh1==sh) goprev();
}

var to_anchor = false; // Флаг, който се вдига при щракване върху линк към бележка под линия
// Изпълнява се при кликване някъде върху текста на страницата
function page_clicked(ev){
isDblClick = false;
if(!to_anchor && !closeNav()) setTimeout(page_move,300,ev);
to_anchor = false;
}

// Изпълнява се при двойно кликване някъде върху текста на страницата - само вдига флаг isDblClick
function page_dblclicked(){
isDblClick = true;
}

var darkMode = cookie_value("darkMode");

// Изпълнява се при зареждане на страницата
function correctTop(){
window.addEventListener("resize",setPaddingTop);
setPaddingTop();
setTimeout(function(){
  var s = document.body.style;
  var v = cookie_value("contrast", 255);
  var cs = document.getElementById("contrast");
  if(cs) cs.value = v;
  var fs = cookie_value("fontSize", "14pt");
  s.fontSize = fs;
  setContrast(v);
  if(darkMode==0){ s.filter=\'invert(0%)\'; document.getElementById("modeBtn").innerText = "Нощ"; }
  else{ s.filter=\'invert(100%)\'; document.getElementById("modeBtn").innerText = "Ден"; }
  },50);
if(location.hash) setTimeout(function(){
  var h1 = document.getElementById("h1");
  var h = h1.offsetHeight;
  var t = document.getElementById(location.hash.substring(1)).offsetTop;
  window.scrollTo(0, t - h);
}, 500);
else{
  var bscrollY = cookie_value("bscrollY");
  window.scrollTo(0, bscrollY);
}
}

var rgbB = "rgb(0,0,0)";
var rgbC = "rgb(256,256,256)";

function setContrast(v){
  rgbB = "rgb("+v+","+v+","+v+")";
  rgbC = "rgb("+(256-v)+","+(256-v)+","+(256-v)+")";
  var s1 = document.getElementById("h1").style;
  var s2 = document.getElementById("all_page").style;
  var s3 = document.getElementById("mbtns"); if(s3) s3 = s3.style;
  var nv = document.getElementById("navigation");
  if(nv) var s4 = nv.style;
  s1.backgroundColor =  rgbB;
  s1.color = rgbC;
  s2.backgroundColor =  rgbB;
  s2.color = rgbC;
  if(s3) s3.backgroundColor =  rgbB;
  if(nv){
     if(nv.offsetHeight<=70) s4.backgroundColor = "#ffffff00";
     else s4.backgroundColor =  rgbB;
     s4.color = rgbC;
  }
}

// Превключва навигацията от сгънато в отворено състояние и обратно
// Изпълнява се при кликване върху бутона
function toggleNav(ev){
ev.stopPropagation();
var n = document.getElementById("navigation");
var h = n.offsetHeight;
var b = document.getElementById("navButton");
var bh = b.offsetHeight;
if(h==70 || h<=bh+32) { // разгъване
  n.style.height = "auto";
  n.style.width = "fit-content";
  n.overflowY = "scroll";
  n.style.backgroundColor = rgbB;
  b.innerHTML = "&nbsp;x&nbsp;";
  makeNavScrollable();
}
else { // сгъване
  n.scroll(0,0);
  n.style.height = bh+32+"px";
  n.style.width = "70px";
  n.style.overflow = "hidden";
  n.style.backgroundColor = "#ffffff00";
  b.innerHTML = "&#9776;";
}
}

// Сгъва навигацията до размерите на бутон
function closeNav(){
var n = document.getElementById("navigation");
if(!n) return;
var h = n.offsetHeight;
if(h==70) return false;
n.style.height = "70px";
n.style.width = "70px";
n.style.overflow = "hidden";
n.style.backgroundColor = "#ffffff00";
document.getElementById("navButton").innerHTML = "&#9776;";
return true;
}

// Наглася височината на навигацията, ако е по-висока от екрана 
function makeNavScrollable(){
var n = document.getElementById("navigation");
if(!n) return;
var wh = window.innerHeight;
var nh = n.clientHeight;
n.scrollTo(0,0);
if(wh<nh){
  n.style.height = wh+"px";
  n.style.overflowY = "scroll";
}
else if(n.offsetHeight>70){
  n.style.overflowY = "auto";
}
}

// Превключване от "Ден"-светъл режим към "Нощ"-тъмен и обратно
function toggleDarkMode(){
var b = document.body;
var bt = document.getElementById("modeBtn");
if(darkMode==0) {
  b.style.filter=\'invert(100%)\';
  darkMode=1;
  cookie_set("darkMode",1);
  bt.innerText = "Ден";
}
else {
  b.style.filter=\'invert(0)\';
  darkMode=0;
  cookie_set("darkMode",0);
  bt.innerText = "Нощ";
}
toggleNav(event);
}

// Вмъква празно пространство над текста, че в него да се помества заглавието
function setPaddingTop(){
makeNavScrollable();
//var h = document.getElementById("h1");
//var ap = document.getElementById("all_page");
//ap.style.paddingTop = h.offsetHeight + 18 + "px";
}

// Изпълнява се при скролване на страницата
function onBodyScroll(){
var h = window.scrollY;
cookie_set("bscrollY",h);
//var h1 = document.getElementById("h1");
//h1.style.top = h+"px";
//var n = document.getElementById("navigation");
//if(n) n.style.top = h+"px";
}

function selectAllText(){
var s = window.getSelection();
var l = String(s).length;
var r = document.createRange();
r.selectNodeContents(document.getElementById("all_page"));
s.removeAllRanges();
if(!l) s.addRange(r);
}

function navClicked(ev){ ev.stopPropagation(); }

function decreaseFont(){
var s = document.body.style;
var fs = parseInt(s.fontSize);
if(fs>8) fs--;
s.fontSize = fs + "pt";
cookie_set("fontSize", s.fontSize );
}

function increaseFont(){
var s = document.body.style;
var fs = parseInt(s.fontSize);
if(fs<25)fs++;
s.fontSize = fs + "pt";
cookie_set("fontSize", s.fontSize );
}

function contrastInput(){
var v = document.getElementById("contrast").value;
setContrast(v);
}

function contrastChanged(){
cookie_set("contrast", document.getElementById("contrast").value)
}

// Изпълнява се, когато курсорът минава върху номер на бележка под линия
function fnmOver(l,a){
var e = document.getElementById("nt" + a);
var d = document.getElementById("fndisplay");
var r1 = l.getBoundingClientRect(); 
d.innerHTML = e.innerHTML;
d.firstElementChild.remove();
d.style.display = "block";
d.style.top = (window.scrollY + r1.top) + "px";
d.style.left = (r1.left - d.getBoundingClientRect().width/2) + "px";
}

// Изпълнява се, когато курсорът напуска номер на бележка под линия
function fnmOut(){
document.getElementById("fndisplay").style.display="none";
}

let FF_FOUC_FIX;

</script>

</HEAD>

<body onload="correctTop()" onhashchange="correctTop()" onclick="page_clicked(event);" 
ondblclick="page_dblclicked()" onscroll="onBodyScroll()">
';
}

function prev_chapter_link(){
global $pt0, $pth, $prev_chapter, $prbk, $prch;
return '<a class="left" href="index.php?cversion='.$pt0.
       '&version='.$pth.'&book='.$prbk.'&chapter='.$prch.'" onclick="goprev();">'.$prev_chapter.'</a>';
}

function next_chapter_link(){
global $pt0, $pth, $next_chapter, $nxbk, $nxch;
return '<a class="right" href="index.php?cversion='.$pt0.
       '&version='.$pth.'&book='.$nxbk.'&chapter='.$nxch.'" onclick="gonext();">'.$next_chapter.'</a>';
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
</div>
<div id="fndisplay" onmouseleave="fnmOut();"></div>
</body>
</html>
