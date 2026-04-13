<?php
$cookie_file = file_get_contents('cookie2.txt');
preg_match('/v10_session\s+([a-zA-Z0-9]+)/', $cookie_file, $matches);
if(!empty($matches)){
    $cookie = $matches[1];
    $mysqli = new mysqli("localhost", "root", "", "bill_xml");
    $res = $mysqli->query("SELECT data FROM ci_sessions WHERE id='$cookie'")->fetch_assoc();
    echo "Session Data for $cookie: \n" . $res['data'] . "\n";
} else {
    echo "No cookie found\n";
}
?>
