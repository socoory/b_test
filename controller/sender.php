<?php
if (!defined("__BAAS_API__")) exit;

Class Sender extends Controller
{
    function __construct() {
        parent::__construct();

    }

    function send() {
        $data = array('message' => 'Hello World!');

        $ids = array('APA91bF3lVRHub2cWTR26C0sDS78ebUDeH3SqXJ5nIr7ViBNiSbAnDbVKsFCL2ZDit16AORyc0xBnb2bbuAabS4JD3wmXG5TzF0u8xauaF0ihAXAsBQpgPrunKt38p6SfUyKe25AqTsF66Bf0itcv_abGyY7DFCG7w');

        $this->sendGoogleCloudMessage($data, $ids);
    }

    function sendGoogleCloudMessage($data, $ids) {
        $apiKey = 'AIzaSyBrEdt9eEU_ZU9V0dZlnNUh4x5ENx5oW8E';
        $url = 'https://android.googleapis.com/gcm/send';

        $post = array(
            'registration_ids' => $ids,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'GCM error: ' . curl_error($ch);
        }

        curl_close($ch);

        echo $result;
    }
}