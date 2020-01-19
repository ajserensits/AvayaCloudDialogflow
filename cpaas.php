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

      $project_info = getZangMapping($to);

      if(isset($project_info) == false) {
          $response = zangRespond($from , $to , "Sorry but that number is not affiliated with a Dialogflow project.");
          header('application/xml');
          echo $response;
          die(1);
      }

      $project_id = $project_info["project_id"];
      $key_file = $project_info["key_file"];
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

/*
      $session_id = "";
      if(isExistingSession($from , $to) == true) {
          file_put_contents("./existingSession.txt" , "true");
          $session_id = getExistingSession($from , $to);
          $end_of_conversation = getSpecificContext($project_id , $session_id , "avaya_cloud_end_conversation");
          if(isset($end_of_conversation) == true) {
            destroySession($session_id);
            $session_id = createNewSession($from , $to);
          }
      } else {
          file_put_contents("./existingSession.txt" , "false");
          $session_id = createNewSession($from , $to);
      }
*/

      $session_id = "";
      if(isExistingSession($from , $to) == true) {
          $session_id = getExistingSession($from , $to);
      } else {
          $session_id = createNewSession($from , $to);
      }

      //Push texts into an array
      $text = array();
      array_push($text , $body);

      //Get fulfillment text
      $sms_arr = detect_intent_texts($project_id , $text , $session_id , "en-US");
      $sms_text = $sms_arr["Text"];
      $intent = $sms_arr["Intent"];
      $intent_id = explode("/" , $intent);
      $intent_id = $intent_id[4];
      $end_of_conversation = endOfConversation($intent_id , $project_id);
      if($end_of_conversation == true) {
          destroySession($session_id);
      }

      //Create Avaya Cloud Inbound XML Response
      $xml_response = zangRespond($from , $to , $sms_text);
      file_put_contents("./xml_response.xml" , $xml_response);
      header('application/xml');
      echo $xml_response;

/**
 * Returns the result of detect intent with texts as inputs.
 * Using the same `session_id` between requests allows continuation
 * of the conversation.
 */
function detect_intent_texts($projectId, $texts, $sessionId, $languageCode = 'en-US')
{

        // new session
        $sessionsClient = new SessionsClient();
        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
        //printf('Session path: %s' . PHP_EOL, $session);

        // query for each string in array
        foreach ($texts as $text) {
            // create text input
            $textInput = new TextInput();
            $textInput->setText($text);
            $textInput->setLanguageCode($languageCode);

            // create query input
            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            // get response and relevant info
            $response = $sessionsClient->detectIntent($session, $queryInput);
            $queryResult = $response->getQueryResult();
            $diagnosticInfo = $queryResult->getDiagnosticInfo();
            $outputContexts = $queryResult->getOutputContexts();
            $queryText = $queryResult->getQueryText();
            $intent = $queryResult->getIntent();
            $displayName = $intent->getDisplayName();
            $confidence = $queryResult->getIntentDetectionConfidence();
            $fulfilmentText = $queryResult->getFulfillmentText();
            $fulfilmentMessages = $queryResult->getFulfillmentMessages();

            // output relevant info
            /*
            print(str_repeat("=", 20) . PHP_EOL);
            printf('Query text: %s' . PHP_EOL, $queryText);
            printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
                $confidence);
            print(PHP_EOL);
            printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);
            */

        }

        $sessionsClient->close();
        $resp = array();
        $resp["Text"] = $fulfilmentText;
        $resp["Intent"] = $intent->getName();
        return $resp;


}

function zangRespond($from , $to , $body)
{
    $response = '<Response><Sms from="'.$to.'" to="'.$from.'">'.$body.'</Sms></Response>';
    return $response;
}

function isExistingSession($phone , $zang)
{
    $sessions = file_get_contents("./sessions/sessions.json");
    $json = json_decode($sessions , $assoc = true);

    if(count($json["Sessions"]) == 0) {
        return false;
    }

    $i = 0;
    for($i = 0; $i < count($json["Sessions"]); $i++)
    {
        if($json["Sessions"][$i]["ani"] == $phone && $json["Sessions"][$i]["zang"] == $zang) {
            return true;
        }
    }

    return false;
}

function destroySession($session_id)
{
    $sessions = file_get_contents("./sessions/sessions.json");
    $json = json_decode($sessions , $assoc = true);

    if(count($json["Sessions"]) == 0) {
        return null;
    }

    $i = 0;
    $new_sessions = array();
    $new_sessions["Sessions"] = array();
    for($i = 0; $i < count($json["Sessions"]); $i++)
    {
        if($json["Sessions"][$i]["session"] == $session_id) {
            continue;
        }

        array_push($new_sessions["Sessions"] , $json["Sessions"][$i]);
    }

    file_put_contents("./sessions/sessions.json" , json_encode($new_sessions));
    return true;
}

function getExistingSession($phone , $zang)
{
    $sessions = file_get_contents("./sessions/sessions.json");
    $json = json_decode($sessions , $assoc = true);

    if(count($json["Sessions"]) == 0) {
        return null;
    }

    $i = 0;
    for($i = 0; $i < count($json["Sessions"]); $i++)
    {
        if($json["Sessions"][$i]["ani"] == $phone && $json["Sessions"][$i]["zang"] == $zang) {
            return $json["Sessions"][$i]["session"];
        }
    }


    return null;
}

function createNewSession($phone , $zang)
{
    $sessions = file_get_contents("./sessions/sessions.json");
    $json = json_decode($sessions , $assoc = true);

    $new_session = array();
    $new_session["session"] = time();
    $new_session["ani"] = $phone;
    $new_session["zang"] = $zang;

    array_push($json["Sessions"] , $new_session);

    $sessions = json_encode($json);
    $sessions = file_put_contents("./sessions/sessions.json" , $sessions);

    return $new_session["session"];
}

function getSessionContexts($projectId , $sessionId)
{
      $contextsClient = new ContextsClient();
      try {
        $formattedParent = $contextsClient->sessionName($projectId, $sessionId);
/*
        // Iterate over pages of elements
        $pagedResponse = $contextsClient->listContexts($formattedParent);
        foreach ($pagedResponse->iteratePages() as $page) {
            foreach ($page as $element) {
                // doSomethingWith($element);
            }
        }
*/
        // Alternatively:

        // Iterate through all elements
        $pagedResponse = $contextsClient->listContexts($formattedParent);
        $els = array();
        foreach ($pagedResponse->iterateAllElements() as $element) {
            // doSomethingWith($element);
            array_push($els , $element->getName());
        }

        file_put_contents("./outputContextsMethod.txt" , json_encode($els));
      } finally {
        $contextsClient->close();
    }
}

function getSpecificContext($project_id , $session_id , $context)
{
      $contextsClient = new ContextsClient();
      $response = null;
      try {
        $formattedName = $contextsClient->contextName($project_id, $session_id, $context);
        $response = $contextsClient->getContext($formattedName);
      } catch(Exception $e){
        return null;
      } finally {
        $contextsClient->close();
      }

      return $response;
}

function getZangMapping($to)
{
    $contents = file_get_contents("./project-mappings/mappings.json");
    $json = json_decode($contents , $assoc = true);

    $i = 0;
    for($i = 0; $i < count($json["Mappings"]); $i++)
    {
        if($json["Mappings"][$i]["phone"] == $to) {
            return $json["Mappings"][$i];
        }
    }

    return null;
}

function endOfConversation($intent , $project_id)
{
      return hasContext($intent , "avaya_cloud_end_conversation" , $project_id);
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
