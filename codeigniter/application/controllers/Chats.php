<?php
class Chats extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('ChatModel');
  }

  public function getChats()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $chats = $this->ChatModel->getChats($userName);
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
}
