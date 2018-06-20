<?php
class Friends extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('FriendModel');
  }

  public function index()
  {
    if ($this->input->method() === 'get') {
      $this->getFriendList();
    } else {
      show_404();
    }
  }

  private function getFriendList()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $friendList = $this->FriendModel->getFriendsOf($userName);
    if (is_null($friendList)) {
      return json_response(401, false, 'jwt header invalid');
    }
    $friendList = array_map(
      function ($item) {
        return array('friendInfo' => array('userName' => $item['userName'],
          'nickname' => $item['nickname'],
          'email' => $item['email'],
          'gender' => $item['gender']),
          'chatInfo' => array('chatId' => $item['chatId'],
            'chatName' => $item['chatName'],
            'isGroup' => $item['isGroup'],
            // 'lastestMessage' => $item['lastestMessage'],
          ));
      },
      $friendList);
    return json_response(200, true, 'ok', $friendList);
  }
}
