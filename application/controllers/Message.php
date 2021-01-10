<?php

class PushyAPI {
    static public function sendPushNotification($toRole, $data, $to, $options) {
        // Insert your Secret API Key here
        $apiKey = "";
        if ($toRole == 'admin') {
          $apiKey = "d047de41a922b5b900d7d6cf1af40c33cdb9ddb06c018a51ed8cd94dfe0c4dce";
        } else if ($toRole == 'user') {
          $apiKey = '03f12a4085603bcaa599ecfd667b3cc19b29f4ee53a7474db68f9240d5f6c378';
        }

        // Default post data to provided options or empty array
        $post = $options ?: array();

        // Set notification payload and recipients
        $post['to'] = $to;
        $post['data'] = $data;

        // Set Content-Type header since we're sending JSON
        $headers = array(
            'Content-Type: application/json'
        );

        // Initialize curl handle
        $ch = curl_init();

        // Set URL to Pushy endpoint
        curl_setopt($ch, CURLOPT_URL, 'https://api.pushy.me/push?api_key=' . $apiKey);

        // Set request method to POST
        curl_setopt($ch, CURLOPT_POST, true);

        // Set our custom headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Get the response back as string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set post data as JSON
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_UNESCAPED_UNICODE));

        // Actually send the push
        $result = curl_exec($ch);

        // Display errors
        if (curl_errno($ch)) {
            echo curl_error($ch);
        }

        // Close curl handle
        curl_close($ch);

        // Attempt to parse JSON response
        $response = @json_decode($result);

        // Throw if JSON error returned
        if (isset($response) && isset($response->error)) {
            //throw new Exception('Pushy API returned an error: ' . $response->error);
        }
    }
    
    static public function send_message($toRole, $token, $notificationType, $showNotification, $title, $body, $data) {
      $to = array($token);
      $data['title'] = $title;
      $data['body'] = $body;
      $data['notification_type'] = intval($notificationType);
      $data['show_notification'] = intval($showNotification);
      $options = array(
        'notification' => array(
          'badge' => 1,
          'body'  => $body
        )
      );
      PushyAPI::sendPushNotification($toRole, $data, $to, $options);
    }
}