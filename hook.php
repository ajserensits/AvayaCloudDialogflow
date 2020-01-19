<?php
      //Set env
      $key_file_path = './credentials/default.json';
      $key_file_path = '/var/www/html/Dialogflow/world-healthcare/credentials/default.json';
      $key_file_path = '/var/www/html/Dialogflow/world-healthcare/credentials/contextstoretest-wqkaul-8b2e2c43324e.json';
      $key_file_path = '/var/www/html/Dialogflow/world-healthcare/credentials/avayacloudtest-vowukg-b46c2e51b9ed.json';


      $command = "export GOOGLE_APPLICATION_CREDENTIALS=".$key_file_path;

      putenv('GOOGLE_APPLICATION_CREDENTIALS='.$key_file_path);

      $access_token = "ya29.Il-5B1Al0x4mkAgxZtoduRwUIQPHZ2kwKQGI9wzIgPIcFfspANh4QC_MlyutM8Nt_q0j07a_I62W670NOWkRjlSSX5fbGyrBMuqARuT1pQ2KkiI6dZINZqkSkEaC4iCJxA";
      //$result = `$command`;

      use Google\Cloud\Dialogflow\V2\SessionsClient;
      use Google\Cloud\Dialogflow\V2\QueryInput;
      use Google\Cloud\Dialogflow\V2\TextInput;
      use Google\Cloud\Dialogflow\V2\CreateIntentRequest;
      use Google\Cloud\Dialogflow\V2\Intent;
      use Google\Cloud\Dialogflow\V2\IntentsClient;
      use Google\Cloud\Dialogflow\V2\sessionPath;
      use Google\Cloud\Dialogflow\V2\RequestParamsHeaderDescriptor;
      use Google\Cloud\Dialogflow\V2\Intent_TrainingPhrase_Part;
      use Google\Cloud\Dialogflow\V2\Intent_TrainingPhrase;
      use Google\Cloud\Dialogflow\V2\Intent_Message_Text;
      use Google\Cloud\Dialogflow\V2\Intent_Message;

      require './vendor/autoload.php';


      //[/home/ajserensits/.config/gcloud/application_default_credentials.json]


      $project_id = "contextstoretest-wqkaul";
      $project_id = "avayacloudtest-vowukg";
      $session_id = "532263e2-b4be-5a26-440b-2f12a5a7c817";
      $path_to_key_file = "./credentials/contextstoretest-wqkaul-8b2e2c43324e.json";
      $key_id = "8b2e2c43324e8040853647727d45402034d355f3";
      // Authenticating with keyfile data.
      /*
      $storage = new StorageClient([
          'keyFile' => json_decode(file_get_contents($path_to_key_file), true)
      ]);
      */

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
        printf('Session path: %s' . PHP_EOL, $session);

        // query for each string in array
        foreach ($texts as $text) {
            echo "</br>YUH</br>";
            // create text input
            $textInput = new TextInput();
            $textInput->setText($text);
            $textInput->setLanguageCode($languageCode);

            // create query input
            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            // get response and relevant info
            $response = $sessionsClient->detectIntent($session, $queryInput);
            if(isset($response) == true) {
                file_put_contents("./responseDia.txt"  , $response);
                echo "OK";
            } else {
                echo "NO FUL";
            }
            $queryResult = $response->getQueryResult();
            $queryText = $queryResult->getQueryText();
            $intent = $queryResult->getIntent();
            $displayName = $intent->getDisplayName();
            $confidence = $queryResult->getIntentDetectionConfidence();
            $fulfilmentText = $queryResult->getFulfillmentText();


            // output relevant info
            print(str_repeat("=", 20) . PHP_EOL);
            printf('Query text: %s' . PHP_EOL, $queryText);
            printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
                $confidence);
            print(PHP_EOL);
            printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);
        }

        $sessionsClient->close();


}

$text = array();
array_push($text , "Hi there test it");
detect_intent_texts($project_id , $text , "532263e2-b4be-5a26-440b-2f12a5a7c817" , "en-US");

/*
        $contents = file_get_contents("php://input");
        file_put_contents("google-post.txt" , $contents);

        $post = json_decode($contents , $assoc = true);
        $intent = $post["queryResult"]["intent"]["displayName"];
        $session = $post["session"];
        $session = explode("/" , $session);
        $session = $session[4];

        //$response = file_get_contents("v1-response.txt");
        //$command = "curl 'http://scriptingsolution.com/Dialogflow/world-healthcare/api_call.php'";

        //$resp = `$command`;
        //$message = json_decode($resp , $assoc = true);
        //$message = $message["response"];

        $message = "Response from google";
        $response = file_get_contents("response-good.txt");

        $json = json_decode($response , $assoc = true);
        $json["fulfillmentMessages"][0]["text"]["text"][0] = $message;
        $json["fulfillmentText"] = $message;

        $json["outputContexts"][0]["name"] = str_replace("<PROJECT_ID>" , $project_id , $json["outputContexts"][0]["name"]);
        $json["outputContexts"][0]["name"] = str_replace("<SESSION_ID>" , $session , $json["outputContexts"][0]["name"]);



        $response = json_encode($json);

        header('Content-Type: application/json');
        echo $response;
*/





 ?>
