<?php

    require_once '../include/common.php';
    require_once 'json_round1_closing.php';
    require_once 'json_round2_closing.php';
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
                "status" => "error",
                "message" => ["round already ended"]
            ];
        }
        else if($status == "Ongoing"){
            $stop = $biddingrounddao->stop_round($round);
            if($stop){
                if($round == 1){
                    json_close_bidding_round1();
                    $result = [
                        "status" => "success"
                    ];
                }
                else{
                    json_close_bidding_round2();
                    $result = [
                        "status" => "success"
                    ];
                }
            }
        }
        else{
            if($status == "Ended"){
                $result = [
                    "status" => "error",
                    "message" => ["round already ended"]
                ];
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>