<?php
class Users extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('UserModel');

    // this controller only handles POST request
    if ($this->input->method() !== 'post') {
      show_404();
    }
  }

  public function register()
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

    $res = $this->UserModel->addUser($userName, $password);
    $code = $res['code'];
    unset($res['code']);

    return $this->output
      ->set_content_type('application/json')
      ->set_status_header($code)
      ->set_output(json_encode($res));
  }

}
