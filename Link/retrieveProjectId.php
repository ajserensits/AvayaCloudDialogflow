<?php


        $phone = $_GET["phone"];

        $phone = str_replace(" " , "" , $phone);

        if(strlen($phone) == 10) {
            $phone = "1".$phone;
        }

        if(strpos($phone , "+") === false) {
            $phone = "+".$phone;
        }

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
            $response = array();
            $response["projectId"] = $mappings["Mappings"][$i]["project_id"];
            echo json_encode($response);
        } else {
            $response = array();
            $response["projectId"] = "<NONE>";
            echo json_encode($response);
        }



 ?>
