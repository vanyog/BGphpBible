<?php

$bnames=array(); $bn=0;

if (file_exists($pth.'BibleTitles.txt')){
 $bnames=file($pth.'BibleTitles.txt'); // ������ �� ���������� �� �������
 $bn=explode(' ',trim($bnames[0]));      // ���� �� ������� � ��������
 $vcount=get_structure($bn,$pth);          // �����, ������� ����������� �� ��������
}

function get_structure($bn,$pth){ // ���� ������, ������� ����������� �� ��������
$bstruct=file($pth.'BibleStructure.csv');
$vcount=array();
for($j=1;$j<=$bn[0];$j++){
 $vcount[$j]=explode(' ',trim($bstruct[$j-1]) );
}
return $vcount;
}

function vindex($bk,$ch,$vcount){ // �������� ������� �� ������ ���� �� ����� $ch �� ����� $bk
$vi=0;
for($i=1;$i<=$bk;$i++){
 if ($i<$bk){ 
  for ($c=1;$c<count($vcount[$i]);$c++){ $vi=$vi+$vcount[$i][$c]; }
 }
 else {
  for($c=1;$c<$ch;$c++){ $vi=$vi+(isset($vcount[$bk][$c])?$vcount[$bk][$c]:0); }
 }
}
return $vi;
}

?>
