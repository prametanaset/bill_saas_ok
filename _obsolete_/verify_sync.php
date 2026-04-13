<?php
echo "MASTER DB (bill_xml):\n";
$m = new mysqli('localhost', 'root', '', 'bill_xml');
$r = $m->query("SELECT site_name, domain FROM db_sitesettings WHERE id=1");
print_r($r->fetch_assoc());

echo "\nTENANT DB (bill_xml_st0002):\n";
$t = new mysqli('localhost', 'root', '', 'bill_xml_st0002');
$r2 = $t->query("SELECT site_name, domain FROM db_sitesettings WHERE id=1");
print_r($r2->fetch_assoc());
