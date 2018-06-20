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
    $res = $this->db->select(array(
      'friend.userName as userName',
      'friend.nickname as nickname',
      'friend.email as email',
      'friend.gender as gender',
      'chats.chatId as chatId',
      'chats.chatName as chatName',
      'chats.isGroup as isGroup',
    ))
      ->from(array('friendships',
        'inChat as inChat1',
        'inChat as inChat2',
        'chats',
        'users as friend'))
      ->where('friendships.userName', $userName)
      ->where('inChat1.chatId = inChat2.chatId')
      ->where('inChat1.userName', $userName)
      ->where('inChat2.userName = friendships.friendName')
      ->where('chats.chatId = inChat1.chatId')
      ->where('chats.isGroup', false)
      ->where('friend.userName = friendships.friendName')
      ->get()
      ->result_array();

    return $res;
  }
}
