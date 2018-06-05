<?php
class Users extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('UserModel');
  }

  public function register()
  {
    if ($this->input->method() !== 'post') {
      return $this->output
        ->set_status_header(404);
    }

    // https://stackoverflow.com/a/37570103
    $body = json_decode($this->security->xss_clean($this->input->raw_input_stream));

    $userName = $body->userName;
    $password = $body->password;
    $this->UserModel->addUser($userName, $password);

    // https://stackoverflow.com/questions/18821492/code-igniter-how-to-return-json-response-from-controller
    return $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array(
        'status' => 'ok',
        'userName' => $userName,
        // 'password' => $password,
      )));
  }

}
