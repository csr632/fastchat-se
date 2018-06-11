<?php
class SessionModel extends CI_Model
{
  public function __construct()
  {
    // $this->load->database();
    $this->load->model('UserModel');
  }

  public function createSession($userName, $password)
  {
    $result = $this->UserModel->verifyUserPassword($userName, $password);
    if ($result === 'ok') {
      log_message('debug', "{$userName} login success");
    } else {
      log_message('debug', "{$userName} login fail: {$result}");
    }
    return $result;
  }
}
