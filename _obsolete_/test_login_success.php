<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/bill_xml_saas/login/verify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('email'=>'admin@example.com', 'pass'=>'123456')));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie2.txt");
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie2.txt");
$response = curl_exec($ch);
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
echo "Final URL: " . $url . "\n\n";

if (strpos($response, 'Sign In') !== false) {
    echo "REACHED LOGIN PAGE (Bounced out)\n";
    if (strpos($response, 'Invalid Email') !== false) {
        echo "Reason: Invalid credentials\n";
    } elseif (strpos($response, 'message-box error') !== false) {
        echo "Reason: Other error message shown\n";
        // Extract the error message
        preg_match('/<div class="message-box error">(.*?)<\/div>/s', $response, $matches);
        if(!empty($matches)) {
            echo "Error text: " . trim($matches[1]) . "\n";
        }
    } else {
        echo "Reason: SILENT redirect (no error message!)\n";
    }
} else {
    echo "SUCCESSFULLY LOGGED IN (Dashboard Reached)\n";
}
curl_close($ch);
?>
