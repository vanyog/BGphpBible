<?php

// ���� ����� ������ ��� ������ �� �������� �� �� ��������
$version = array(
'Ort/'=>'����������� ������ 1991',   
'38/'=>'������ 1940',
//'Tzrg-f/'=>'"�����������" ������',
'Tzrg/'=>'"�����������" ��� �����',
'Veren/'=>'����������� "�����"',
//'mk/'=>'���������� ������ 1999',
//'Mac1/'=>'���������� ������ 1990',
//'Srb/'=>'������� ������ (���-��������)',
'Ru/'=>'�������� �� �����',
'Asv/'=>'American Standard Version',
//'KjvSn/'=>"King James with Strong's numbers"
'KJV/'=>"King James Version",
'Gr/'=>"����������� ��� �����"
);

// ���� ����� ������ ��� ������ �� �������� �� ������� �� ����� �������
$on_other_sites = array(
'mk/'=>"http://mkbible.net/biblija/index.php",
'Mac1/'=>"http://mkbible.net/biblija/index.php",
'Srb/'=>"http://mkbible.net/biblija/index.php"
//,'KJV/'=>"http://mkbible.net/biblija/index.php"
);

$default_version='38/'; // ������, ����� �� ������ �� ������������
$language='bg';         // ���� �� ���������� �� �����
$motranslator=true;     // ���� �� �� ������� ����������� motranslator.js
$search_edit_size=40;   // ������ �� ���������� ���� �� ��������� �� ����� �� �������

// ������� �� ���������� ����� �� �������� �� ��������
function version_languege($pth){
switch ($pth){
 case 'Asv/': return 'en0'; break;
 case 'KJV/': return 'en0'; break;
 case 'KjvSn/': return 'en0'; break;
 case 'Mac07/': return 'ma'; break;
 case 'Ru/': return 'ru'; break;
 case 'Gr/': return 'gr'; break;
 default: return 'bg';
}
}

?>
