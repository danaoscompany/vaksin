<?php

require('Message.php');

class User extends CI_Controller {
  
  private function post($name) {
    $obj = json_decode(file_get_contents('php://input'), true);
    return $obj[$name];
  }
  
  public function purchase() {
    $userID = intval($this->input->post('user_id'));
    $amount = intval($this->input->post('amount'));
    $this->db->where('id', $userID);
    $this->db->set('balance', 'balance-' . $amount, false);
    $this->db->update('users');
  }
  
  public function confirm_payment_success() {
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
      PushyAPI::send_message($pushyToken, 2, 1, 'Pembayaran berhasil', "Pembayaran Anda sebesar" . $amount . " telah berhasil", array(
        'data' => json_encode($obj)
      ));
      $this->db->where('external_id', $externalID);
      $this->db->update('payments', array(
        'status' => 'PAID'
      ));
      $this->db->where('id', intval($user['id']));
      $this->db->set('balance', 'balance+' . $amount, FALSE);
      $this->db->update('users');
    }
    echo "OK";
  }
  
  public function find_online_admins() {
    $userID = intval($this->input->post('user_id'));
    $name = $this->db->get_where('users', array(
      'id' => $userID
    ))->row_array()['name'];
    $admins = $this->db->query("SELECT * FROM `admins` WHERE `chatting_with_user`=0");
    for ($i=0; $i<sizeof($admins); $i++) {
      $admin = $admins[$i];
      $pushyToken = $admin['pushy_token'];
      PushyAPI::send_message($pushyToken, 1, 1, 'Pesan baru', "Anda mendapat 1 permintaan pesan baru dari " . $name, array(
          ));
    }
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
$this->email->from('admin@adityap.my.id', 'Probis Vaksin');
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
    $age = intval($this->input->post('age'));
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
      'age' => $age,
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
  
  public function use_vaccine() {
    $userID = intval($this->input->post('user_id'));
    $slotID = intval($this->input->post('slot_id'));
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
