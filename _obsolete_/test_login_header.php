<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/bill_xml_saas/login/verify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('email'=>'admin@example.com', 'pass'=>'123456')));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);     // GET HEADERS
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // DO NOT FOLLOW
$response = curl_exec($ch);
echo $response;
curl_close($ch);
?>
