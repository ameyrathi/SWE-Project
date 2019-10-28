<?php

    require_once '../include/common.php';
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
        }
        else{
            $result = [
                "status" => "error",
                "message" => ["HTTP REQUEST NOT FOUND"]
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
?>