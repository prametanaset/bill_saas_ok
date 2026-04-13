<?php
$m = new mysqli('localhost', 'root', '');
$r = $m->query("SHOW DATABASES");
while($row = $r->fetch_row()){
    echo $row[0] . "\n";
}
