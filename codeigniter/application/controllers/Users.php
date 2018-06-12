<?php
class Users extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('UserModel');
  }

  public function index()
  {
    if ($this->input->method() === 'post') {
      $this->register();
    } else {
      show_404();
    }
  }

  private function register()
  {
    // https://stackoverflow.com/a/37570103
    $body = json_decode($this->security->xss_clean($this->input->raw_input_stream));
    if (!isset($body->userName) || !isset($body->password)) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(array(
          'success' => false,
          'msg' => "username or password is not given")));
    }
    $userName = $body->userName;
    $password = $body->password;
    $email = $body->email;

    $res = $this->UserModel->addUser($userName, $password, $email);
    if ($res["result"] === 'ok') {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode($res));
    } else if ($res["result"] === 'exists') {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode($res));
    }
  }

}
