<?php

    function drop_section($drop_courseid, $drop_section) {
        require_once 'process_min_bid.php';
    
        $studentdao = new StudentDAO();
        $biddingrounddao = new BiddingRoundDAO();
        $successfuldao = new SuccessfulDAO();
        $sectionresultsdao = new SectionResultsDAO();
        $biddao = new BidDAO();
    
        $current_round = $biddingrounddao->get_current_round();

        $successful_bids = $successfuldao->get_successful_bids_and_amount($_SESSION["userid"], 1);
        $drop_valid = false;

        foreach($successful_bids as $idx => [$successful_course, $successful_section, $successful_amount]) {
            if($successful_course == $drop_courseid && $successful_section == $drop_section) {
                $drop_valid = true;
                break;
            }
        }

        if($drop_valid) { // must also delete from round1_bid (else view results will show as unsuccessful)
            $drop_success = $successfuldao->drop_section($_SESSION["userid"], $drop_courseid, $drop_section) && $biddao->drop_bid($_SESSION["userid"], $drop_courseid, 1) && $sectionresultsdao->add_one_seat($drop_courseid, $drop_section) && process_min_bid($drop_courseid, $drop_section);
            
            if($drop_success) {
                $refund_success = $studentdao->add_balance($_SESSION["userid"], $successful_amount);
                if($refund_success) {
                    return true;
                }
            }
        } else { // if section student wants to drop isn't in successful tables
            return false;
        }
    }
?>