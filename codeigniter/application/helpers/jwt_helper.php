<?php
use \Firebase\JWT\JWT;

const JWT_KEY = "fastchat_se_default_jwt_key";

function generateJWT($userName)
{
  $token = array(
    "userName" => $userName,
  );
  $jwt = JWT::encode($token, JWT_KEY);
  return $jwt;
}

function parseJWT()
{
  $CI = &get_instance();
  $jwtHeader = $CI->input->get_request_header('Authorization');
  if (is_null($jwtHeader)) {
    return null;
  }
  $decoded = (array) JWT::decode(substr($jwtHeader, strlen('Bearer ')), JWT_KEY, array('HS256'));
  // var_dump($decoded);die();
  return $decoded;
}
