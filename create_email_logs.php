<?php
$mysqli = new mysqli("localhost", "root", "", "bill_xml");
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit;
}

$sql = "CREATE TABLE IF NOT EXISTS `db_email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `email_to` text DEFAULT NULL,
  `email_cc` text DEFAULT NULL,
  `email_bcc` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `attachment` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `error_msg` text DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($mysqli->query($sql) === TRUE) {
    echo "success";
} else {
    echo "Error: " . $mysqli->error;
}
$mysqli->close();
?>
