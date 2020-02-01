<?php


      $project_id = "avayacloudtest-vowukg";

      $contents = file_get_contents("php://input");
      file_put_contents("google-post.txt" , $contents);

      $post = json_decode($contents , $assoc = true);
      $intent = $post["queryResult"]["intent"]["displayName"];
      $session = $post["session"];
      $session = explode("/" , $session);
      $project_id = $session[1];
      $session = $session[4];

      $oc = $post["queryResult"]["outputContexts"];


      $message = "Generic Response from google";
      $response = file_get_contents("response-good.txt");

      switch($intent)
      {
          case "Test it":
              $message = "Test it response for avaya cloud";
              $context = "TestItFulfillment";
          break;

          case "AuthCode":
              $message = "Auth Code response for avaya cloud";
              $context = "AuthCodeFulfillment";

          break;

          case "Again here":
              $message = "Again here response for avaya cloud";
              $context = "AgainHereFulfillment";
          break;

          case "EndSession":
              $message = "Ending the session for avaya cloud";
              $context = "EndSessionFulfillment";
          break;
      }

      $json = json_decode($response , $assoc = true);
      $json["fulfillmentMessages"][0]["text"]["text"][0] = $message;
      $json["fulfillmentText"] = $message;
      unset($json["outputContexts"]);

      //$json["outputContexts"][0]["name"] = str_replace("<PROJECT_ID>" , $project_id , $json["outputContexts"][0]["name"]);
      //$json["outputContexts"][0]["name"] = str_replace("<SESSION_ID>" , $session , $json["outputContexts"][0]["name"]);

/*
      $i = 0;
      for($i = 0; $i < count($oc); $i++)
      {
          array_push($json["outputContexts"] , $oc[$i]);
          file_put_contents('./ful-res.txt' , $i);

      }
      */
      //$json["outputContexts"][0]["name"] = str_replace("myhookcontextstuff" , $context , $json["outputContexts"][0]["name"]);




      $response = json_encode($json);

      file_put_contents('./ful-rest.txt' , $response);

      header('Content-Type: application/json');
      echo $response;






 ?>
