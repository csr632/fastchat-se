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
      $this->createSession();
    } else {
      show_404();
    }
  }

  private function createSession()
  {
    $body = json_body();
    if (!isset($body->userName) || !isset($body->password)) {
			return json_response(401, false, 'username or password is not given');
    }
    $userName = $body->userName;
    $password = $body->password;

    $res = $this->SessionModel->createSession($userName, $password);

    switch ($res) {
      case 'ok':
        $userInfo = $this->UserModel->getUserInfo($userName);
        $jwt = generateJWT($userName);
				return json_response(200, true, $res, array('jwt' => $jwt, 'userInfo' => $userInfo));
        break;
      case 'not exist':
				return json_response(404, false, $res);
        break;
      case 'password incorrect':
				return json_response(401, false, $res);
        break;
      default:
        throw new Exception("unknown createSession result: {$res}");
        break;
    }
  }

  // private function deactivate()
  // {
  //   if ($this->input->method() !== 'post') {
  //     show_404();
  //   }
  // }
}
