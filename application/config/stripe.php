<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
/* 
| ------------------------------------------------------------------- 
|  Stripe API Configuration 
| ------------------------------------------------------------------- 
| 
| You will get the API keys from Developers panel of the Stripe account 
| Login to Stripe account (https://dashboard.stripe.com/) 
| and navigate to the Developers >> API keys page 
| 
|  stripe_api_key            string   Your Stripe API Secret key. 
|  stripe_publishable_key    string   Your Stripe API Publishable key. 
|  stripe_currency           string   Currency code. 
*/ 

$config['stripe_currency']        = 'thb'; // Thai Baht (ใช้ 'usd' ถ้าชาร์จเป็น USD)
$config['stripe_api_key']         = 'sk_test_placeholder'; // ⚠ แทนที่ด้วย Secret Key จริงจาก Stripe Dashboard
$config['stripe_publishable_key'] = 'pk_test_placeholder'; // ⚠ แทนที่ด้วย Publishable Key จริงจาก Stripe Dashboard