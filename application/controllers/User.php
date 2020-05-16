<?php

require('Message.php');

class User extends CI_Controller {
  
  public function email_test() {
    $email = 'danaoscompany@gmail.com';
    $this->load_email_config();
    $this->email->from('admin@adityap.my.id', 'Vaksin Cikarang');
    $this->email->to($email); 
    $this->email->subject('Tes Email');
    $this->email->message('Ini adalah <b>email</b>, <i>email</i>.');  
    $this->email->send();
  }
  
  public function remind_vaccine() {
    date_default_timezone_set('Asia/Jakarta');
    $currentDate = date('Y:m:d H:i:s');
    $users = $this->db->get('users');
    for ($i=0; $i<sizeof($users); $i++) {
      $user = $users[$i];
      $birthDate = $user['birthday'];
      $diff = $currentDate->diff($birthDate);
      $timelines = $this->db->get_where('timeline', array(
        'month' => intval($diff->m)
      ))->result_array();
      if (sizeof($timelines) > 0) {
        $timeline = $timelines[0];
        $scheduleSent = $this->db->get_where('schedule_sent', array(
          'timeline_id' => intval($timeline['id'])
        ))->result_array();
        if (sizeof($scheduleSent) == 0) {
          
        }
      }
    }
  }
  
  public function add_ad() {
    $config = array(
        'upload_path' => './userdata',
        'allowed_types' => "gif|jpg|png|jpeg",
        'overwrite' => TRUE,
        'max_size' => "2048000"
        );
        $this->load->library('upload', $config);
        if($this->upload->do_upload('file')) { 
          $this->db->insert('ads', array(
            'img' => $this->upload->data()['file_name']
          ));
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
      'message' => $message,
      'sender' => 'user',
      'date' => $date
    ));
    $lastID = intval($this->db->insert_id());
    $admin = $this->db->get_where('admins', array(
        'id' => $adminID
      ))->row_array();
    $messageInfo = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
    $messageInfo['user_name'] = $this->db->get_where('users', array(
            'id' => $userID
          ))->row_array()['name'];
    PushyAPI::send_message("admin", $admin['pushy_token'], 5, 1, 'Pesan baru', $shortMessage, array(
        'data' => json_encode($messageInfo)
        ,
        'user_id' => $userID,
        'message_id' => $lastID
      ));
    $row = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
    $row['user_name'] = $this->db->get_where('users', array(
      'id' => $userID
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
            'sender' => 'user',
            'image' => $this->upload->data()['file_name'],
            'date' => $date
          ));
          $lastID = intval($this->db->insert_id());
          $admin = $this->db->get_where('admins', array(
              'id' => $adminID
            ))->row_array();
          $messageInfo = $this->db->get_where('messages', array(
            'id' => $lastID
          ))->row_array();
          $messageInfo['user_name'] = $this->db->get_where('users', array(
            'id' => $userID
          ))->row_array()['name'];
          PushyAPI::send_message("admin", $admin['pushy_token'], 3, 1, 'Pesan baru', $shortMessage);
          $row = $this->db->get_where('messages', array(
            'data' => json_encode($messageInfo)
          ))->row_array();
          $row['user_name'] = $this->db->get_where('users', array(
            'id' => $userID
          ))->row_array()['name'];
          echo json_encode($row);
        }
  }
  
  public function test_notification() {
    $token = "e63c693662a264646c1591";
    PushyAPI::send_message("admin", $token, 2, 1, 'Pembayaran berhasil', "Pembayaran Anda sebesar 10000 telah berhasil", array(
      ));
  }
  
  private function post($name) {
    $obj = json_decode(file_get_contents('php://input'), true);
    return $obj[$name];
  }
  
  public function get_messages() {
    $start = intval($this->input->post('start'));
    $length = intval($this->input->post('length'));
    $this->db->limit($length, $start);
    $this->db->order_by('date', 'DESC');
    $messages = $this->db->get('messages')->result_array();
    for ($i=0; $i<sizeof($messages); $i++) {
      $messages[$i]['user_name'] = $this->db->get_where('users', array(
        'id' => intval($messages[$i]['user_id'])
      ))->row_array()['name'];
      $messages[$i]['admin_name'] = $this->db->get_where('admins', array(
        'id' => intval($messages[$i]['admin_id'])
      ))->row_array()['name'];
    }
    echo json_encode($messages);
  }
  
  public function get_histories() {
    $userID = intval($this->input->post('user_id'));
    $histories = $this->db->get_where('history', array(
      'user_id' => $userID
    ))->result_array();
    for ($i=0; $i<sizeof($histories); $i++) {
      $history = $histories[$i];
      $slot = $this->db->get_where('slots', array(
        'id' => intval($history['slot_id'])
      ))->row_array();
      $usedVaccine = $this->db->get_where('used_vaccines', array(
        'user_id' => $userID,
        'slot_id' => intval($history['slot_id'])
      ))->row_array();
      $histories[$i]['vaccines'] = $usedVaccine['vaccines'];
      $histories[$i]['start_date'] = $slot['start_date'];
      $histories[$i]['end_date'] = $slot['end_date'];
    }
    echo json_encode($histories);
  }
  
  public function add_history() {
    $userID = intval($this->input->post('user_id'));
    $slotID = intval($this->input->post('slot_id'));
    $date = $this->input->post('date');
    $results = $this->db->get_where('history', array(
      'user_id' => $userID,
      'slot_id' => $slotID
    ))->result_array();
    if (sizeof($results) == 0) {
      $this->db->insert('history', array(
        'user_id' => $userID,
        'slot_id' => $slotID,
        'date' => $date
      ));
    }
  }
  
  public function purchase() {
    $userID = intval($this->input->post('user_id'));
    $amount = intval($this->input->post('amount'));
    $this->db->where('id', $userID);
    $this->db->set('balance', 'balance-' . $amount, false);
    $this->db->update('users');
  }
  
  public function cancel_slot() {
    $slotID = intval($this->input->post('slot_id'));
    $slot = $this->db->get_where('used_vaccines', array(
      'id' => $slotID
    ))->row_array();
    $userID = intval($slot['user_id']);
    $paid = intval($slot['paid']);
    $price = intval($slot['price']);
    if ($paid == 1) {
      $this->db->where('id', $userID);
      $this->db->set('balance', 'balance + ' . $price, FALSE);
      $this->db->update('users');
    }
    $this->db->where('id', $slotID);
    $this->db->delete('used_vaccines');
    $this->db->where('id', intval($slot['slot_id']));
    $this->db->set('slots_used', 'slots_used-1', FALSE);
    $this->db->update('slots');
  }
  
  public function update_payment_status() {
    $obj = json_decode(file_get_contents('php://input'), true);
    $externalID = $obj['external_id'];
    $payment = $this->db->get_where('payments', array(
      'external_id' => $externalID
    ))->row_array();
    $amount = intval($payment['amount']);
    $status = $obj['status'];
    if ($status == 'PAID') {
      $user = $this->db->get_where('users', array(
          'id' => intval($payment['user_id'])
      ))->row_array();
      $pushyToken = $user['pushy_token'];
      PushyAPI::send_message("admin", $pushyToken, 2, 1, 'Pembayaran berhasil', "Pembayaran Anda sebesar" . $amount . " telah berhasil", array(
        'data' => json_encode($obj)
      ));
      $this->db->where('external_id', $externalID);
      $this->db->update('payments', array(
        'status' => 'PAID'
      ));
      $this->db->where('id', intval($user['id']));
      $this->db->set('balance', 'balance+' . $amount, FALSE);
      $this->db->update('users');
    } else if ($status == 'FAILED') {
      $user = $this->db->get_where('users', array(
          'id' => intval($payment['user_id'])
      ))->row_array();
      $pushyToken = $user['pushy_token'];
      PushyAPI::send_message("admin", $pushyToken, 2, 1, 'Pembayaran gagal', "Pembayaran Anda sebesar" . $amount . " gagal", array(
        'data' => json_encode($obj)
      ));
      $this->db->where('external_id', $externalID);
      $this->db->update('payments', array(
        'status' => 'FAILED'
      ));
    }
    echo "OK";
  }
  
  public function update_withdraw_status() {
    $obj = json_decode(file_get_contents('php://input'), true);
    $externalID = $obj['external_id'];
    $this->db->where('external_id', $externalID);
    $this->db->update('withdraws', array(
        'status_payload' => json_encode($obj)
      ));
    $payment = $this->db->get_where('withdraws', array(
      'external_id' => $externalID
    ))->row_array();
    $amount = intval($payment['amount']);
    $status = $obj['status'];
    if ($status == 'PAID' || $status == 'SUCCESS') {
      $user = $this->db->get_where('users', array(
          'id' => intval($payment['user_id'])
      ))->row_array();
      $pushyToken = $user['pushy_token'];
      PushyAPI::send_message("admin", $pushyToken, 2, 1, 'Penarikan berhasil', "Penarikan Anda sebesar" . $amount . " telah berhasil", array(
        'data' => json_encode($obj)
      ));
      $this->db->where('external_id', $externalID);
      $this->db->update('payments', array(
        'status' => 'PAID'
      ));
      $this->db->where('id', intval($user['id']));
      $this->db->set('balance', 'balance-' . $amount, FALSE);
      $this->db->update('users');
    } else if ($status == 'FAILED') {
      $user = $this->db->get_where('users', array(
          'id' => intval($payment['user_id'])
      ))->row_array();
      $pushyToken = $user['pushy_token'];
      PushyAPI::send_message("admin", $pushyToken, 2, 1, 'Penarikan gagal', "Penarikan Anda sebesar" . $amount . " gagal", array(
        'data' => json_encode($obj)
      ));
      $this->db->where('external_id', $externalID);
      $this->db->update('payments', array(
        'status' => 'FAILED'
      ));
    }
    echo "OK";
  }
  
  public function find_online_admins() {
    $userID = intval($this->input->post('user_id'));
    $name = $this->db->get_where('users', array(
      'id' => $userID
    ))->row_array()['name'];
    $admins = $this->db->query("SELECT * FROM `admins`")->result_array();
    for ($i=0; $i<sizeof($admins); $i++) {
      $admin = $admins[$i];
      $pushyToken = $admin['pushy_token'];
      PushyAPI::send_message("admin", $pushyToken, 1, 1, 'Pesan baru', "Anda mendapat 1 permintaan pesan baru dari " . $name, array(
          ));
      $adminID = intval($admin['id']);
      echo "Sending message to " . $adminID . " with Pushy token " . $pushyToken . "\n";
      $this->db->where('admin_id', $adminID)->where('user_id', $userID)->where('type', 1);
      $this->db->delete('messages');
      $this->db->insert('messages', array(
        'admin_id' => $adminID,
        'type' => 1,
        'user_id' => $userID
      ));
    }
  }
  
  public function get_vaccines() {
    $vaccines = $this->db->query('SELECT * FROM `vaccines`')->result_array();
    for ($i=0; $i<sizeof($vaccines); $i++) {
      $vaccines[$i]['vaccine_name'] = $this->db->get_where('vaccine_names', array(
        'id' => intval($vaccines[$i]['vaccine_name_id'])
      ))->row_array()['name'];
      $vaccines[$i]['vaccine_type'] = $this->db->get_where('vaccine_types', array(
        'id' => intval($vaccines[$i]['vaccine_type_id'])
      ))->row_array()['name'];
    }
    echo json_encode($vaccines);
  }
  
  public function get_slot() {
    $userID = intval($this->input->post('user_id'));
    $slotID = intval($this->input->post('slot_id'));
    echo json_encode($this->db->get_where('used_vaccines', array(
      'user_id' => $userID,
      'slot_id' => $slotID
    ))->row_array());
  }
  
  public function get_added_vaccines() {
    $userID = intval($this->input->post('user_id'));
    $results = $this->db->query("SELECT * FROM `used_vaccines` WHERE `user_id`=" . $userID . "")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $row = $results[$i];
      $slot = $this->db->get_where('slots', array(
        'id' => intval($row['slot_id'])
      ))->row_array();
      $results[$i]['start_date'] = $slot['start_date'];
      $results[$i]['end_date'] = $slot['end_date'];
      $results[$i]['slots'] = $slot['slots'];
      $results[$i]['slots_used'] = $slot['slots_used'];
      /*$vaccineNameID = intval($this->db->get_where('vaccines', array(
        'id' => intval($slot['vaccine_id'])
        ))->row_array()['vaccine_name_id']);
      $results[$i]['vaccine_name'] = $this->db->get_where('vaccine_names', array(
        'id' => $vaccineNameID
      ))->row_array()['name'];
      $vaccineTypeID = intval($this->db->get_where('vaccines', array(
        'id' => intval($slot['vaccine_id'])
        ))->row_array()['vaccine_type_id']);
      $results[$i]['vaccine_type'] = $this->db->get_where('vaccine_types', array(
        'id' => $vaccineNameID
      ))->row_array()['name'];*/
    }
    echo json_encode($results);
  }
  
  public function get_active_vaccines() {
    $userID = intval($this->input->post('user_id'));
    $results = $this->db->query("SELECT * FROM `used_vaccines` WHERE `user_id`=" . $userID . " AND `active`=1")->result_array();
    for ($i=0; $i<sizeof($results); $i++) {
      $row = $results[$i];
      $slot = $this->db->get_where('slots', array(
        'id' => intval($row['slot_id'])
      ))->row_array();
      $results[$i]['start_date'] = $slot['start_date'];
      $results[$i]['end_date'] = $slot['end_date'];
      $results[$i]['slots'] = $slot['slots'];
      $results[$i]['slots_used'] = $slot['slots_used'];
      /*$vaccineNameID = intval($this->db->get_where('vaccines', array(
        'id' => intval($slot['vaccine_id'])
        ))->row_array()['vaccine_name_id']);
      $results[$i]['vaccine_name'] = $this->db->get_where('vaccine_names', array(
        'id' => $vaccineNameID
      ))->row_array()['name'];
      $vaccineTypeID = intval($this->db->get_where('vaccines', array(
        'id' => intval($slot['vaccine_id'])
        ))->row_array()['vaccine_type_id']);
      $results[$i]['vaccine_type'] = $this->db->get_where('vaccine_types', array(
        'id' => $vaccineNameID
      ))->row_array()['name'];*/
    }
    echo json_encode($results);
  }
  
  public function load_email_config() {
    $this->load->library('email');
$config['protocol']    = 'smtp';
$config['smtp_host']    = 'adityap.my.id';
$config['smtp_port']    = '587';
$config['smtp_timeout'] = '7';
$config['smtp_user']    = 'admin@adityap.my.id';
$config['smtp_pass']    = 'HelloWorld@123';
$config['charset']    = 'utf-8';
$config['newline']    = "\r\n";
$config['mailtype'] = 'html'; // or html
$config['validation'] = TRUE; // bool whether to validate email or not      

$this->email->initialize($config);
  }
  
  public function reset_password() {
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $this->db->where('email', $email);
    $this->db->update('users', array(
      'password' => $password
    ));
  }
  
  public function test() {
    $this->load_email_config();
$this->email->from('admin@adityap.my.id', 'Probis Vaksin');
$this->email->to('danaoscompany@gmail.com'); 
$this->email->subject('Email Test');
$this->email->message('Testing the <b>email</b> class.');  

$this->email->send();

echo $this->email->print_debugger();
  }
  
  public function send_verification_email() {
    $title = $this->input->post('title');
    $code = $this->randomNumber(6);
    $email = $this->input->post('email');
    $this->load_email_config();
    $this->email->from('admin@adityap.my.id', 'Vaksin Cikarang');
    $this->email->to($email); 
    $this->email->subject($title);
    $this->email->message('Mohon verifikasi email Anda dengan memasukkan kode 6 digit berikut: <b>' . $code . '</b>');  
    $this->email->send();
      echo json_encode(array(
        'verification_code' => $code
      ));
  }
  
  public function sign_in_with_google() {
    $phone = $this->input->post('phone');
    $password = $this->input->post('password');
    $uid = $this->input->post('uid');
    $users = $this->db->get_where('users', array(
      'google_uid' => $uid
    ))->result_array();
    if (sizeof($users) == 0) {
      $this->db->insert('users', array(
        'phone' => $phone,
        'password' => $password,
        'google_uid' => $uid
      ));
      echo json_encode(array(
        'response_code' => 1,
        'user_id' => intval($this->db->last_insert_id())
      ));
    } else if (sizeof($users) > 0) {
      $user = $users[0];
      if ($user['password'] != $password) {
        echo json_encode(array(
          'response_code' => -1
        ));
      } else {
        echo json_encode(array(
          'response_code' => 1,
          'user_id' => intval($this->db->last_insert_id())
        ));
      }
    }
  }
  
  public function complete_data() {
    $userID = intval($this->input->post('user_id'));
    $name = $this->input->post('name');
    $address = $this->input->post('address');
    $birthday = $this->input->post('birthday');
    $parentName = $this->input->post('parent_name');
    $phone = $this->input->post('phone');
    $lastVaccineDate = $this->input->post('last_vaccine_date');
    $lastVaccineID = intval($this->input->post('last_vaccine_id'));
    $registrationDate = $this->input->post('registration_date');
    $lastNoAnggota = 1;
    $lastUsers = $this->db->query('SELECT * FROM `users` ORDER BY `no_anggota` DESC LIMIT 1')->result_array();
    if (sizeof($lastUsers) > 0) {
      $lastNoAnggota = intval($lastUsers[0]['no_anggota'])+1;
    }
    $noAnggota = str_pad('' . $lastNoAnggota, 4, '0', STR_PAD_LEFT);
    $this->db->where('id', $userID);
    $this->db->update('users', array(
      'no_anggota' => $noAnggota,
      'name' => $name,
      'address' => $address,
      'birthday' => $birthday,
      'parent_name' => $parentName,
      'phone' => $phone,
      'last_vaccine_date' => $lastVaccineDate,
      'last_vaccine_id' => $lastVaccineID,
      'registration_date' => $registrationDate,
      'registration_complete' => 1
    ));
    echo json_encode(array(
      'no_anggota' => $noAnggota
    ));
  }
  
  public function edit_profile() {
    $userID = intval($this->input->post('user_id'));
    $name = $this->input->post('name');
    $address = $this->input->post('address');
    $age = intval($this->input->post('age'));
    $email = $this->input->post('email');
    $phone = $this->input->post('phone');
    $emailChanged = intval($this->input->post('email_changed'));
    $registrationDate = $this->input->post('registration_date');
    $passwordChanged = intval($this->input->post('password_changed'));
    if ($emailChanged == 1) {
      if ($this->db->get_where('users', array(
        'email' => $email
      ))->num_rows() > 0) {
        echo -1;
        return;
      }
      $this->db->where('id', $userID);
      $this->db->update('users', array(
        'email' => $email
      ));
    }
    if ($passwordChanged == 1) {
      $password = $this->input->post('password');
      $this->db->where('id', $userID);
      $this->db->update('users', array(
        'password' => $password
      ));
    }
    $profilePictureChanged = intval($this->input->post('profile_picture_changed'));
    if ($profilePictureChanged == 1) {
      $config['upload_path'] = './userdata/';
      $config['allowed_types'] = '*';
      $config['max_size'] = '2048000';
      $config['max_width'] = '5000';
      $config['max_height'] = '5000';
      $this->load->library('upload', $config);
      if ($this->upload->do_upload('file')) {
        $this->db->where('id', $userID);
        $this->db->update('users', array(
          'name' => $name,
          'address' => $address,
          'age' => $age,
          'email' => $email,
          'phone' => $phone,
          'profile_picture' => $this->upload->data()['file_name'],
          'registration_date' => $registrationDate,
          'registration_complete' => 1
        ));
      }
    } else {
      $this->db->where('id', $userID);
      $this->db->update('users', array(
      'name' => $name,
      'address' => $address,
      'age' => $age,
      'email' => $email,
      'phone' => $phone,
      'registration_date' => $registrationDate,
      'registration_complete' => 1
    ));
    }
    echo 1;
  }
  
  public function use_vaccine() {
    $userID = intval($this->input->post('user_id'));
    $slotID = intval($this->input->post('slot_id'));
    $noAnggota = 1;
    $usedVaccines = $this->db->query("SELECT * FROM `used_vaccines` WHERE `slot_id`=" . $slotID . " ORDER BY `no_anggota` DESC LIMIT 1")->result_array();
    if (sizeof($usedVaccines) > 0) {
      $noAnggota = $usedVaccines[0]['no_anggota'];
      $noAnggota = intval($noAnggota)+1;
    }
    $noAnggota = str_pad($noAnggota, 4, '0', STR_PAD_LEFT);
    $vaccines = $this->input->post('vaccines');
    $price = intval($this->input->post('price'));
    $paymentMethod = intval($this->input->post('payment_method'));
    $paid = intval($this->input->post('paid'));
    $usedVaccines = $this->db->get_where('used_vaccines', array(
      'user_id' => $userID,
      'slot_id' => $slotID
    ))->result_array();
    if (sizeof($usedVaccines) > 0) {
      $this->db->where('user_id', $userID)->where('slot_id', $slotID);
      $this->db->update('used_vaccines', array(
        'user_id' => $userID,
        'slot_id' => $slotID,
        'vaccines' => $vaccines,
        'price' => $price,
        'payment_method' => $paymentMethod,
        'paid' => $paid
      ));
      echo 1;
    } else {
      //$this->db->where('user_id', $userID)->where('slot_id', $slotID);
      //$this->db->delete('used_vaccines');
      $this->db->insert('used_vaccines', array(
        'user_id' => $userID,
        'slot_id' => $slotID,
        'no_anggota' => $noAnggota,
        'vaccines' => $vaccines,
        'price' => $price,
        'payment_method' => $paymentMethod,
        'paid' => $paid
      ));
      $this->db->where('id', $slotID);
      $this->db->set('slots_used', 'slots_used+1', FALSE);
      $this->db->update('slots');
      echo 1;
    }
  }
  
  public function signup() {
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $users = $this->db->get_where('users', array(
      'email' => $email
    ))->result_array();
    if (sizeof($users) > 0) {
      echo json_encode(array(
        'response_code' => -1
      ));
    } else {
      $code = $this->randomNumber(6);
      $this->db->insert('users', array(
        'email' => $email,
        'password' => $password
      ));
      $lastID = intval($this->db->insert_id());
      $user = $this->db->get_where('users', array(
        'id' => $lastID
      ))->row_array();
      echo json_encode(array(
        'response_code' => 1,
        'data' => $user,
        'verification_code' => $code
      ));
    }
  }
  
  private function randomNumber($length) {
    $result = '';

    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}
  
  public function login_with_email() {
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $users = $this->db->get_where('users', array(
      'email' => $email
    ))->result_array();
    if (sizeof($users) > 0) {
      $user = $users[0];
      if ($user['password'] == $password) {
        echo json_encode(array(
          'response_code' => 1,
          'user_id' => intval($user['id']),
          'registration_complete' => intval($user['registration_complete'])
        ));
      } else {
        echo json_encode(array(
          'response_code' => -1
        ));
      }
    } else {
      echo json_encode(array(
        'response_code' => -2
      ));
    }
  }
  
  public function login_with_phone() {
    $phone = $this->input->post('phone');
    $password = $this->input->post('password');
    $users = $this->db->get_where('users', array(
      'phone' => $phone
    ))->result_array();
    if (sizeof($users) > 0) {
      $user = $users[0];
      if ($user['password'] == $password) {
        echo json_encode(array(
          'response_code' => 1,
          'user_id' => intval($user['id']),
          'registration_complete' => intval($user['registration_complete'])
        ));
      } else {
        echo json_encode(array(
          'response_code' => -1
        ));
      }
    } else {
      echo json_encode(array(
        'response_code' => -2
      ));
    }
  }
  
  public function query() {
    $cmd = $this->input->post('cmd');
    echo json_encode($this->db->query($cmd)->result_array());
  }
}
