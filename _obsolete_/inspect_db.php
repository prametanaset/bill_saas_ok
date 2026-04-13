<?php
$c = mysqli_connect('localhost', 'root', '', 'bill_xml');
$r = mysqli_query($c, 'DESCRIBE db_store');
while($f = mysqli_fetch_assoc($r)) {
    echo $f['Field'] . "\n";
}
?>
