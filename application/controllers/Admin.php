<?php

require('Message.php');

class Admin extends CI_Controller {
  
  public function get_used_vaccines() {
  	$date = $this->input->post('date');
    $slots = $this->db->query("SELECT * FROM `used_vaccines`")->result_array();
    $activeSlots = [];
    for ($i=0; $i<sizeof($slots); $i++) {
      $this->db->where('id', $slots[$i]['slot_id']);
      $membersIDs = $slots[$i]['members'];
      echo "ID: " . $slots[$i]['id'] . ", members: " . $membersIDs . "\n";
      if ($membersIDs == NULL || $membersIDs == "") {
      	$membersIDs = array();
      } else {
      	$membersIDs = json_decode($membersIDs, true);
      }
      $members = [];
      for ($j=0; $j<sizeof($memberIDs); $j++) {
      	$member = $this->db->query("SELECT * FROM `members` WHERE `id`=" . $membersIDs[$j])->row_array();
      	array_push($members, $member);
      }
      $slot = $this->db->get('slots')->row_array();
      $user = $this->db->get_where('users', array('id' => intval($slots[$i]['user_id'])))->row_array();
      $activeSlot = $slots[$i];
      $activeSlot['name'] = $user['name'];
      $activeSlot['members'] = $members;
      array_push($activeSlots, $activeSlot);
    }
    echo json_encode($activeSlots);
  }
  
  public function get_active_slots() {
  	$date = $this->input->post('date');
    $slots = $this->db->query("SELECT * FROM `used_vaccines` WHERE `done`=0")->result_array();
    $activeSlots = [];
    for ($i=0; $i<sizeof($slots); $i++) {
      $this->db->where('id', $slots[$i]['slot_id']);
      $slot = $this->db->get('slots')->row_array();
      if ($slot['start_date'] >= $date && $date < $slot['end_date']) {
        $user = $this->db->get_where('users', array('id' => intval($slots[$i]['user_id'])))->row_array();
      	$activeSlot = $slots[$i];
      	$activeSlot['name'] = $user['name'];
      	array_push($activeSlots, $activeSlot);
      }
    }
    echo json_encode($activeSlots);
  }
  
  public function send_status() {
    $userID = intval($this->input->post('user_id'));
    $statuses = $this->input->post('statuses');
    $token = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array()['fcm_token'];
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = 'AAAAIwQS5GU:APA91bEKw_FfDy4doPGHEDu9pQKRjq8zX6Fh9SVLQFCULbC97RNfmMh3bv83s5i4FYTEw9Aj9-qDRl7vKlBHUe_mDT8n4FFxkLmXielxDoNHkcIs2UpiVpwBdoZI6Uc_gRqQDnOn_55z';
    $title = "Pembaharuan status pasien";
    $body = "";
    $data = array(
      'status' => $statuses,
      'type' => 'send_status'
    );
    $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
  }
  
  public function add_status() {
    $status = $this->input->post('status');
    $users = $this->db->get('users')->result_array();
    for ($i=0; $i<sizeof($users); $i++) {
    $user = $users[$i];
    $userID = intval($user['id']);
    $token = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array()['fcm_token'];
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = 'AAAAIwQS5GU:APA91bEKw_FfDy4doPGHEDu9pQKRjq8zX6Fh9SVLQFCULbC97RNfmMh3bv83s5i4FYTEw9Aj9-qDRl7vKlBHUe_mDT8n4FFxkLmXielxDoNHkcIs2UpiVpwBdoZI6Uc_gRqQDnOn_55z';
    $title = "Pembaharuan status pasien";
    $body = "";
    $data = array(
      'status' => $status,
      'type' => 'add_status'
    );
    $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    }
  }
  
