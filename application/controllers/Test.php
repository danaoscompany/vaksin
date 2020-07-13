<?php

class Test extends CI_Controller {
	
	public function email() {
		$this->load->library('email');
		$this->email
    		->from('admin@rumahvaksin.com', 'RumahVaksin')
    		->to('danaoscompany@gmail.com')
    		->subject('Hello from Example Inc.')
    		->message('Hello, We are <strong>Example Inc.</strong>')
    		->set_mailtype('html');
		$this->email->send();
	}
}
