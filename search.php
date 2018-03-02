<?php

include("_options.php");
include("functions.php");
include("search-$language.php");

$wplace=0; $before=4; $after=6;
$pth=posted('version',$default_version); // директория с версията на Библията
$bk=posted('book',1);         // номер на книга
$ch=posted('chapter',1);      // номер на глава
include("hlanguage.php");     // създаване на клас HLanguage
$sst=posted('stext','Аава');  // низ от думи за търсене
$wrd=sptlit_words($sst);      // масив от думи за търсене
$prt=posted('part',0);        // номер на група резултати
$shv=posted('showv',1);       // дали да се показват целите стихове
if (!file_exists($pth.'WordPoint.bin'))
{ die('Файловете с тази Библия липсват.'); }
$wcount=filesize($pth.'WordPoint.bin')/4; // брой на думите в Библията
$aprt=500; if ($shv){ $aprt=50; }         // брой стихове, покадвани на 1 страница

include("structure.php");

Start_page(); // показва началото на страницата

//Отваряне на файловете
$wpf=fopen($pth.'WordPoint.bin','r');
$wtf=fopen($pth.'WordList.txt','r');
if (count($wrd)){
 $cpf=fopen($pth.'ConcP.bin','r');
 $ccf=fopen($pth.'Conc.bin','r');
 if ($shv){
  $vpf=fopen($pth.'CompactPoint.bin','r');
  $vtf=fopen($pth.'CompactText.bin','r');
 }
 
 // съставяне на масива $c с индекси на намерените стихове
 $c=bwverses($wrd[0]);
 $wlnx=bnwords($wplace);
 for($j=1;$j<count($wrd);$j++){
  $c1=bwverses($wrd[$j]);
  $wlnx=$wlnx.bnwords($wplace);
  $c=array_values( array_intersect(array_values($c),$c1) );
 }
 
 //Затваряне на файловете
 fclose($cpf);
 fclose($ccf);
}
else { 
 $c=array(); 
 $wlnx=bnwords(0);
}
fclose($wpf);
fclose($wtf);

// показване на резултата
if (count($c)){ 
 echo '<td><p><b>'.count($c).'</b> '.$found_in.':<br>&nbsp;</td>
<td width="30%" valign="top">
<form name="showv" action="">
<input type="CHECKBOX" name="chb" onclick="ShowWithV();"';
 if ($shv){ echo ' checked'; }
 echo '> 
'.$show_verse_text.'
</form></td>
</tr><tr><td colspan="2">'; 
}
else { echo "<td colspan=2><P>$not_found"; }

echo list_to_text($c);

if ($shv && isset($vpf)){ fclose($vpf); fclose($vtf); }

echo '<p>&nbsp;
<hr size="1" width="98%">
<p>'.$neighbour_words.':
<table><tr>'.$wlnx.'</tr></table>
<hr size="1" width="98%">
<center>
'.search_form().'
</center>
<p>&nbsp;

</td></tr></table>

<table cellspacing="0" width="100%"><tr>'.about_the_project().'</tr></table>

</BODY>
</HTML>
';

//---------функции----------

function bnwords($i){ // връща няколко думи около последната потърсена дума
global $wcount,$wplace,$before,$after;
$r='<td><p>';
$ba=$before+$after;
$i2=$i + $after; 
if ($i2<$ba){ $i2=$ba; }
if ($i2>=$wcount){ $i2=$wcount-1; }
$i1=$i2-$ba; if ($i1<0){ $i1=0; }
for($j=$i1;$j<$i2+1;$j++){
 $w=bword($j,false);
 $n="<a href=\"\" onclick=\"SearchWord('$w');return false;\">".$w.'</a>';
 if ($j==$wplace){ $n='<b>'.$n.'</b>'; }
 $r=$r."\n<br>$n";
}
return $r.'</td>
';
}

function list_to_text($c){ // обръща в стринг за показване масива стихове $c
global $vcount, $bnames, $prt, $aprt, $shv, $more_results;
$r=''; $i=$prt*$aprt; $iend=$i+$aprt; $cc=count($c);
if ($iend>$cc){ $iend=$cc; }
$bk1=1; $ch1=0; $vr=1; $gi=0; $g=0; $b0=''; $b='';
while ($i<$iend){
 do {
  if ($ch1<count($vcount[$bk1])-1){ $ch1++; }
  else {
   if ($bk1<count($vcount)){ $bk1++; }
   else { break; }
   $ch1=1;
  }
  $g=$gi;
  $gi=$gi+$vcount[$bk1][$ch1];
 }
 while ( ($gi<$c[$i]) );
 $vr=$c[$i]-$g;
 $b0=$b;
 $b =rtrim($bnames[count($vcount)+$bk1]);
 $b1=rtrim($bnames[2*count($vcount)+$bk1]);
 if ($b!=$b0){ $r=$r."\n<p><b>$b</b>:"; }
 $r=$r."\n".' <a href="" onclick="OpenVerse('.
    "$bk1,$ch1,$vr".');return false;">'."$ch1:$vr".'</a>';
 if ($shv){ $r=$r.' '.bverse($c[$i]).'<br>'; }
 else { $r=$r.', '; }
 $i++; $ch1--;
 $gi=$g;
}
if ($cc>$aprt){
 $i=1; $r=$r."\n<p>&nbsp;\n<br>$more_results: ";
 while (($i-1)*$aprt<=$cc){
  if ($i==($prt+1)){ $r=$r." <font size=+1><b>$i</b></font>&nbsp;"; }
  else { $r=$r.' <a href="" onclick="SearchPart('.($i-1).');return false;">'."$i</a>&nbsp;"; }
  $i++; 
 }
}
return $r;
}

