<?php

function json_body()
{
  $CI = &get_instance();
  // https://stackoverflow.com/a/37570103
  $body = json_decode($CI->security->xss_clean($CI->input->raw_input_stream));
  return $body;
}

function json_response($code = 500, $success = false, $msg = 'msg not set', $data = null)
{
  $CI = &get_instance();
  return $CI->output
    ->set_content_type('application/json')
    ->set_status_header($code)
    ->set_output(json_encode(array(
      'success' => $success,
      'msg' => $msg,
      'data' => $data,
    )));
}
