<?php
class UserModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
  }

  public function getUser($userName)
  {
    $query = $this->db->get_where('user', array('user_name' => $userName));
    return $query->row_array();
  }

  public function addUser($userName, $password)
  {
    $data = array(
      'user_name' => $userName,
      'password' => $password,
    );
    return $this->db->insert('user', $data);
  }
}
