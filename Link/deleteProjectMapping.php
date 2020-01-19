<?php


        $phone = $_GET["phone"];
        $project_id = $_GET["project_id"];

        $phone = str_replace(" " , "" , $phone);


        if(strlen($phone) == 10) {
            $phone = "1".$phone;
        }

        if(strpos($phone , "+") !== 0) {
            $phone = "+".$phone;
        }

        $mappings = file_get_contents("../project-mappings/mappings.json");
        $mappings = json_decode($mappings , $assoc = true);

        $i = 0;
        $new_mappings = array();
        $new_mappings["Mappings"] = array();
        $found = false;
        for($i = 0; $i < count($mappings["Mappings"]); $i++)
        {
            if($mappings["Mappings"][$i]["phone"] == $phone && $mappings["Mappings"][$i]["project_id"] == $project_id) {
                $found = true;
                continue;
            }

            array_push($new_mappings["Mappings"] , $mappings["Mappings"][$i]);
        }

        if($found == true) {
            file_put_contents("../project-mappings/mappings.json" , json_encode($new_mappings));
            echo "Success";
        } else {
            echo "NotMapped";
        }



 ?>
