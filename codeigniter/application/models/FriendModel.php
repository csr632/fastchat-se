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
    // 对于每个好友，不仅找到好友的信息，还要找到对应的私聊的chatId
    $res = $this->db->select(array(
      'users.userName as userName',
      'users.email as email',
      'users.nickname as nickname',
      'users.gender as gender',
      'chats.chatId',
    ))
      ->from(array(
        'friendships',
        'users',
        'inChat as ic1',
        'inChat as ic2',
        'chats',
      ))
      ->where('friendships.userName', $userName)
      ->where('friendships.friendName = users.userName')
      ->where('ic1.chatId = ic2.chatId')
      ->where('ic1.chatId = chats.chatId')
      ->where('chats.isGroup', false)
      ->where('ic1.userName', $userName)
      ->where('ic2.userName = friendships.friendName')
      ->get();
    return $res->result_array();
  }
}
