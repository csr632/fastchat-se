<?php
class Friends extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('FriendModel');
  }

  public function getFriendList()
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
    return json_response(200, true, 'ok', $friendList);
  }

  public function addFriendRequest()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];

    $body = json_body();
    if (is_null($body->to) || is_null($body->msg)) {
      return json_response(400, false, 'post body incomplete');
    }
    $res = $this->FriendModel->addFriendRequest($userName, $body->to, $body->msg);
    if ($res === true) {
      return json_response(200, true, 'ok');
    }
    return json_response(500, false, $res);
  }

  public function getFriendRequest()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $res = $this->FriendModel->getFriendRequestAbout($userName);
    return json_response(200, true, 'ok', $res);
  }
}
