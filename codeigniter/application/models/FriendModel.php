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

  public function addFriendRequest($from, $to, $msg)
  {
    if ($from === $to) {
      return 'is self';
    }
    if ($this->isFriend($from, $to)) {
      return 'is friend';
    }
    if (!is_null($this->getFriendRequest($from, $to))) {
      return 'request exist';
    }
    $res = $this->db
      ->set(array('from' => $from,
        'to' => $to,
        'message' => $msg,
        'state' => 'pending'))
      ->set('time', 'NOW()', false)
      ->insert('friendRequests');
    return $res;
  }

  private function isFriend($userName1, $userName2)
  {
    $res = $this->db
      ->from('friendships')
      ->where('userName', $userName1)
      ->where('friendName', $userName2)
      ->get()
      ->row_array();
    if (is_null($res)) {
      return false;
    }
    return true;
  }

  private function getFriendRequest($from, $to)
  {
    $res = $this->db
      ->from('friendRequests')
      ->where('from', $from)
      ->where('to', $to)
      ->get()
      ->row_array();
    return $res;
  }

  public function getFriendRequestAbout($userName)
  {
    $res = $this->db
      ->select(array('reqId', 'from', 'to', 'UNIX_TIMESTAMP(time) AS time',
        'state', 'message',
        'u1.nickname AS fromNickname', 'u2.nickname AS toNickname'))
      ->from('friendRequests')
      ->join('users AS u1', 'u1.userName = friendRequests.from', 'inner')
      ->join('users AS u2', 'u2.userName = friendRequests.to', 'inner')
      ->group_start()
      ->where('to', $userName)
      ->or_where('from', $userName)
      ->group_end()
      ->order_by('time', 'ASC')
      ->get();
    return $res->result_array();
  }
}
