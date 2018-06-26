<?php
class Users extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('UserModel');
  }

  private function register()
  {
    $body = json_body();
    if (!isset($body->userName)
      || !isset($body->password)
      || !isset($body->email)
      || !isset($body->nickname)
      || !isset($body->gender)
    ) {
      return json_response(400, false, 'info incomplete');
    }
    $userName = $body->userName;
    $password = $body->password;
    $email = $body->email;
    $nickname = $body->nickname;
    $gender = $body->gender;

    $res = $this->UserModel->addUser($userName, $password, $email, $nickname, $gender);
    if ($res["result"] === 'ok') {
      return json_response(200, true, 'ok');
    } else if ($res["result"] === 'exists') {
      return json_response(409, false, 'some fields exists', $res);
    }
  }

  public function findUsers()
  {
    $contain = $this->input->get('contain', true);
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $res = $this->UserModel->findUsers($contain, $userName);
    return json_response(200, true, 'ok', $res);
  }

}
