<?php


      $project_id = "contextstoretest-wqkaul";

      $contents = file_get_contents("php://input");
      file_put_contents("google-post.txt" , $contents);

      $post = json_decode($contents , $assoc = true);
      $intent = $post["queryResult"]["intent"]["displayName"];
      $session = $post["session"];
      $session = explode("/" , $session);
      $project_id = $session[1];
      $session = $session[4];


      $message = "Generic Response from google";
      $response = file_get_contents("response-good.txt");

      switch($intent)
      {
          case "Test it":
              $message = "Test it response";
              $context = "TestItFulfillment";
          break;

          case "AuthCode":
              $message = "Auth Code response";
              $context = "AuthCodeFulfillment";

          break;

          case "Again here":
              $message = "Again here response";
              $context = "AgainHereFulfillment";
          break;
      }

      $json = json_decode($response , $assoc = true);
      $json["fulfillmentMessages"][0]["text"]["text"][0] = $message;
      $json["fulfillmentText"] = $message;
      //unset($json["outputContexts"]);

      $json["outputContexts"][0]["name"] = str_replace("<PROJECT_ID>" , $project_id , $json["outputContexts"][0]["name"]);
      $json["outputContexts"][0]["name"] = str_replace("<SESSION_ID>" , $session , $json["outputContexts"][0]["name"]);
      $json["outputContexts"][0]["name"] = str_replace("myhookcontextstuff" , $context , $json["outputContexts"][0]["name"]);




      $response = json_encode($json);

      header('Content-Type: application/json');
      echo $response;






 ?>
