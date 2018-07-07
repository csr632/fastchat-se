<?php
class ChatModel extends CI_Model
{
  public function __construct()
  {
    $this->load->database();
    $this->load->model('FriendModel');
    $this->load->model('UserModel');

  }

  public function getChatsByUser($userName)
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

  public function getChatsById($chatId)
  {
    return $this->db->where('chatId', $chatId)->get('chats')->row_array();
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

  public function isMember($chatId, $userName)
  {
    $exist = $this->db->get_where('inChat', array('chatId' => $chatId,
      'userName' => $userName))->row_array();
    return !is_null($exist);
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
    $success = $this->db->insert('chats',
      array('chatName' => $isGroup ? $chatName : null,
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

  public function createGroupInvitation($from, $to, $chatId, $message)
  {
    if (is_null($this->UserModel->getUserInfo($from))) {
      return 'sender not exist';
    }
    if (is_null($this->UserModel->getUserInfo($to))) {
      return 'receiver not exist';
    }
    if (is_null($this->getChatsById($chatId))) {
      return 'chat not exist';
    }
    if (!$this->isMember($chatId, $from)) {
      return 'sender not in the group';
    }
    if ($this->isMember($chatId, $to)) {
      return 'receiver already in the group';
    }
    if (!$this->FriendModel->isFriend($from, $to)) {
      return 'receiver is not sender\'s friend';
    }

    $previousInv = $this->searchGroupInvitation($to, $chatId);
    foreach ($previousInv as $inv) {
      if ($inv['state'] === 'pending') {
        return 'invitation exists';
      }
    }
    $res = $this->db
      ->set('time', 'NOW()', false)
      ->insert('groupInvitations', array(
        'from' => $from,
        'to' => $to,
        'chatId' => $chatId,
        'message' => $message,
        'state' => 'pending',
      ));
    return $res ? 'ok' : 'insert fail';
  }

  public function searchGroupInvitation($to, $chatId)
  {
    $res = $this->db
      ->where('chatId', $chatId)
      ->where('to', $to)
      ->get('groupInvitations');
    if (!$res) {
      throw new Exception($this->db->error()['message']);
    }
    return $res->result_array();
  }

  public function getGroupInvitationByUser($userName)
  {
    $res = $this->db
      ->select(array('invId', 'from', 'to', 'UNIX_TIMESTAMP(time) AS time',
        'state', 'message',
        'u1.nickname AS fromNickname', 'u2.nickname AS toNickname',
        'chats.chatId', 'chats.chatName'))
      ->from('groupInvitations')
      ->join('users AS u1', 'u1.userName = groupInvitations.from', 'inner')
      ->join('users AS u2', 'u2.userName = groupInvitations.to', 'inner')
      ->join('chats', 'chats.chatId = groupInvitations.chatId', 'inner')
      ->group_start()
      ->where('to', $userName)
      ->or_where('from', $userName)
      ->group_end()
      ->order_by('time', 'ASC')
      ->get();
    if (!$res) {
      throw new Exception($this->db->error()['message']);
    }
    return $res->result_array();
  }

  public function getGroupInvitationById($invId)
  {
    $res = $this->db
      ->from('groupInvitations')
      ->where('invId', $invId)
      ->get()
      ->row_array();
    return $res;
  }

  public function responseGroupInvitation($invId, $newState)
  {
    $res = $this->db
      ->set('state', $newState)
      ->where('invId', $invId)
      ->update('groupInvitations');
    return $res;
  }

  public function changeChatName($chatId, $newChatName)
  {
    $res = $this->db
      ->set('chatName', $newChatName)
      ->where('chatId', $chatId)
      ->update('chats');
    return $res;
  }

  public function deleteGroupMember($chatId, $userName)
  {
    $this->db->trans_start();
    if (!$this->isMember($chatId, $userName)) {
      return 'not a member';
    }
    if ($this->getChatsById($chatId)['isGroup'] !== '1') {
      return 'can\'t quit private chat';
    }
    $res = $this->db
      ->where('userName', $userName)
      ->where('chatId', $chatId)
      ->delete('inChat');
    if (!$res) {
      return 'delete inChat fail';
    }

    $deleteGroupInvitations = <<<'MYQUERY'
DELETE FROM fastchat_db.groupInvitations
WHERE
		chatId NOT IN (SELECT
				inChat.chatId
		FROM
				fastchat_db.inChat)
MYQUERY;
    $deleteGroupMessages = <<<'MYQUERY'
DELETE FROM fastchat_db.messages
WHERE
		chatId NOT IN (SELECT
				inChat.chatId
		FROM
				fastchat_db.inChat)
MYQUERY;
    $deleteGroup = <<<'MYQUERY'
DELETE FROM fastchat_db.chats
WHERE
		chatId NOT IN (SELECT
				inChat.chatId
		FROM
				fastchat_db.inChat)
MYQUERY;

    $this->db->query($deleteGroupInvitations);
    $this->db->query($deleteGroupMessages);
    $this->db->query($deleteGroup);

    $this->db->trans_complete();
    if ($this->db->trans_status() === false) {
      return 'quit chat fail';
    }
    return true;
  }
}
