<?php
    function drop_bid($drop_courseid, $drop_section) {
        require_once 'include/common.php';
        require_once 'process_min_bid.php';

        $StudentDAO = new StudentDAO();
        $biddingrounddao = new BiddingRoundDAO();
        $current_round = $biddingrounddao->get_current_round();
    
    
        $BidDAO = new BidDAO();
        $list_of_bids = $BidDAO->get_pending_bids_and_amount($_SESSION["userid"], $current_round);

        $bid_valid = false;

        foreach($list_of_bids as $this_list) {
            $this_courseid = $this_list[0];
            $this_section = $this_list[1];
            if($drop_courseid == $this_courseid && $drop_section == $this_section) {
                $bid_valid = true;
                $this_amount = $this_list[2];
                break;
            }
        }

        if($bid_valid) {
            $BidDAO = new BidDAO();
            $StudentDAO = new StudentDAO();
            $drop_success = $BidDAO->drop_bid($_SESSION["userid"], $drop_courseid, $current_round) && $StudentDAO->add_balance($_SESSION["userid"], $this_amount);
            $new_balance = $StudentDAO->get_balance($_SESSION["userid"]);

            if($drop_success) {
                if($current_round == 2) {
                    process_min_bid($drop_courseid, $drop_section);
                }
                
                return true;

                // $success_message = "<strong>Your bid for $drop_courseid $drop_section has been successfully dropped.<br>You have been refunded $$this_amount. Your current e$ balance is $$new_balance.</strong>";

                // return $success_message;
            }
        } else {
            return false;
            // $error_message = "<strong><span id='error'>Error:</span></strong><br><br><span id='error'>$drop_courseid $drop_section is not a course you have bidded for.</span>";
        }
    }
?>