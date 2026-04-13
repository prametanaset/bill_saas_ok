<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/bill_xml_saas/login/verify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('email'=>'admin@example.com', 'pass'=>'123456')));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, "c:/laragon-8/www/bill_xml_saas/cookie3.txt");
curl_setopt($ch, CURLOPT_COOKIEFILE, "c:/laragon-8/www/bill_xml_saas/cookie3.txt");
$response = curl_exec($ch);
curl_close($ch);

$cookie_file = file_get_contents('c:/laragon-8/www/bill_xml_saas/cookie3.txt');
preg_match('/v10_session\s+([a-zA-Z0-9]+)/', $cookie_file, $matches);
if(!empty($matches)){
    $cookie = $matches[1];
    $mysqli = new mysqli("localhost", "root", "", "bill_xml");
    $res = $mysqli->query("SELECT data FROM ci_sessions WHERE id='$cookie'")->fetch_assoc();
    if($res) {
        echo "Session Data for $cookie: \n" . $res['data'] . "\n";
    } else {
        echo "Session Data for $cookie NOT FOUND!\n";
    }
} else {
    echo "No cookie found\n";
}
?>
