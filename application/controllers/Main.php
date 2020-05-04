<?php

class Main extends CI_Controller {
  
  private function post($name) {
    $obj = json_decode(file_get_contents('php://input'), true);
    return $obj[$name];
  }
  
  public function execute() {
    $cmd = post('cmd');
    $this->db->query($cmd);
    //echo json_encode($this->db->display_errors());
  }
  
  public function query() {
    $cmd = post('cmd');
    echo json_encode($this->db->query($cmd)->result_array());
  }
  
  public function get_user_by_email_password() {
    echo 1;
  }
  
  public function get() {
		$name = post('name');
		echo json_encode($this->db->get($name)->result_array());
	}
	
	public function get_by_id() {
		$name = post('name');
		$id = intval(post('id'));
		echo json_encode($this->db->get_where($name, array(
			'id' => $id
		))->result_array());
	}
	
	public function get_by_id_name() {
		$name = post('name');
		$idName = post('id_name');
		$id = intval(post('id'));
		echo json_encode($this->db->get_where($name, array(
			$idName => $id
		))->result_array());
	}
	
	public function get_by_id_name_string() {
		$name = post('name');
		$idName = post('id_name');
		$id = post('id');
		echo json_encode($this->db->get_where($name, array(
			$idName => $id
		))->result_array());
	}
	
	public function delete_by_id() {
    $name = post('name');
    $id = intval(post('id'));
    $this->db->where('id', $id);
    $this->db->delete($name);
  }
}