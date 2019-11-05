<?php

    require_once '../include/common.php';
    require_once '../include/token.php';
    $sortclass = new Sort();

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("token")];
    $errors = array_filter($errors);
    
    if(isset($_GET["token"]) && $_GET["token"] != "") {
        if(!verify_token($_GET["token"])) { // if invalid token
            $errors[] = "invalid token";
        }
    }

    // if there are common validation errors
    if(!isEmpty($errors)) {
        $errors = $sortclass->sort_it($errors, "common_validation");

        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
    } else { // if there are no common validation errors
        $biddingrounddao = new BiddingRoundDAO();
        $round = $biddingrounddao->get_round();
        $status = $biddingrounddao->get_status();
        
        if($status == "Not Started"){
            $result = [
                "status" => "success",
                "round" => (int)$round
            ];
            $biddingrounddao->start_round($round);
        }
        else if($status == "Ongoing"){
            $result = [
                "status" => "success",
                "round" => (int)$round
            ];
        }
        else{
            if($status == "Ended"){
                if($round == 1){
                    $result = [
                        "status" => "success",
                        "round" => 2
                    ];
                    $biddingrounddao->start_round(2);
                }
                else{
                    if($round == 2){
                        $result = [
                            "status" => "error",
                            "message" => ["round 2 ended"]
                        ];
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
?>