<?php

      //CPaaS Inbound XML Parameters
      $from = $_GET["From"];
      $to = $_GET["To"];
      $body = $_GET["Body"];

      //Set env
      //$key_file_path = './credentials/default.json';
      //$key_file_path = '/var/www/html/Dialogflow/world-healthcare/credentials/default.json';
      //avayacloudtest-vowukg-b46c2e51b9ed
      $key_file_base = "/var/www/html/Dialogflow/credentials/";


      $project_id = "avayacloudtest-vowukg";
      $key_file = "avayacloudtest-vowukg-b46c2e51b9ed.json";
      $key_file_path = $key_file_base.$key_file;

      putenv('GOOGLE_APPLICATION_CREDENTIALS='.$key_file_path);

      use Google\Cloud\Dialogflow\V2\SessionsClient;
      use Google\Cloud\Dialogflow\V2\QueryInput;
      use Google\Cloud\Dialogflow\V2\TextInput;
      use Google\Cloud\Dialogflow\V2\CreateIntentRequest;
      use Google\Cloud\Dialogflow\V2\Intent;
      use Google\Cloud\Dialogflow\V2\IntentsClient;
      use Google\Cloud\Dialogflow\V2\ContextsClient;
      use Google\Cloud\Dialogflow\V2\sessionPath;
      use Google\Cloud\Dialogflow\V2\RequestParamsHeaderDescriptor;
      use Google\Cloud\Dialogflow\V2\Intent_TrainingPhrase_Part;
      use Google\Cloud\Dialogflow\V2\Intent_TrainingPhrase;
      use Google\Cloud\Dialogflow\V2\Intent_Message_Text;
      use Google\Cloud\Dialogflow\V2\Intent_Message;

      require './vendor/autoload.php';


      $intent_id = "a7790b60-3949-4057-8720-f113dbaf3aa1";

      $intent = findBeginningIntent($project_id);
      if(isset($intent) == true) {
          echo $intent->getDisplayName();
          echo "</br>";
          $message = $intent->getMessages();
          echo json_encode((array) $message);

          $iter = $message->getIterator();

          while($iter->valid() == true)
          {
            echo "</br> YO </br>";
            file_put_contents("./inloop.txt" , "True");
            $current = $iter->current();
            echo json_encode((array)$current);
            echo "</br>";
            echo json_encode((array)$current->getDesc());
            //echo json_encode((array) $current->getMessage());
            echo "</br>";
            $iter->next();
          }

      } else {
          echo "NULL";
      }


        function findBeginningIntent($project_id)
        {
              $intentsClient = new IntentsClient();
              try {
                $formattedParent = $intentsClient->projectAgentName($project_id);

                // Iterate through all elements
                $pagedResponse = $intentsClient->listIntents($formattedParent);
                foreach ($pagedResponse->iterateAllElements() as $element) {
                    $intent_id = $element->getName();
                    $intent_id = explode("/" , $intent_id);
                    $intent_id = $intent_id[4];
                    if(hasContext($intent_id , "avaya_cloud_begin_conversation" , $project_id) == true) {
                        return $element;
                    }
                }
              } catch(Exception $e) {
                    file_put_contents("./exception-voice-begin.txt" , json_encode((array) $e));
                } finally {
                    $intentsClient->close();
                }

                return null;
        }

        function hasContext($intent_id , $context_name , $project_id)
        {

            if(isset($intent_id) == false) {
                return false;
            }

            $intentsClient = new IntentsClient();
            try {
                $formattedName = $intentsClient->intentName($project_id, $intent_id);
                $response = $intentsClient->getIntent($formattedName);
                $contexts = $response->getOutputContexts();
                $iter = $contexts->getIterator();

                while($iter->valid() == true)
                {
                  file_put_contents("./inloop.txt" , "True");
                  $current = $iter->current();
                  $name = $current->getName();
                  $name = explode("/" , $name);
                  $name = $name[6];
                  if($name == $context_name) {
                      return true;
                  }
                  $iter->next();
                }
            } catch(Exception $e) {
                file_put_contents("./exception-hascontext.txt" , json_encode((array) $e));
            } finally {
              $intentsClient->close();
            }

            return false;
        }

?>
