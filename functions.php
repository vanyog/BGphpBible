<?php

include("functions-$language.php");

$fnotes = ''; // Бележки под линию
$input_data=array(); // масив за входни данни
check_for_get_data(); // установяване на входните данни, ако са изпратени с GET метод
$last_bk=1; // Последната разпизната в препратка книга. 

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
return '<span class="panel">'.$word_project.'
<b><a href="http://vanyog.com/bible/php/about.html">BGphpBible 2.3.3</a>,</b>
 '.$maintained_by.': 
<b><a href="http://vanyog.com">vanyog.com</a></b>.
</span>';
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

function search_form($i=''){
global $pth, $bk, $ch, $shv, $word_search, $text_to_search, 
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
return '<form name="b_search'.$i.'" method="'.$form_metod.'" action="search.php">'.$mt.
'<input type="HIDDEN" name="version" value="'.$pth.'">
<input type="HIDDEN" name="book" value="'.$bk.'">
<input type="HIDDEN" name="chapter" value="'.$ch.'">
<input type="HIDDEN" name="part" value="0">
<input type="HIDDEN" name="showv" value="'.$shv.'">
<p><label for="srch">'.$text_to_search.'</label><br><input type="TEXT" name="stext" id="srch" '.$ml.'value="" size="'.$search_edit_size.'"></p>
<p><input type="SUBMIT" value="'.$word_search.'"></p>
</form>';
}

function posted($k,$v){
global $input_data;
return array_value($k,$input_data,$v);
}

function array_value($k,$a,$v){
if (array_key_exists($k,$a))
{ if (empty($a[$k])) return $v; else return $a[$k]; } 
else
{ return $v; }
}

// Четене на стих

function read_verse($enc,$pf,$tf,$vi){
$vt='';
$vp=read_vpos($pf,$vi);
if ($vp!=4294967295){
 fseek($tf,$vp);
 $vl=fread($tf,2); $vl=ord($vl[0])+ord($vl[1])*256;
 $vt=decode(fread($tf,$vl));
 $GLOBALS['enc'] = $enc;
 // Обрботване на бележктите под линия и препратките, заградени с {} 
 $vt=preg_replace_callback('/\s*\{(.*?)\}[0-9\*]*/', 'replace_notes', $vt);
}
return make_format($vt);
}

function fromt_verse($vt){
$vt=preg_replace_callback('/\s*\{(.*?)\}[0-9\*]*/', 'replace_notes', $vt);
return make_format($vt);
}

function replace_notes($a){
global $findex, $fnotes, $enc, $sreader;
if($sreader) return '';
$a[1] = make_links($a[1]);
$findex++;
$fnotes.="\n".'<p><span class="fnotes"><sup>'.$findex.'</sup></span> '.
         iconv($enc,'utf-8//IGNORE',make_format($a[1]));
return '<a href="#fnotes" class="fnotes" onclick="to_anchor = true;"><sup>'.$findex.'</sup></a>';
}

function make_links($a){
$parts = preg_split('/,|;/',$a);
foreach($parts as $i=>$p) $parts[$i] = make_link($p);
return implode(', ',$parts);
}

function make_link($p){
global $bnames, $bn, $pt0, $pth, $last_bk, $enc;
$a = array();
if(!preg_match_all('/(.*?)\s(\d+):(\d+)/', $p, $a)) return $p;
$gl = iconv('utf-8',$enc,'гл.');
$st = iconv('utf-8',$enc,'ст.');
if(empty($a[1][0])) $bk = $last_bk;
else if(trim($a[1][0])==$gl) $bk = $GLOBALS['bk'];
else {
  $bk = $bn[0] * 2 + 1;
  while( (trim($bnames[$bk])!=trim($a[1][0])) && ($bk<3*$bn[0]) ) $bk++;
  if(trim($bnames[$bk])!=trim($a[1][0])){ return $p; }
  $bk = $bk - $bn[0] * 2;
}
$lk = $_SERVER['PHP_SELF']."?cversion=$pt0&version=$pth&book=$bk&chapter=".$a[2][0]."&verse=".$a[3][0]."#".$a[3][0];
$last_bk = $bk;
return "<a href=\"$lk\">$p</a>";
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

function audio($pth, $bk, $ch){
global $pt0,$audio_link, $audio_message;
$p = __DIR__."/$pth"."audio.php";
if(!file_exists($p)) return 
'<script>
function audioMessage(l){
alert("'.$audio_message.'");
document.location = l;
}
</script>
<p><a href="index.php?cversion='.$pt0.'&version='.$pth.'&book='.$bk.'&chapter='.$ch.'&listen=on" 
onclick="audioMessage(this);return false;">
'.$audio_link.'</a></p>';
include_once($p);
$lk = audio_link($bk, $ch);
if($lk) return '<p><a href="'.$lk.'" target="_blank">'.$audio_link.'</a></p>';
}

?>