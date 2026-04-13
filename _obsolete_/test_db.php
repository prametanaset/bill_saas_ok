<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SHOW TABLES LIKE 'ci_sessions'");
if ($result->num_rows > 0) {
    echo "ci_sessions exists\n";
} else {
    echo "ci_sessions DOES NOT EXIST\n";
    // Let's create it maybe?
}
$result2 = $mysqli->query("SHOW DATABASES");
while($row = $result2->fetch_assoc()) {
    echo $row['Database'] . "\n";
}
?>
