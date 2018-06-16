<?php

class FriendModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
    $this->load->model('UserModel');
  }

  public function getFriendsOf($userName)
  {
		// 如果用户不存在，返回null
    if (is_null($this->UserModel->getUserInfo($userName))) {
      return null;
    }
    $res = $this->db
      ->get_where('friendship', array('userName' => $userName))
      ->result_array();
    $friendList = array_map(
      function ($item) {
        return $this->UserModel->getUserInfo($item['friendName']);
      },
      $res);
    return $friendList;
  }
}
