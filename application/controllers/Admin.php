<?php

class Admin extends CI_Controller {
  
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