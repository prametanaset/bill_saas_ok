<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/bill_xml_saas/signup/register_store');
curl_setopt($ch, CURLOPT_POST, 1);
$post_data = [
    'store_name' => 'Store Test 99',
    'contact_name' => 'Demo User',
    'email' => 'store99_' . time() . '@test.com',
    'mobile' => '0999999999',
    'password' => '123456',
    'cpassword' => '123456',
    'package_id' => '1',
    'payment_method' => 'trial'
];
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: $httpcode\n";
echo "--- Output ---\n$response\n";
curl_close($ch);
