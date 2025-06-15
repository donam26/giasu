<?php

return [
    'tmn_code' => 'GEBGNQZC',
    'hash_secret' =>'391WOHKIIMQZIH348STZWJTF1I9LO974',
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url' => env('VNPAY_RETURN_URL', '/payment/vnpay/callback'),
]; 