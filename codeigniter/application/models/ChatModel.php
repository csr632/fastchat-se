<?php
class ChatModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
  }

  public function getMembers($chatId)
  {
    // 如果不存在这个chatId，返回NULL
    $exist = $this->db->get_where('chats', array('chatId' => $chatId))->row_array();
    if (is_null($exist)) {
      return null;
    }

    $res = $this->db
      ->select(array('u.userName',
        'u.nickname',
        'u.email',
        'u.gender',
      ))
      ->from(array('inChat as i', 'users as u'))
      ->where('i.chatId', $chatId)
      ->where('i.userName = u.userName')
      ->get()
      ->result_array();

    return $res;
  }

  public function getMessages($chatId)
  {
    // 如果不存在这个chatId，返回NULL
    $exist = $this->db->get_where('chats', array('chatId' => $chatId))->row_array();
    if (is_null($exist)) {
      return null;
    }

    $res = $this->db
      ->select(array('messageId',
        'content',
        'from',
      ))
      ->from('messages')
			->where('chatId', $chatId)
			->order_by('messageId', 'ASC')
      ->get()
      ->result_array();

    return $res;
  }
}
