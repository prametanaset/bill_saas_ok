<?php
$conn = mysqli_connect("localhost", "root", "", "bill_xml_st0004");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

echo "--- Data in bill_xml_st0004.db_smtp ---\n";
$res = mysqli_query($conn, "SELECT * FROM db_smtp");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        print_r($row);
    }
} else {
    echo "db_smtp table error: " . mysqli_error($conn) . "\n";
}

echo "\n--- Data in bill_xml_st0004.db_store ---\n";
$res = mysqli_query($conn, "SELECT id, store_name, smtp_status, smtp_host FROM db_store");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

mysqli_close($conn);
?>
