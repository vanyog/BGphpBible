<?php

correct();

function correct(){
global $pt0,$pth,$bk,$ch,$vr,$bn;
$f=fopen($pt0.'BibleTitles.txt','r');
$l1=fgets($f);// echo "$l1<br>";
fclose($f);
$bna=explode(' ',$l1);
$bk0=$bna[$bk];
$i=0;
do $i++; while (($i<count($bn))&&($bn[$i]!=$bk0));
//echo "$bk $bk0 $i";
if ($i<count($bn)){
 $gch=$ch; $gvr=$vr;
 $df=file($pt0.'_Diff_.txt');
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bk0==$la[0])&&($ch==$la[1])&&(($vr>=$la[2])||$la[2]==1)){
   $gch=$ch+$la[3]; $vr=$gvr+$la[4];
  }
 }
 $df=file($pth.'_Diff_.txt');
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bk0==$la[0])&&($ch==$la[1])&&(($vr>=$la[2])||$la[2]==1)){
   $ch=$gch-$la[3]; $vr=$gvr-$la[4];
  }
 }
}
$bk=$i;
}

?>
