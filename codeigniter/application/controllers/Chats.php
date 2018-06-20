<?php
class Chats extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('ChatModel');
  }

  public function messages($chatId)
  {
    $res = $this->ChatModel->getMessages($chatId);
    if (is_null($res)) {
      json_response(404, false, "chatId {$chatId} not exist");
      return;
    }
    json_response(200, true, 'ok', $res);
  }

  public function members($chatId)
  {
    $res = $this->ChatModel->getMembers($chatId);
    if (is_null($res)) {
      json_response(404, false, "chatId {$chatId} not exist");
      return;
    }
    json_response(200, true, 'ok', $res);
  }
}
