<?php
      $sid = $_GET["Sid"];
      $from = $_GET["From"];
      $to = $_GET["To"];
      $session = $_GET["Session"];
      $agent = $_GET["Agent"];
      $customer = $_GET["Customer"];
      $body = $_GET["Body"];

      file_put_contents("./Webhook/hit.txt" , "true");

      if($agent == "")
      {
          appendToSession($session , $sid , $from , $to , $body);
      }
      else //New Session
      {
          createNewSession($session , $sid , $from , $to , $agent , $customer , $body);
      }

      function createNewSession($session , $sid , $from , $to , $agent , $customer , $body)
      {
          $sessions = file_get_contents("./Webhook/sessions.json");
          $json = json_decode($sessions , $assoc = true);

          $new_session = array();
          $new_session["session"] = $session;
          $new_session["agent"] = $agent;
          $new_session["customer"] = $customer;
          $new_session["messages"] = array();

          $new_message = array();
          $new_message["sid"] = $sid;
          $new_message["from"] = $from;
          $new_message["to"] = $to;
          $new_message["body"] = $body;

          array_push($new_session["messages"] , $new_message);
          array_push($json["Sessions"] , $new_session);

          file_put_contents("./Webhook/sessions.json" , json_encode($json));

      }

      function appendToSession($session , $sid , $from , $to , $body)
      {

          $sessions = file_get_contents("./Webhook/sessions.json");
          $json = json_decode($sessions , $assoc = true);

          $new_message = array();
          $new_message["sid"] = $sid;
          $new_message["from"] = $from;
          $new_message["to"] = $to;
          $new_message["body"] = $body;

          $i = 0;
          for($i = 0; $i < count($json["Sessions"]); $i++)
          {
              if($json["Sessions"][$i]["session"] == $session) {
                    array_push($json["Sessions"][$i]["messages"] , $new_message);
                    break;
              }
          }

          file_put_contents("./Webhook/sessions.json" , json_encode($json));
      }
 ?>
