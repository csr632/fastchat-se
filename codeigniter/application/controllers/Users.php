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

  public function changeUserInfo($userName)
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $jwt_userName = $parsedJWT['userName'];
    if ($userName !== $jwt_userName) {
      return json_response(403, false, 'can\'t edit other user\'s info');
    }
    $body = json_body();
    if ($this->UserModel->changeUserInfo($userName,
      array('nickname' => $body->nickname,
        'email' => $body->email,
        'gender' => $body->gender,
      ))) {
      return json_response(200, true, 'ok');
    }
    // 识别mysql错误，一般是因为email冲突
    $error = $this->db->error();
    switch ($error['code']) {
      case 1062:
        // https://dev.mysql.com/doc/refman/8.0/en/error-messages-server.html#error_er_dup_entry
        $matchRes = preg_match("/Duplicate entry '(.*)' for key '(?:(.*)_UNIQUE|(PRIMARY))'/",
          $error['message'], $matches);
        if ($matchRes !== 1) {
          throw new Exception("Unknown mysql 1062 error: {$errorStr}");
        }
        if (count($matches) === 3) {
          $return = array('result' => 'exists', 'field' => $matches[2]);
        } else if (count($matches) === 4) {
          $return = array('result' => 'exists', 'field' => 'userName');
        } else {
          throw new Exception("Unknown mysql 1062 error: {$errorStr}");
        }
        return json_response(409, false, 'some fields exists', $return);
        break;

      default:
        throw new Exception("Unknown mysql error: {$errorStr}");
        break;
    }
  }

  public function changeUserPassword($userName)
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $jwt_userName = $parsedJWT['userName'];
    if ($userName !== $jwt_userName) {
      return json_response(403, false, 'can\'t edit other user\'s info');
    }
    $body = json_body();
    if ($this->UserModel->verifyUserPassword($userName, $body->oldP) !== 'ok') {
      return json_response(401, false, 'password incorrect');
    }
    if (!$this->UserModel->changeUserPassword($userName, $body->newP)) {
      return json_response(500, false, 'update fail');
    }
    return json_response(200, true, 'ok');
  }

}
