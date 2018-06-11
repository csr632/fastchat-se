<?php
class UserModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
  }

  public function verifyUserPassword($userName, $password)
  {
    $res = $this->db->get_where('users', array('userName' => $userName))->result_array();
    if (count($res) > 1) {
      throw new Exception("userName: {$userName} refer to more than one row");
    }
    if (count($res) < 1) {
      return 'not exist';
		}
    if ($res[0]['password'] !== $password) {
      return 'password incorrect';
    }
    return 'ok';
  }

  public function addUser($userName, $password)
  {
    $res = $this->db->get_where('users', array('userName' => $userName))->result_array();
    if (count($res) > 1) {
      return array(
        'code' => 500,
        'success' => false,
        'msg' => "userName: {$userName} refer to more than one row");
    }
    if (count($res) === 1) {
      return array(
        'code' => 409,
        'success' => false,
        'msg' => "userName: {$userName} already exists");
    }

    $res = $this->db->insert('users', array(
      'userName' => $userName,
      'password' => $password,
    ));

    if (!$res) {
      $errMsg = $this->db->error()['message'];
      return array(
        'code' => 500,
        'success' => false,
        'msg' => "{$errMsg}");
    }

    return array(
      'code' => 200,
      'success' => true,
      'msg' => "ok");
  }
}
