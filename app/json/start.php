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
        if(isset($_POST["token"])) {
            if(!verify_token($_POST["token"])) { // if invalid token
                $result = [
                    "status" => "error",
                    "message" => ["invalid token"]
                ];
            }
            else{
                $biddingrounddao = new BiddingRoundDAO();
                $round = $biddingrounddao->get_round();
                $status = strtolower($biddingrounddao->get_status());
                $post = $_POST["round"];

                // not started, ongoing, ended
                if($status == "not started"){
                    if($post == 1 || $post == 2){
                        if($post == $round){ // if round has not started and admin wants to start it
                            $result = [
                                "status" => "success",
                                "round" => $post
                            ];
                            $biddingrounddao->start_round($post); 
                        }
                        else if($post > $round){ // admin wants to start round 2 but round 1 has not started yet
                            $result = [
                                "status" => "error",
                                "message" => ["round $round not started"]
                            ];
                        }
                        else{
                            if($round > $post){ // round 2 has not started but admin wants round 1 to start
                                $result = [
                                    "status" => "error",
                                    "message" => ["round $post ended"]
                                ];
                            }
                        }
                    }
                    else{ // any values that are not 1 or 2
                        $result = [
                            "status" => "error",
                            "message" => ["round $post is not available"]
                        ];
                    }
                }
                else if($status == "ongoing"){
                    if($post == 1 || $post == 2){
                        if($post == $round){ // [ongoing round is equal to what admin wants]
                            $result = [
                                "status" => "success",
                                "round" => $post
                            ];
                        }
                        else if($post > $round){ // [admin wants to start round 2 but round 1 is still ongoing],
                            $result = [
                                "status" => "error",
                                "message" => ["round $round ongoing"]
                            ];
                        }
                        else{
                            if($round > $post){ // [round 2 is on going but admin wants to start round 1]
                                $result = [
                                    "status" => "error",
                                    "message" => ["round $post ended"]
                                ];
                            }
                        }
                    }
                    else{ // any values that are not 1 or 2
                        $result = [
                            "status" => "error",
                            "message" => ["round $post is not available"]
                        ];
                    }
                }
                else{
                    if($status == "ended"){
                        if($post == 1 || $post == 2){
                            if($post == $round){ // if admin wants round to start when that round has ended
                                $result = [
                                    "status" => "error",
                                    "message" => ["round $round ended"]
                                ];
                            }
                            else if ($post > $round){ //admin wants to start round 2 and round 1 has ended
                                $result = [
                                    "status" => "success",
                                    "round" => $post
                                ];
                                $biddingrounddao->start_round($post);
                            }
                            else{
                                if($round > $post){ //admin wants round 1 but round 2 has ended
                                    $result = [
                                        "status" => "error",
                                        "message" => ["round $post has already ended"]
                                    ];
                                }
                            }
                        }
                        else{ // any values that are not 1 or 2
                            $result = [
                                "status" => "error",
                                "message" => ["round $post is not available"]
                            ];
                        }
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
?>