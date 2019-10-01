<?php

function close_bidding_round1(){
    $biddao = new BidDAO();
    $sectiondao = new SectionDAO();
    $sectionresultsdao = new SectionResultsDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $successfuldao = new SuccessfulDAO();

    $sectionresultsdao->removeAll(1);
    $cs = $sectiondao->retrieve_all_course_section();

    $min_bid = 10;

    // adding all course+section to round 1 results 
    // (using min_bid = 10 and vacancies = maximum section size
    for($i=0; $i<count($cs); $i++){ // $cs = [[IS110, S1, 45] , [IS110, S2, 45], ... ]
        $course = $cs[$i][0];
        $section = $cs[$i][1];
        $max_size = $cs[$i][2];

        // add all possible course-sections into round1_results table, default min_bid assume 10
        $sectionresultsdao->add_results($course, $section, $min_bid, $max_size, 1);
    } 

    // obtains an array of bids
    // $temp_arr = [ "IS100, S1" => [ ['ben.ng.2009','11'], ['calvin.ng.2009','12'] ], ... ]
    $temp_arr = $biddao->retrieve_all_bids(1);

    foreach($temp_arr as $course_section_str=>$array_of_bids){
        $course = explode(", ", $course_section_str)[0];
        $section = explode(", ", $course_section_str)[1];

        // get maximum capacity of each section
        $capacity = (int)($sectiondao->get_size($course, $section));

        // echo "Capacity: $capacity";
        // $no_of_bids = count($value);
        // echo "Number of bids: $no_";

        if($capacity == count($array_of_bids)) { // if section is just nice full
            $vacancies = 0;
            
            usort(
                $array_of_bids, 
                function($a, $b) {
                    $result = 0;
                    if ($a[1] < $b[1]) {
                        $result = 1;
                    } else if ($a[1] > $b[1]) {
                        $result = -1;
                    }
                    return $result; 
                }
            );

            $clearing_price = $array_of_bids[$capacity-1][1];

            if($array_of_bids[$capacity-2][1] == $clearing_price) { // if more than 1 clearing price bid
                for($i=0; $i<$capacity; $i++) {
                    if($array_of_bids[$i][1] == $clearing_price) { // if this bid amount = clearing price
                        unset($array_of_bids[$i]);
                        $vacancies++;
                    }
                }
            }

            foreach($array_of_bids as $idx => [$userid, $amount]) {
                $successfuldao->add_success($userid, $amount, $course, $section, 1);
            }
        } elseif($capacity < count($array_of_bids)) { // if section is OVER booked
            $vacancies = 0;

            usort(
                $array_of_bids, 
                function($a, $b) {
                    $result = 0;
                    if ($a[1] < $b[1]) {
                        $result = 1;
                    } else if ($a[1] > $b[1]) {
                        $result = -1;
                    }
                    return $result; 
                }
            );

            $clearing_price = $array_of_bids[$capacity-1][1];

            $first_cut_successful = array_slice($array_of_bids, 0, $capacity);

            $number_of_clearing_price_bids = 0;
            foreach($array_of_bids as $idx => [$userid, $amount]) {
                if($amount == $clearing_price) {
                    $number_of_clearing_price_bids++;
                }
                if($number_of_clearing_price_bids > 1) {
                    break;
                }
            }

            if($number_of_clearing_price_bids > 1) {
                for($i=0; $i<$capacity; $i++) {
                    if($first_cut_successful[$i][1] == $clearing_price) { // if this bid amount = clearing price
                        unset($first_cut_successful[$i]);
                        $vacancies++;
                    }
                }
            }

            foreach($first_cut_successful as $idx => [$userid, $amount]) {
                $successfuldao->add_success($userid, $amount, $course, $section, 1);
            }

        } else { // if section is UNDER booked, aka everyone succeeded
            $vacancies = $capacity - count($array_of_bids);

            usort(
                $array_of_bids, 
                function($a, $b) {
                    $result = 0;
                    if ($a[1] < $b[1]) {
                        $result = 1;
                    } else if ($a[1] > $b[1]) {
                        $result = -1;
                    }
                    return $result; 
                }
            );

            $clearing_price = $array_of_bids[count($array_of_bids)-1][1];

            foreach($array_of_bids as $idx => [$userid, $amount]) {
                $successfuldao->add_success($userid, $amount, $course, $section, 1);
            }
        }

        $sectionresultsdao->update_results($course, $section, $clearing_price, $vacancies, 1);
    }

    $update_bidding_round_success = $biddingrounddao->updateBiddingRound(2);
    if($update_bidding_round_success) {
        $current_round = $biddingrounddao->checkBiddingRound();
        return "Round <strong>1</strong> has been successfuly closed.<br>Round <strong>$current_round</strong> has begun.";
    } else {
        return "ROUND 1 CLOSING FAILED";
    }
}

?>