<?php
$conn = new mysqli('localhost', 'root', '', 'bill_xml');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "DESCRIBE db_store";
$result = $conn->query($sql);

$columns = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}
$output = "Columns in db_store: " . implode(', ', $columns);
file_put_contents('db_columns.txt', $output);
$conn->close();
?>