  public function edit_status() {
    $status = $this->input->post('status');
    $users = $this->db->get('users')->result_array();
    for ($i=0; $i<sizeof($users); $i++) {
    $user = $users[$i];
    $userID = intval($user['id']);
    $token = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array()['fcm_token'];
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = 'AAAAIwQS5GU:APA91bEKw_FfDy4doPGHEDu9pQKRjq8zX6Fh9SVLQFCULbC97RNfmMh3bv83s5i4FYTEw9Aj9-qDRl7vKlBHUe_mDT8n4FFxkLmXielxDoNHkcIs2UpiVpwBdoZI6Uc_gRqQDnOn_55z';
    $title = "Pembaharuan status pasien";
    $body = "";
    $data = array(
      'status' => $status,
      'type' => 'edit_status'
    );
    $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    }
  }
  
  public function delete_status() {
    $uuid = $this->input->post('uuid');
    $users = $this->db->get('users')->result_array();
    for ($i=0; $i<sizeof($users); $i++) {
    $user = $users[$i];
    $userID = intval($user['id']);
    $token = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array()['fcm_token'];
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = 'AAAAIwQS5GU:APA91bEKw_FfDy4doPGHEDu9pQKRjq8zX6Fh9SVLQFCULbC97RNfmMh3bv83s5i4FYTEw9Aj9-qDRl7vKlBHUe_mDT8n4FFxkLmXielxDoNHkcIs2UpiVpwBdoZI6Uc_gRqQDnOn_55z';
    $title = "Pembaharuan status pasien";
    $body = "";
    $data = array(
      'uuid' => $uuid,
      'type' => 'delete_status'
    );
    $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('to' => $token, 'notification' => $notification, 'priority' => 'high', 'data' => $data);
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    }
  }
  
  public function accept_user_chat() {
    $userID = intval($this->input->post('user_id'));
    $adminID = intval($this->input->post('admin_id'));
    $chatWithAdminID = intval($this->db->get_where('users', array(
      'id' => $userID
    ))->row_array()['chat_with_admin_id']);
    if ($chatWithAdminID == 0) {
      $this->db->where('id', $userID);
      $this->db->update('users', array(
        'chat_with_admin_id' => $adminID
      ));
      $this->db->query("DELETE FROM `messages` WHERE `user_id`=" . $userID . " AND `admin_id`=" . $adminID . " AND `type`=1");
      $user = $this->db->get_where('users', array(
        'id' => $userID
      ))->row_array();
      PushyAPI::send_message("user", $user['pushy_token'], 4, 1, 'Obrolan dengan Admin sedang aktif', 'Klik untuk memulai', array(
        'admin_id' => $adminID
      ));
      echo "Admin ID: " . $adminID;
    } else {
      $this->db->query("DELETE FROM `messages` WHERE `user_id`=" . $userID . " AND `admin_id`=" . $adminID . " AND `type`=1");
      echo -1;
    }
  }
  
  public function send_message() {
    $message = $this->input->post('message');
    $shortMessage = $message;
    if (strlen($message) > 60) {
      $shortMessage = substr($message, 0, 60);
    }
    $userID = intval($this->input->post('user_id'));
    $adminID = intval($this->input->post('admin_id'));
    $date = $this->input->post('date');
    $this->db->insert('messages', array(
      'user_id' => $userID,
      'admin_id' => $adminID,
      'sender' => 'admin',
      'message' => $message,
      'date' => $date
    ));
    $lastID = intval($this->db->insert_id());
    $user = $this->db->get_where('users', array(
        'id' => $userID
      ))->row_array();
    $messageInfo = $this->db->get_where('messages', array(
          'id' => $lastID
        ))->row_array();
    $messageInfo['admin_name'] = $this->db->get_where('admins', array(
      'id' => $adminID
    ))->row_array()['name'];
    PushyAPI::send_message("user", $user['pushy_token'], 5, 1, 'Pesan baru', $shortMessage, array(
        'data' => json_encode($messageInfo)
      ));
    $row = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
    $row['admin_name'] = $this->db->get_where('admins', array(
      'id' => $adminID
    ))->row_array()['name'];
    echo json_encode($row);
  }
  
