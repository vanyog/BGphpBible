<?php

$gstruct=file("BibleStructure.txt");
$gcount=array(); $j=0;                  // �����, ������� ����������� �� ��������
foreach($gstruct as $l){                // ��������� �� ������ $vcount
 $j++;
 $gcount[$j]=split(' ',trim($l) );
}

?>
