<?php

      $post = file_get_contents("php://input");

      $phone = $_GET["phone"];
      $projectId = $_GET["projectId"];
      $eventName = $_GET["eventName"];
      $webhook = $_GET["webhook"];
      $fileName = $projectId.".json";

      $phone = str_replace(" " , "" , $phone);

      if(strlen($phone) == 10) {
          $phone = "1".$phone;
      }

      if(strpos($phone , "+") === false) {
          $phone = "+".$phone;
      }

      $mapping = array();
      $mapping["phone"] = $phone;
      $mapping["project_id"] = $projectId;
      $mapping["key_file"] = $fileName;
      $mapping["welcome_intent"] = $eventName;
      $mapping["webhook"] = $webhook;
      file_put_contents("../credentials/".$fileName , $post);


      $mappings = file_get_contents("../project-mappings/mappings.json");
      $mappings = json_decode($mappings , $assoc = true);

      $i = 0;
      $found = false;
      for($i = 0; $i < count($mappings["Mappings"]); $i++)
      {
          if($mappings["Mappings"][$i]["phone"] == $phone) {
              $found = true;
              break;
          }
      }

      if($found == true) {
          echo "Dup";

      } else {
          array_push($mappings["Mappings"] , $mapping);
          file_put_contents("../project-mappings/mappings.json" , json_encode($mappings));
          file_put_contents("../project-mappings-voice/mappings.json" , json_encode($mappings));

          echo "Success";
      }







 ?>
