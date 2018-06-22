<?php
class Chats extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('ChatModel');
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
}