function bverse($i){ // връща текста на $i-ия стих
global $vpf,$vtf;
$p=fread4($vpf,($i-1)*4);
$l=fread2($vtf,$p);
$t=fread($vtf,$l);
return decode($t);
}

function bwverses($w){ // връща номерата на стиховете, в които се среща думата $w
global $cpf,$ccf,$wplace;
$r=array();
$wplace=bwindex($w);
if ($w==bword($wplace,true)){
 $cp=fread4($cpf,$wplace*4);
 $cc=fread2($ccf,$cp*2);
 $a=fread($ccf,$cc*2);
 for($j=0;$j<$cc;$j++){
  $k=$j*2;
  $r[]=ord($a[$k])+256*ord($a[$k+1]);
 }
}
return $r;
}

function bwindex($w0){ // връща номера който би имала думата $w0
global $wcount,$hlang;
$i1=0; $i2=$wcount-1;
do {
 $i=round(($i1+$i2)/2);
 $w=bword($i,false);
 $c = $hlang->compare($w0,$w); // echo "$w0 $w $c<BR>";
 if ( $c > 0 ){ $i1=$i; } else { $i2=$i; }
} while ($i2-$i1>1);
$w1=bword($i1,false); $w2=bword($i2,false);
if ($hlang->compare($w0,$w2)>0){ return $i2+1; }
else {
 if ($hlang->compare($w0,$w1)>0){ return $i2; }
 else { return $i1; }
}
}

function bword($i,$l){ // връща $i-тата дума, ако $l изписана с малки букви
global $wpf,$wtf;
$wp=fread4($wpf,$i*4);
fseek($wtf,$wp);
$w=split("\r",fread($wtf,64));
if ($l){ return lc_word($w[0]); }
else { return $w[0]; }
}

function sptlit_words($st){ // връща масив от думите в $st, изписани само с малки букви
global $hlang;
$r=array(); $w='';
for($i=0;$i<strlen($st);$i++){
 $l=$hlang->lc_letter($st[$i]);
 if ($l>-1){ $w=$w.$l; }
 else { 
  if ($w){ $r[]=$w; }
  $w='';
 } 
}
if ($w){ $r[]=$w; }
return $r;
}

function lc_word($t){ // променя всички букви на $t на малки
global $hlang;
for($i=0;$i<strlen($t);$i++){
 $l=$hlang->lc_letter($t[$i]);
 if ($l>-1) $t[$i]=$l;
}
return $t;
}

function fread2($f,$p){ // чете двубайтово цяло число от позиция $p на файл $f
fseek($f,$p);
$r=fread($f,2);
return ord($r[0])+256*ord($r[1]);
}

function fread4($f,$p){ // чете четирибайтово цяло число от позиция $p на файл $f
fseek($f,$p);
$r=fread($f,4);
return ord($r[0])+256*ord($r[1])+256*256*ord($r[2])+256*256*256*ord($r[3]);
}


function Start_page(){
global $pth,$bk,$ch,$sst,$search_result_for,$word_bible;
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>

<HEAD>
  <TITLE>Библията на български - php реализация</TITLE>
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
  <link rel=stylesheet type="text/CSS" href="php-bible.css">
<script language="JavaScript" type="text/javascript">

function OpenVerse(b,c,v){
 if (v>1) document.f1.action="index.php#"+v;
 else document.f1.action="index.php";
 document.f1.book.value=b;
 document.f1.chapter.value=c;
 document.f1.verse.value=v;
 document.f1.submit();
}

function SearchWord(w){
 document.b_search.stext.value=w;
 document.b_search.submit();
}

function SearchPart(p){
 document.b_search.stext.value="'.$sst.'";
 document.b_search.part.value=p;
 document.b_search.submit();
}

function ShowWithV(){
 if (document.showv.chb.checked){ document.b_search.showv.value="1"; }
 else { document.b_search.showv.value="0"; }
 SearchWord("'.$sst.'");
}

</script>
</HEAD>

<BODY>

<form name="f1" method="POST" action="index.php">
<input type="HIDDEN" name="version" value="'.$pth.'">
<input type="HIDDEN" name="book" value="'.$bk.'">
<input type="HIDDEN" name="chapter" value="'.$ch.'">
<input type="HIDDEN" name="verse" value="">
</form>

<table width="100%" cellspacing="0" bgcolor="#FFFFFF" border="0">
<tr><td>
<p>'.$search_result_for.': "<b>'.$sst.'</b>":
<p>&nbsp;<br>
</td>
<td ALIGN="right" VALIGN="top">
<A HREF="" onclick="javascript:document.f1.submit();return false;"><B>'.$word_bible.'</B></A>&nbsp;&nbsp;
</td></tr>
<tr>
';
}

?>
