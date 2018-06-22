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
    // 对于每个好友，不仅找到好友的信息，还要找到对应的私聊的信息
    $res = $this->db->query(<<<'MYQUERY'
SELECT
    `friend`.`userName` AS `userName`,
    `friend`.`nickname` AS `nickname`,
    `friend`.`email` AS `email`,
    `friend`.`gender` AS `gender`,
    `chats`.`chatId` AS `chatId`,
    `chats`.`chatName` AS `chatName`,
    `chats`.`isGroup` AS `isGroup`,
    `latestMessageRow`.`messageId` AS `latestMessageId`,
    `latestMessageRow`.`content` AS `latestContent`,
    `latestMessageRow`.`from` AS `latestFrom`
FROM
    `friendships`,
    `inChat` AS `inChat1`,
    `inChat` AS `inChat2`,
    `chats`,
    `users` AS `friend`,
    (SELECT
        m1.*
    FROM
        messages AS m1
    WHERE
        m1.messageId IN (SELECT
                MAX(m2.messageId)
            FROM
                messages AS m2
            GROUP BY m2.chatId)) AS latestMessageRow
WHERE
    `friendships`.`userName` = 't3'
        AND `inChat1`.`chatId` = `inChat2`.`chatId`
        AND `inChat1`.`userName` = 't3'
        AND `inChat2`.`userName` = `friendships`.`friendName`
        AND `chats`.`chatId` = `inChat1`.`chatId`
        AND `chats`.`isGroup` = 0
        AND `friend`.`userName` = `friendships`.`friendName`
        AND `latestMessageRow`.`chatId` = `chats`.`chatId`
MYQUERY
    );
    return $res->result_array();
  }
}
