<?php

$gstruct=file("BibleStructure.txt");
$gcount=array(); $j=0;                  // Масив, описващ структурата на Библията
foreach($gstruct as $l){                // Попълване на масива $vcount
 $j++;
 $gcount[$j]=split(' ',trim($l) );
}

?>
