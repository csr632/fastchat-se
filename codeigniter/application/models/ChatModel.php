<?php
class ChatModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
  }

  public function getChats($userName)
  {
    $sql = <<<'MYQUERY'
SELECT
    chats.chatId, chats.chatName,
    chats.isGroup,
    latestMessages.messageId,
    latestMessages.content,
    latestMessages.`from`
FROM
    inChat,
    chats,
    (SELECT
        m1.*
    FROM
        messages AS m1
    WHERE
        m1.messageId IN (SELECT
                MAX(m2.messageId)
            FROM
                messages AS m2
            GROUP BY m2.chatId)) AS latestMessages
WHERE
    inChat.userName = ?
    AND chats.chatId = inChat.chatId
		AND latestMessages.chatId = chats.chatId
MYQUERY;
    $res = $this->db->query($sql, array($userName));
    return $res->result_array();
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

  public function saveMessage($chatId, $from, $content)
  {
    $insert = array('chatId' => $chatId,
      'from' => $from,
      'content' => $content);
    $success = $this->db->insert('messages', $insert);
    if (!$success) {
      return null;
    }
    $messageId = $this->db->insert_id();
    $insert['messageId'] = $messageId;
    return $insert;
  }
}
