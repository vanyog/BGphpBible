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
include("functions.php");
include("parallel-$language.php");

//print_r($_POST);

$vpth=array_keys($version);   // ����� � ������������ �� ��������
$pth=posted('version',$default_version); // ������������ � ��������� �� ��������
include("hlanguage.php");
$bk=posted('book',1);    // ����� �� �������� �����
$ch=posted('chapter',1); // ����� �� �������� �����
$vr=posted('verse',1);   // ����� �� ������� ����
$gch=$ch; $gvr=$vr;      // "��������" ������ �� �������� ����� � ����
include("structure.php");// ������� ���������� �� ����������� �� ��������
if ($pth!='/') globalize();// ���������� �� "����������" ����� � ����
$next_bk=$bk;
$prev_bk=$bk;
$next_ch=$ch;
$prev_ch=$ch;
$next_vr=$vr;
$prev_vr=$vr;
next_prev(); // �������� ������ �� ��������� � �� ��������� ���� 
$fnotes=''; // ������� ��� �����
$findex=0;  // ����� �� ��������� ��� �����

start_page(); // ������ �� ����������

// ��������� �� ����� �� ���������� �������
foreach($vpth as $p) if (!in_array($p,array_keys($on_other_sites))) parallel($p);

// ��������� �� ��������� ��� �����
if ($fnotes) 
echo "\n".'<P>&nbsp;
<HR SIZE="1" ALIGN="left" WIDTH="30%">
<A NAME="fnotes"></A>'.$fnotes;

echo '</DIV>
<table width="100%" cellspacing="0"><tr>
'.pbutton().
about_the_project().
nbutton().
'
</tr></table>';

// --------- ������� ----------

function globalize(){
global $bn,$pth,$bk,$ch,$vr,$gch,$gvr;
$apth=a_path($pth);
$dfn="$apth".'_Diff_.txt';
if (file_exists($dfn)){
 $df=file($dfn);
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bn[$bk]==$la[0])&&($ch==$la[1])&&($vr>=$la[2])){
   $gch=$ch+$la[3]; $gvr=$vr+$la[4];
  }
 }
}
}

function parallel($p){
global $pth,$bn,$bk,$version,$gch,$gvr,$hlang;
// ����������� ��� "�������" ������ �� ����� $ch � ���� $vr
$apth=a_path($p);
$dfn=$apth.'_Diff_.txt';
$ch=$gch; $vr=$gvr;
if (file_exists($dfn)){
 $df=file($dfn);
 foreach($df as $l){
  $la=explode(' ',trim($l));
  if (($bn[$bk]==$la[0])&&($ch==$la[1])&&($vr>=$la[2])){
   $ch=$gch-$la[3]; $vr=$gvr-$la[4];
  }
 }
}
// ���������� "��������" ����� �� ������� $bk1
$bn0=file($p."BibleTitles.txt");
$bn1=explode(' ',trim($bn0[0]));
if ($pth!='/') $bk1=array_search( $bn[$bk],array_slice($bn1,1) ) + 1;
else $bk1=array_search( $bk,array_slice($bn1,1) ) + 1;
if (($bk1==1)&&($bk!=1)) // ��� ���� ������ �����
{ $vt=''; $bn3=''; $bk1=1; $vr=''; }
else {
 // ���������� ������� �� �����
 $vi=vindex($bk1,$ch,get_structure($bn0,$p))+$vr-1;
 // ������ �� ����� $vt;
 $pf=fopen($p.'CompactPoint.bin','r');
 $tf=fopen($p.'CompactText.bin','r');
 $hlang->HLanguage(version_languege($p));
 $vt=read_verse($pf,$tf,$vi);
 $bn3=' - '.$bn0[2*$bn1[0]+$bk1]." $ch:$vr";
}
echo "\n".'<P><B><A HREF="" ONCLICK="BkToBible('
     ."'$p',$bk1,$ch,'$vr'".');return false;">'.$version[$p]."$bn3</B></A>
<P CLASS=\"p0\">$vt".'
<P>&nbsp;';
}

function start_page(){
global $next_bk, $prev_bk, $next_ch, $prev_ch, $next_vr, $prev_vr;
header("Content-Type: text/html; charset=windows-1251");
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>

<HEAD>
  <TITLE>�������� �� ��������� - php ����������</TITLE>
  <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
  <link rel=stylesheet type="text/CSS" href="php-bible.css">
<SCRIPT TYPE="text/javascript">
function BkToBible(p,b,c,v){
if (v>1) document.forms.b_open.action="index.php#"+v;
document.forms.b_open.version.value=p;
document.forms.b_open.book.value=b;
document.forms.b_open.chapter.value=c;
document.forms.b_open.verse.value=v;
document.forms.b_open.submit();
}
function NextVerse(){
document.forms.b_parallel.version.value="/";
document.forms.b_parallel.book.value="'.$next_bk.'";
document.forms.b_parallel.chapter.value="'.$next_ch.'";
document.forms.b_parallel.verse.value="'.$next_vr.'";
document.forms.b_parallel.submit();
}
function PrevVerse(){
document.forms.b_parallel.version.value="/";
document.forms.b_parallel.book.value="'.$prev_bk.'";
document.forms.b_parallel.chapter.value="'.$prev_ch.'";
document.forms.b_parallel.verse.value="'.$prev_vr.'";
document.forms.b_parallel.submit();
}
</SCRIPT>
</HEAD>

<BODY>
<FORM NAME="b_open" METHOD="POST" ACTION="index.php">
<INPUT TYPE="HIDDEN" NAME="version" VALUE="">
<INPUT TYPE="HIDDEN" NAME="book" VALUE="">
<INPUT TYPE="HIDDEN" NAME="chapter" VALUE="">
<INPUT TYPE="HIDDEN" NAME="verse" VALUE="">
</FORM>
'.parallel_form().'
'.tbuttons().
'
<DIV CLASS="content">
<H1>��������� ������� (��������� �� ���������)</H1>
';
}

function tbuttons(){
global $prev_verse,$next_verse;
return '<table width="100%" cellspacing="0"><tr>
'.pbutton().'
'.nbutton().'
</tr></table>';
}


function pbutton(){
global $prev_verse;
return '<td class="panel">
<input type="BUTTON" value="'.$prev_verse.'" ONCLICK="PrevVerse()">
</td>';
}

function nbutton(){
global $next_verse;
return '<td class="panel" align="right">
<input type="BUTTON" value="'.$next_verse.'" ONCLICK="NextVerse()">
</td>';
}

function next_prev(){
global $bn,$next_bk, $prev_bk, $next_ch, $prev_ch, $next_vr, $prev_vr;
if ($bn) $next_bk=$bn[$next_bk];
$gs=file("BibleStructure.txt");
$gvc=explode(' ',trim($gs[$next_bk-1]));
if ($next_vr<$gvc[$next_ch]) $next_vr++;
else {
 if ($next_ch<$gvc[0]) { $next_vr=1; $next_ch++; }
 else if ($next_bk<77){ $next_ch=1; $next_vr=1; $next_bk++; }
} 
if ($prev_vr>1) $prev_vr--;
else {
 if ($prev_ch>1) { $prev_vr=$gvc[$prev_ch-1]; $prev_ch--; }
 else if ($prev_bk>1){
  $prev_bk--; 
  $gvc=explode(' ',trim($gs[$prev_bk-1])); 
  $prev_ch=$gvc[0]; $prev_vr=$gvc[$prev_ch];
 }
} 
}


?>

</BODY>
</HTML>
