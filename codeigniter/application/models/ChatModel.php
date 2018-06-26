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
    chats.chatId,
    chats.chatName,
    chats.isGroup,
    latestMessages.messageId,
    latestMessages.content,
    latestMessages.`from`
FROM
    inChat,
    chats
        LEFT OUTER JOIN
    (SELECT
        m1.*
    FROM
        messages AS m1
    WHERE
        m1.messageId IN (SELECT
                MAX(m2.messageId)
            FROM
                messages AS m2
            GROUP BY m2.chatId)) AS latestMessages ON latestMessages.chatId = chats.chatId
WHERE
    inChat.userName = ?
        AND chats.chatId = inChat.chatId
MYQUERY;
    $res = $this->db->query($sql, array($userName))->result_array();
    foreach ($res as &$item) {
      $item['isGroup'] = ($item['isGroup'] === '0' ? false : true);
    }
    return $res;
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

  public function createChat($chatName, $isGroup)
  {
    $success = $this->db->insert('chats', array('chatName' => $chatName,
      'isGroup' => $isGroup));
    if (!$success) {
      return null;
    }
    $chatId = $this->db->insert_id();
    return $chatId;
  }

  public function addMembers($chatId, $members)
  {
    $batch = array_map(function ($memberName) use ($chatId) {
      return array('userName' => $memberName, 'chatId' => $chatId);
    }, $members);
    $success = $this->db->insert_batch('inChat', $batch);
    if ($success !== count($members)) {
      return false;
    }
    return true;
  }
}
