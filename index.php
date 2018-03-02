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
include("index-$language.php");
include("functions.php");

$pt0=posted('cversion','');   // Директория на предишната показвана Библия
$pth=posted('version',$default_version); // Директорията с файловете на показваната Библия
include("hlanguage.php");     // Зарежда обекта със зависещи от езика функции
$bk=posted('book',1);         // Номер на текущата книга
$ch=posted('chapter',1);      // Номер на текущата глава
$vr=posted('verse',0);        // Номер на текущия стих

$apth=a_path($pth);           // Абсолютната директория с файловете на Библията
include("structure.php");     // Зарежда описанието на структурата на Библията
$shv=1; // Да се показват цели стихове в резултата от търсенето
$fnotes=''; // Бележки под линия
$findex=0;  // Номер на бележката под линия

// Ако се сменя версията, се коригират номерата на стиховете, за да си съответстват по смисъл
if ( ($pth!=$pt0) && in_array($pt0,array_keys($version)) 
     && !in_array($pt0,array_keys($on_other_sites)) 
    ) 
  include("correct.php");

//в случай че данните са изпратени опростено с QUERY_STRING
if (isset($_SERVER['QUERY_STRING'])) get_query();

header("Content-Type: text/html; charset=windows-1251");

pagehead(); // Изпращане <HEAD>...</HEAD> частта на страницата

echo '<table bgcolor="#FFFFFF" border="0" width="100%" cellspacing="0">
<tr><td colspan="2">

<form name="b_open" action="index.php" method="POST">
<input type="HIDDEN" name="cversion" value="'.$pth.'">

<table border="0" width="100%" cellspacing="0">
<tr class="panel">

<td class="panel"><input type="BUTTON" value="'.$prev_chapter.'" onclick="goprev();"></td>
<td class="panel" NOWRAP>'.about_version().'</td>
<td class="panel">
';

// Показване на select елемента за избор на Версия
echo '<select name="version" onchange="changever();" class="w200">';
foreach($version as $v=>$n){
 echo "\n<option value=\"$v\"";
 if ($v==$pth){ echo " selected"; }
 echo ">$n";
}
echo '</select>
';

// Показване на select елемента за избор на книга
echo '</td>
<td NOWRAP>
<select name="book" onchange="bookchange();">';
for ($i=$bn[0]+1;$i<2*$bn[0]+1;$i++){
 $j=$i-$bn[0];
 $bnames[$i]=trim($bnames[$i]);
 echo "\n<option value=\"$j\"";
 if ($j==$bk){ echo " selected"; } 
 echo '>'.$bnames[$i]; 
}
echo '</select>';

// Показване на select елемента за избор на глава
echo "\n".'<select name="chapter" onchange="document.forms[0].submit();">';
for ($i=1;$i<count($vcount[$bk]);$i++){
 if ($i==$ch){ echo "\n<option selected>"; } else { echo "\n<option>"; }
 echo $i; 
}
echo '</select>
<input type="SUBMIT" value="'.$open_chapter.'">
</td>
<td class="panel" align="right"><input type="BUTTON" value="'.$next_chapter.'" onclick="gonext();"></td>
</tr>
</table>
</form>

'.parallel_form().'

</td></tr>
<tr valign="top"><td>
';

// Показване на заглавието на текста
if (count($bn)>21){
 if ( ($bk<count($bn)) && ($bn[$bk]==22) ){ echo "<h1>$word_psalm &nbsp;$ch</h1>"; }
 else {
  if ($bk>$bn[0]) echo "<p>$missing_book";
  else echo "<h1>".$hlang->encode($bnames[$bk])."<br>$word_chapter &nbsp;$ch</h1>"; 
 }
}
else { echo "<P>$missing_files"; }

//Показване на формата за търсене и линк "Тълкувание"
echo '</td><td align="right" NOWRAP>
'.search_form("right").'
'.coment_link().
'</td></tr><tr><td colspan="2" height="400" valign="top">
';

if (count($bn)>21){ // Изпълнява се ако версията съществува

//Пресмятане индекса на първия стих
$vi=vindex($bk,$ch,$vcount);

//Отваряне на файловете с указателите и текста
$pf=fopen($pth.'CompactPoint.bin','r');
$tf=fopen($pth.'CompactText.bin','r');

// Четене и показване на стиховете от текущата глава
$cvc = isset($vcount[$bk][$ch]) ? $vcount[$bk][$ch] : 0;
for ($i=0;$i<$cvc;$i++){
 $vt=read_verse($pf,$tf,$vi+$i);
 $i1=$i+1;
 if ($vr==$i1){ $bl='<p class="averse">'; }
 else { $bl='<p>'; }
 if ($vr)  $bl="<a name=\"$i1\"></a>\n".$bl;
 if ($i1<10) $bl=$bl.'&nbsp; ';
 $bl=$bl.'<A HREF="" TITLE="'.$word_parallel.
     '" CLASS="prl" ONCLICK="parallel('.$i.','.($vi+$i).');return false;">'.
     $i1.'</A> ';
 if ($vt)  echo "\n$bl$vt";
}

//Зетваряне на файловете с указателите и текста
fclose($tf);
fclose($pf);

if ($fnotes) 
echo "\n".'<P>&nbsp;
<HR SIZE="1" ALIGN="left" WIDTH="30%">
<A NAME="fnotes"></A>'.$fnotes;

}

//Показване на долните бутони
echo '<p>&nbsp;
</td></tr><tr><td colspan="2">
<table width="100%" cellspacing="0"><tr>
<td class="panel"><input type="BUTTON" value="'.$prev_chapter.'" onclick="goprev();"></td>
'.about_the_project().'
<td align="right" class="panel"><input type="BUTTON" value="'.$next_chapter.'" onclick="gonext();"></td>
</tr></table>
</td></tr></table>

';

//--------ФУНКЦИИ-----------

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
if( ($bk<count($vcount)) && ($ch==(count($vcount[$bk])-1)) ){
  $prbk=$bk;
  $prch=$ch-1;
  $nxbk=$bk+1;
  if ($nxbk>(count($vcount)-1)){ $nxbk=1; }
  $nxch=1;
}
}

function pagehead(){
global $on_other_sites,$version,$pth,$vcount,$nxbk,$nxch,$prbk,$prch;
determinenextandprev();
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>

<HEAD>
  <TITLE>Библията на български - php реализация</TITLE>
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
  <META name="keywords" content="Библия,Библията,търсене,онлайн,преводи на български,руски,английски,македонски,сръбски">
  <META name="description" content="Библията на български и други езици; търсене в Библията; php скриптове с отворен код за представяне на Библията върху отдалечен или локален сървър.">
  <link rel=stylesheet type="text/CSS" href="php-bible.css">

<script type="text/javascript">
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
document.b_open.book.selectedIndex="'.($nxbk-1).'";
document.b_open.book.value="'.($nxbk).'";
document.b_open.chapter.selectedIndex='.($nxch-1).';
document.b_open.submit();
}

function goprev(){
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

</script>

</HEAD>

<BODY>
';
}

function coment_link(){
global $pth,$bk,$bnames,$bn,$ch;
$r='';
if ( (($pth=='38/')   && ($bk>38) && ($bk<=$bn[0]))
  || (($pth=='Tzrg/') && ($bk>0) && ($bk<=$bn[0]))
   )
{
 $r='<a href="coment.php?'.
   rawurlencode(
    trim($bnames[$bk+2*$bn[0]])." $ch:1"
   ).
  '">Тълкувание</a>&nbsp;&nbsp;';
}
return $r;
}

?>

</BODY>
</HTML>
