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

  public function addUser($userName, $password, $email)
  {
    $res = $this->db->insert('users', array(
      'userName' => $userName,
      'password' => $password,
      'email' => $email,
    ));
    if ($res === false) {
      $error = $this->db->error();
      switch ($error['code']) {
				case 1062:
					// https://dev.mysql.com/doc/refman/8.0/en/error-messages-server.html#error_er_dup_entry
          $matchRes = preg_match("/Duplicate entry '(.*)' for key '(?:(.*)_UNIQUE|(PRIMARY))'/",
            $error['message'], $matches);
          if ($matchRes !== 1) {
            throw new Exception("Unknown mysql 1062 error: ${$error}");
          }
          if (count($matches) === 3) {
            $return = array('result' => 'exists', 'field' => $matches[2]);
          } else if (count($matches) === 4) {
            $return = array('result' => 'exists', 'field' => 'userName');
          } else {
            throw new Exception("Unknown mysql 1062 error: ${$error}");
          }
          break;

        default:
          throw new Exception("Unknown mysql error: ${$error}");
          break;
      }
    } else {
      $return = array('result' => 'ok');
    }
    return $return;
  }
}
