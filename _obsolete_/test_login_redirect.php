<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/bill_xml_saas/login/verify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('email'=>'admin@example.com', 'pass'=>'wrongpassword')));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
$response = curl_exec($ch);
if (strpos($response, 'Invalid Email') !== false) {
    echo "ERROR MESSAGE SHOWS\n";
} else {
    echo "NO ERROR MESSAGE\n";
    echo $response;
}
curl_close($ch);
?>
