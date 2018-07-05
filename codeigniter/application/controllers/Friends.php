<?php
class Friends extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('FriendModel');
    $this->load->database();
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

  public function getFriendRequestByUser()
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $res = $this->FriendModel->getFriendRequestAbout($userName);
    return json_response(200, true, 'ok', $res);
  }

  public function processFriendRequest($reqId)
  {
    $parsedJWT = parseJWT();
    if (is_null($parsedJWT)) {
      return json_response(401, false, 'no jwt header');
    }
    $userName = $parsedJWT['userName'];
    $req = $this->FriendModel->getFriendRequestById($reqId);
    if (is_null($req)) {
      return json_response(404, false, 'friend request not exist');
    }
    if ($req['to'] !== $userName) {
      return json_response(403, false, 'permission deny');
    }
    $body = json_body();

    $this->db->trans_start();
    $this->FriendModel->responseFriendRequest($reqId, $body->state);
    if ($body->state === 'accepted') {
      $this->FriendModel->establishFrienship($req['from'], $req['to']);
    }
    $this->db->trans_complete();
    if ($this->db->trans_status() === false) {
      return json_response(500, false, 'fail to process friendRequests');
    }
    return json_response(200, true, 'ok');
  }
}