  public function send_image() {
    $userID = intval($this->input->post('user_id'));
    $adminID = intval($this->input->post('admin_id'));
    $date = $this->input->post('date');
    $config = array(
        'upload_path' => './userdata',
        'allowed_types' => "gif|jpg|png|jpeg",
        'overwrite' => TRUE,
        'max_size' => "2048000"
        );
        $this->load->library('upload', $config);
        if($this->upload->do_upload('file')) { 
          $this->db->insert('messages', array(
            'user_id' => $userID,
            'admin_id' => $adminID,
            'message' => '',
            'sender' => 'admin',
            'image' => $this->upload->data()['file_name'],
            'date' => $date
          ));
          $lastID = intval($this->db->insert_id());
          $user = $this->db->get_where('users', array(
              'id' => $userID
            ))->row_array();
          $messageInfo = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
          $messageInfo['admin_name'] = $this->db->get_where('admins', array(
            'id' => $adminID
          ))->row_array()['name'];
          PushyAPI::send_message("user", $user['pushy_token'], 5, 1, 'Pesan baru', $shortMessage, array(
              'data' => json_encode($messageInfo)
          ));
          $row = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
          $row['admin_name'] = $this->db->get_where('admins', array(
            'id' => $adminID
          ))->row_array()['name'];
          echo json_encode($row);
        }
  }
  
  public function get_chats() {
    $adminID = intval($this->input->post('admin_id'));
    $chats = [];
    $results = $this->db->query("SELECT * FROM `messages` WHERE `admin_id`=" . $adminID . " AND `type`=1")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $chat = $results[$i];
      $userInfo = $this->db->get_where('users', array(
        'id' => intval($chat['user_id'])
      ))->row_array();
      $chat['profile_picture'] = $userInfo['profile_picture'];
      $chat['name'] = $userInfo['name'];
      array_push($chats, $chat);
    }
    $results = $this->db->query("SELECT * FROM `messages` WHERE `admin_id`=" . $adminID . " AND `type`!=1 ORDER BY `date` DESC LIMIT 1")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $chat = $results[$i];
      $userInfo = $this->db->get_where('users', array(
        'id' => intval($chat['user_id'])
      ))->row_array();
      $chat['profile_picture'] = $userInfo['profile_picture'];
      $chat['name'] = $userInfo['name'];
      array_push($chats, $chat);
    }
    echo json_encode($chats);
  }
  
  public function edit_article() {
    $articleID = intval($this->input->post('article_id'));
    $title = $this->input->post('title');
    $content = $this->input->post('content');
    $imageChanged = intval($this->input->post('image_changed'));
    if ($imageChanged == 1) {
      $config = array(
        'upload_path' => './userdata',
        //'allowed_types' => "mp4|avi|ogg|flv|wmv|3gp",
        'allowed_types' => "*",
        'overwrite' => TRUE,
        'max_size' => "2097152"
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
    if($this->upload->do_upload('file')) {
      $this->db->where('id', $articleID);
      $this->db->update('articles', array(
        'title' => $title,
        'content' => $content,
        'img' => $this->upload->data()['file_name']
      ));
    } else {
      echo json_encode($this->upload->display_errors());
    }
    } else {
      $this->db->where('id', $articleID);
      $this->db->update('articles', array(
        'title' => $title,
        'content' => $content
      ));
    }
  }
  
  public function add_article() {
    $title = $this->input->post('title');
    $content = $this->input->post('content');
    $config = array(
        'upload_path' => './userdata',
        //'allowed_types' => "mp4|avi|ogg|flv|wmv|3gp",
        'allowed_types' => "*",
        'overwrite' => TRUE,
        'max_size' => "2097152"
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
    if($this->upload->do_upload('file')) {
      $this->db->insert('articles', array(
        'title' => $title,
        'content' => $content,
        'img' => $this->upload->data()['file_name']
      ));
    } else {
      echo json_encode($this->upload->display_errors());
    }
  }
}
