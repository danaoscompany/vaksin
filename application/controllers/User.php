<?php

class User extends CI_Controller {
  
  private function post($name) {
    $obj = json_decode(file_get_contents('php://input'), true);
    return $obj[$name];
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
  
  public function test() {
    load_email_config();
$this->email->from('admin@adityap.my.id', 'DanaOS');
$this->email->to('danaoscompany@gmail.com'); 
$this->email->subject('Email Test');
$this->email->message('Testing the <b>email</b> class.');  

$this->email->send();

echo $this->email->print_debugger();
  }
  
  public function send_verification_email() {
    $email = $this->input->post('email');
    $this->load_email_config();
$this->email->from('admin@adityap.my.id', 'Probis Vaksin');
$this->email->to($email); 
$this->email->subject('Verifikasikan email Anda');
$this->email->message('Mohon verifikasi email Anda dengan memasukkan kode 6 digit berikut: <b>' . $code . '</b>');  

$this->email->send();
      echo "Email: " . $email . ", ";
      echo $this->email->print_debugger();
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
    $userID = intval(post('user_id'));
    $name = $this->input->post('name');
    $address = $this->input->post('address');
    $age = intval(post('age'));
    $parentName = $this->input->post('parent_name');
    $phone = $this->input->post('phone');
    $lastVaccineDate = $this->input->post('last_vaccine_date');
    $lastVaccineID = intval(post('last_vaccine_id'));
    $registrationDate = $this->input->post('registration_date');
    $this->db->where('id', $userID);
    $lastNoAnggota = intval($this->db->query('SELECT * FROM `users` ORDER BY `no_anggota` DESC LIMIT 1')->row_array()['no_anggota'])+1;
    $noAnggota = str_pad('' . $lastNoAnggota, 4, '0', STR_PAD_LEFT);
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
    $userID = intval(post('user_id'));
    $slotID = intval(post('slot_id'));
    $vaccines = $this->input->post('vaccines');
    $usedVaccines = $this->db->get_where('used_vaccines', array(
      'user_id' => $userID,
      'slot_id' => $slotID
    ))->result_array();
    if (sizeof($usedVaccines) > 0) {
      echo -1;
    } else {
      $this->db->insert('used_vaccines', array(
        'user_id' => $userID,
        'slot_id' => $slotID,
        'vaccines' => $vaccines
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
