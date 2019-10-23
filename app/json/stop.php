<?php

    require_once '../include/common.php';
    require_once 'json_round1_closing.php';
    require_once 'json_round2_closing.php';
    require_once '../include/token.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ("token")];
    $errors = array_filter($errors);

    if (!isEmpty($errors)) { // if missing or empty token
        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
    }
    else{
        if(isset($_GET["token"])) {
            if(!verify_token($_GET["token"])) { // if invalid token
                $result = [
                    "status" => "error",
                    "message" => ["invalid token"]
                ];
            }
            else{
                $biddingrounddao = new BiddingRoundDAO();
                $round = $biddingrounddao->get_round();
                $status = $biddingrounddao->get_status();

                if($status == "Not Started"){
                    $result = [
                        "status" => "error",
                        "message" => ["round not started"]
                    ];
                }
                else if($status == "Ongoing"){
                    $stop = $biddingrounddao->stop_round($round);
                    if($stop){
                        if($round == 1){
                            close_bidding_round1();
                            $result = [
                                "status" => "success"
                            ];
                        }
                        else{
                            close_bidding_round2();
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
        }
        else{
            $result =[
                "status" => "error",
                "message" => ["HTTP REQUEST NOT FOUND"]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>