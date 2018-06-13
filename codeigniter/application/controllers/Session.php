<?php
class Session extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('SessionModel');
    $this->load->model('UserModel');

  }

  public function index()
  {
    if ($this->input->method() === 'post') {
      $this->create();
    } else {
      show_404();
    }
  }

  private function create()
  {
    $body = json_decode($this->security->xss_clean($this->input->raw_input_stream));
    if (!isset($body->userName) || !isset($body->password)) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(401)
        ->set_output(json_encode(array(
          'success' => false,
          'msg' => "username or password is not given")));
    }
    $userName = $body->userName;
    $password = $body->password;

    $res = $this->SessionModel->createSession($userName, $password);

    switch ($res) {
      case 'ok':
        $code = 200;
        $userInfo = $this->UserModel->getUserInfo($userName);
        $response = array('success' => true, 'msg' => $res, 'data' => $userInfo);
        break;
      case 'not exist':
        $code = 404;
        $response = array('success' => false, 'msg' => $res);
        break;
      case 'password incorrect':
        $code = 401;
        $response = array('success' => false, 'msg' => $res);
        break;
      default:
        throw new Exception("unknown createSession result: {$res}");
        break;
    }

    return $this->output
      ->set_content_type('application/json')
      ->set_status_header($code)
      ->set_output(json_encode($response));
  }

  // private function deactivate()
  // {
  //   if ($this->input->method() !== 'post') {
  //     show_404();
  //   }
  // }
}
