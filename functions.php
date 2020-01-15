<?php

include("functions-$language.php");

$input_data=array(); // масив за входни данни
check_for_get_data(); // установяване на входните данни, ако са изпратени с GET метод

function a_path($p){
if (strpos($_SERVER['SERVER_SOFTWARE'],'(Win32)'))
 return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/$p";
else
 return $p;
}

function about_version(){
global $apth,$pth,$about_version;
 $alk=$apth.'about.html';
 $lk=$pth.'about.html';
 if (file_exists($alk))
  return '<a href="'.$lk.'" id="about_link">'.$about_version.'</a>';
 else
  return '&nbsp;';
}

function about_the_project(){
global $word_project,$maintained_by,$and_hosted_at;
return '<td class="panel">'.$word_project.'
<b><a href="http://vanyog.com/bible/php/about.html">BGphpBible 2.0.1</a>,</b>
 '.$maintained_by.': 
<b><a href="http://vanyog.com">vanyog.com</a></b>.
</td>';
}

function parallel_form(){
global $pth,$bk,$ch,$form_metod;
return '<FORM METHOD="'.$form_metod.'" ACTION="parallel.php" NAME="b_parallel">
<INPUT TYPE="HIDDEN" NAME="version" VALUE="'.$pth.'">
<INPUT TYPE="HIDDEN" NAME="book" VALUE="'.$bk.'">
<INPUT TYPE="HIDDEN" NAME="chapter" VALUE="'.$ch.'">
<INPUT TYPE="HIDDEN" NAME="verse" VALUE="0">
<INPUT TYPE="HIDDEN" NAME="index" VALUE="0">
</FORM>';
}

function search_form(){
global $pth, $bk, $ch, $shv, $word_search, 
 $motranslator, $motrans_help_tip, $motrans_help, $motrans_lang_tip,
 $search_edit_size, $form_metod;
$mt=''; $ml='';
if ($motranslator) {
$mt='
<script src="mobrowser.js"></script>
<script src="CookieManager.js"></script>
<script src="motranslator.js"></script>
<script>
cTranslator.sGlobalLangID = cCyrPho.sDName
cTranslator.registerLang( cOffLang )
cTranslator.registerLang( cCyrPho )
</script>
<A href="#" id="langHelpLink" title="'.$motrans_help_tip.'" class="ln">'.$motrans_help.'</A>
<A href="#" id="langLink" title="'.$motrans_lang_tip.'" class="ln"></A>
';
$ml='MOLANG="DEFAULT" ';
}
return '<form name="b_search" method="'.$form_metod.'" action="search.php">'.$mt.
'<input type="HIDDEN" name="version" value="'.$pth.'">
<input type="HIDDEN" name="book" value="'.$bk.'">
<input type="HIDDEN" name="chapter" value="'.$ch.'">
<input type="HIDDEN" name="part" value="0">
<input type="TEXT" name="stext" '.$ml.'value="" size="'.$search_edit_size.'">
<input type="HIDDEN" name="showv" value="'.$shv.'"> 
<input type="SUBMIT" value="'.$word_search.'">&nbsp;
</form>';
}

function posted($k,$v){
global $input_data;
return array_value($k,$input_data,$v);
}

function array_value($k,$a,$v){
if (array_key_exists($k,$a))
{ if ($a[$k]=="") return $v; else return $a[$k]; } 
else
{ return $v; }
}

function read_verse($enc,$pf,$tf,$vi){
//global $findex,$fnotes;
$vt='';
$vp=read_vpos($pf,$vi);
if ($vp!=4294967295){
 fseek($tf,$vp);
 $vl=fread($tf,2); $vl=ord($vl[0])+ord($vl[1])*256;
 $vt=decode(fread($tf,$vl)); //die($vt);
 $vt=preg_replace_callback('/\s*\{(.*?)\}[0-9\*]*/', 'replace_notes', $vt);
}
return make_format($vt);
}

function fromt_verse($vt){
$vt=preg_replace_callback('/\s*\{(.*?)\}[0-9\*]*/', 'replace_notes', $vt);
return make_format($vt);
}

function replace_notes($a){
//die(print_r($a,true));
global $findex,$fnotes,$enc;
$findex++;
$fnotes.="\n".'<p><span class="fnotes"><sup>'.$findex.'</sup></span> '.
         iconv($enc,'utf-8//IGNORE',make_format($a[1]));
return '<a href="#fnotes" class="fnotes"><sup>'.$findex.'</sup></a>';
}

function make_format($vt){
$vt = preg_replace('/(\{.*?\})/', '<span class="note">${1}</span>', $vt);
return preg_replace('/\|(.*?)\|/', '<i>${1}</i>', $vt);
}

function read_vpos($pf,$vi){
fseek($pf,$vi*4);
$posv=fread($pf,4);
$posv=ord($posv[0])+256*(ord($posv[1])+256*(ord($posv[2])+256*ord($posv[3])));
return $posv;
}

function decode($v){
global $hlang;
$a=explode('\|',$v);
$r=''; $y=true;
foreach($a as $p){
 if ($y){ $r=$r.$p; } else { $r=$r.'<i>'.$p.'</i>'; }
 $y=!$y;
}
return nl2br($hlang->encode($r));
}

function get_query(){
global $bnames,$bk,$ch,$vr;
$d=explode(' ',rawurldecode($_SERVER['QUERY_STRING']));
if(!isset($d[1])) $d[1] = 0;
if(!isset($d[2])) $d[2] = '';
if (!intval($d[1])){ $bkn=$d[0].' '.$d[1]; $chv=$d[2]; }
else { $bkn=$d[0]; $chv=$d[1]; }
$lns=file("Abrevs.txt");
foreach($lns as $l){
 $i=explode('=',$l);
// echo $i[0]." $bkn|<br>";
 if ($i[0]==$bkn){
  $n=explode(' ',$bnames[0]);
  for($j=1;$j<count($n);$j++){
   if (intval($n[$j])-intval($i[1])==0){
    $bk=$j;
    $k=explode(':',$chv);
    $ch=$k[0];
    if (count($k)>1){ $vr=$k[1]; }
    break;
   }
  }
  break;
 }
}
}

function check_for_get_data(){
global $input_data;
parse_str($_SERVER['QUERY_STRING'],$a);
$c='chapter';
if (in_array($c,array_keys($a))) $input_data=$a;
else $input_data=$_POST;
}

function para_style($pth){
switch ($pth) {
case 'BL/': case 'Tzrg/': case 'Ru/': return true; break;
default: return false;
}
}

?>