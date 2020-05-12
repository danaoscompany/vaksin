<?php

require('Message.php');

class Admin extends CI_Controller {
  
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
      'message' => $message,
      'date' => $date
    ));
    $lastID = intval($this->db->insert_id());
    $user = $this->db->get_where('users', array(
        'id' => $userID
      ))->row_array();
    
    PushyAPI::send_message("user", $user['pushy_token'], 3, 1, 'Pesan baru', $shortMessage, array(
        'message_id' => "" . $lastID
      ));
    $row = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
    $row['name'] = $this->db->get_where('users', array(
      'id' => $senderID
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
            'image' => $this->upload->data()['file_name'],
            'date' => $date
          ));
          $lastID = intval($this->db->insert_id());
          $user = $this->db->get_where('users', array(
              'id' => $userID
            ))->row_array();
          PushyAPI::send_message("user", $user['pushy_token'], 3, 1, 'Pesan baru', $shortMessage);
          $row = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
          $row['name'] = $this->db->get_where('users', array(
            'id' => $senderID
          ))->row_array()['name'];
          echo json_encode($row);
        }
  }
  
  public function get_chats() {
    $adminID = intval($this->input->post('admin_id'));
    $chats = [];
    $results = $this->db->query("SELECT * FROM `messages` WHERE `admin_id`=" . $adminID . " AND `type`=1")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $row = $results[$i];
      $chat = $this->db->query("SELECT * FROM `messages` WHERE `user_id`=" . intval($row['user_id']) . " AND `admin_id`=" . intval($row['admin_id']))->row_array();
      $userInfo = $this->db->get_where('users', array(
        'id' => intval($row['user_id'])
      ))->row_array();
      $chat['profile_picture'] = $userInfo['profile_picture'];
      $chat['name'] = $userInfo['name'];
      array_push($chats, $chat);
    }
    $results = $this->db->query("SELECT * FROM `messages` WHERE `admin_id`=" . $adminID . " AND `type`!=1")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $row = $results[$i];
      $chat = $this->db->query("SELECT * FROM `messages` WHERE `user_id`=" . intval($row['user_id']) . " AND `admin_id`=" . intval($row['admin_id']))->row_array();
      $userInfo = $this->db->get_where('users', array(
        'id' => intval($row['user_id'])
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