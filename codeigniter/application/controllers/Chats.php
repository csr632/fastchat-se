<?php
class Chats extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('ChatModel');
    $this->load->model('FriendModel');
  }

  public function getChatsByUser()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $chats = $this->ChatModel->getChatsByUser($userName);
    $chats = array_map(
      function ($item) {
        return array(
          'chatId' => $item['chatId'],
          'chatName' => $item['chatName'],
          'isGroup' => $item['isGroup'],
          'lastestMessage' => is_null($item['messageId']) ? null :
          array('messageId' => $item['messageId'],
            'content' => $item['content'],
            'from' => $item['from'],
          ),
        );
      },
      $chats);
    json_response(200, true, 'ok', $chats);
  }

  public function getMessages($chatId)
  {
    $res = $this->ChatModel->getMessages($chatId);
    if (is_null($res)) {
      json_response(404, false, "chatId {$chatId} not exist");
      return;
    }
    json_response(200, true, 'ok', $res);
  }

  public function postMessages($chatId)
  {
    $body = json_body();
    $message = $this
      ->ChatModel
      ->saveMessage($chatId, $body->from, $body->content);
    if (is_null($message)) {
      json_response(500, false, 'insert fail');
      return;
    }
    json_response(200, true, 'ok', $message);
  }

  public function getMembers($chatId)
  {
    $res = $this->ChatModel->getMembers($chatId);
    if (is_null($res)) {
      json_response(404, false, "chatId {$chatId} not exist");
      return;
    }
    json_response(200, true, 'ok', $res);
  }

  public function postChat()
  {
    // post chat API只能创建群聊
    // 私聊只能通过添加好友来创建
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $body = json_body();
    if (isset($body->chatName)) {
      $chatId = $this->createGroupChat($userName, $body->chatName);
    } else {
      $chatId = $this->createGroupChat($userName, '默认群聊名称');
    }
    json_response(200, true, 'ok', array('chatId' => $chatId,
      'chatName' => $body->chatName,
      'isGroup' => true,
      'lastestMessage' => null));
  }

  private function createPrivateChat($memberName1, $memberName2)
  {
    $chatId = $this->ChatModel->createChat(null, false);
    if (is_null($chatId)) {
      json_response(500, false, 'insert chat fail');
      return;
    }
    $success = $this->ChatModel->addMembers($chatId, array($memberName1, $memberName2));
    if (!$success) {
      json_response(500, false, 'insert members fail');
      return;
    }
    return $chatId;
  }

  private function createGroupChat($creatorName, $chatName)
  {
    $chatId = $this->ChatModel->createChat($chatName, true);
    if (is_null($chatId)) {
      json_response(500, false, 'insert chat fail');
      return;
    }
    $success = $this->ChatModel->addMembers($chatId, array($creatorName));
    if (!$success) {
      json_response(500, false, 'insert members fail');
      return;
    }
    return $chatId;
  }

  public function inviteFriend()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $body = json_body();
    $res = $this->ChatModel->createGroupInvitation($userName,
      $body->friendName, $body->chatId, $body->message);
    switch ($res) {
      case 'receiver already in the group':
        return json_response(409, false, $res);
        break;
      case 'invitation exists':
        return json_response(409, false, $res);
        break;
      case 'insert fail':
        return json_response(500, false, $res);
        break;
      case 'ok':
        return json_response(200, true, $res);
        break;
      default:
        return json_response(400, false, 'bad request');
        break;
    }
  }

  public function getGroupInvitationByUser()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $res = $this->ChatModel->getGroupInvitationByUser($userName);
    return json_response(200, true, 'ok', $res);
  }

  public function processGroupInvitation($invId)
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $inv = $this->ChatModel->getGroupInvitationById($invId);
    if (is_null($inv)) {
      return json_response(404, false, 'group invitation not exist');
    }
    if ($inv['to'] !== $userName) {
      return json_response(403, false, 'permission deny');
    }
    if ($inv['state'] !== 'pending') {
      return json_response(403, false, 'inv is already responsed');
    }
    $body = json_body();
    $this->db->trans_start();
    $this->ChatModel->responseGroupInvitation($invId, $body->state);
    if ($body->state === 'accepted') {
      $this->ChatModel->addMembers($inv['chatId'], array($userName));
    }
    $this->db->trans_complete();
    if ($this->db->trans_status() === false) {
      return json_response(500, false, 'fail to process friendRequests');
    }
    return json_response(200, true, 'ok');
  }

  public function patchChatName($chatId)
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];

    if (!$this->ChatModel->isMember($chatId, $userName)) {
      return json_response(403, false, 'not in the chat');
    }
    $body = json_body();
    if (!$this->ChatModel->changeChatName($chatId, $body->chatName)) {
      return json_response(500, false, 'changeChatName');
    }
    return json_response(200, true, 'ok');
  }
}
