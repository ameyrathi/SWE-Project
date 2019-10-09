<?php

function close_bidding_round1(){
    $biddao = new BidDAO();
    $sectiondao = new SectionDAO();
    $sectionresultsdao = new SectionResultsDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $successfuldao = new SuccessfulDAO();
    $unsuccessfuldao = new UnsuccessfulDAO();
    $studentdao = new StudentDAO();

    $sectionresultsdao->removeAll();
    $cs = $sectiondao->retrieve_all_course_section();

    $min_bid = 10;

    // adding all course+section to round 1 results 
    // (using min_bid = 10 and vacancies = maximum section size

    for($i=0; $i<count($cs); $i++){ // $cs = [[IS110, S1, 45] , [IS110, S2, 45], ... ]
        $course = $cs[$i][0];
        $section = $cs[$i][1];
        $max_size = $cs[$i][2];

        // add all possible course-sections into section_results table, default min_bid assume 10
        $sectionresultsdao->add_results($course, $section, 10, $max_size, 1);
    } 

    // obtains an array of bids
    // $temp_arr = [ "IS100, S1" => [ ['ben.ng.2009','11'], ['calvin.ng.2009','12'] ], ... ]
    $temp_arr = $biddao->retrieve_sort_bids(1);

    foreach($temp_arr as $course_section_str=>$array_of_bids){
        $course = explode(", ", $course_section_str)[0];
        $section = explode(", ", $course_section_str)[1];

        // get maximum capacity of each section
        $capacity = (int)($sectiondao->get_size($course, $section));

        // echo "Capacity: $capacity";
        // $no_of_bids = count($value);
        // echo "Number of bids: $no_";

        // sort $array_of_bids by descending bid amount
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

        if($capacity == count($array_of_bids)) { // if section is just nice full

            $clearing_price = $array_of_bids[$capacity-1][1];

            $num_successful_bids = 0;

            if($array_of_bids[$capacity-2][1] == $clearing_price) { // if more than 1 clearing price bid
                for($i=0; $i<count($array_of_bids); $i++) {
                    [$userid, $amount] = $array_of_bids[$i];
                    if($amount == $clearing_price) { // if this bid amount = clearing price, bid fails
                        $studentdao->add_balance($userid,$amount); // bid fail, so refund
                        $unsuccessfuldao->add_unsuccessful($userid, $amount, $course, $section, 1);
                        echo "
                        Course: $course<br>
                        Section: $section<br>
                        User: $userid<br>
                        Bid Amount: $$amount<br>
                        Clearing Type: Section Just Nice Full<br>
                        Status: <span id='fail'>Fail</span>, refunded $$amount<br>
                        ------------------------------------------------------------<br>                   
                        ";
                    } else {
                        $successfuldao->add_success($userid, $amount, $course, $section, 1); // bid success
                        echo "
                        Course: $course<br>
                        Section: $section<br>
                        User: $userid<br>
                        Bid Amount: $$amount<br>
                        Clearing Type: Section Just Nice Full<br>
                        Status: <span id='success'>Success</span><br>
                        ------------------------------------------------------------<br>                   
                        ";
                        $num_successful_bids++;
                    }
                }
            } else { // if only 1 clearing price bid, aka all bids succeed
                for($i=0; $i<count($array_of_bids); $i++) {
                    [$userid, $amount] = $array_of_bids[$i];
                    $successfuldao->add_success($userid, $amount, $course, $section, 1); // bid success
                    echo "
                    Course: $course<br>
                    Section: $section<br>
                    User: $userid<br>
                    Bid Amount: $$amount<br>
                    Clearing Type: Section Just Nice Full<br>
                    Status: <span id='success'>Success</span><br>
                    ------------------------------------------------------------<br>                   
                    ";
                    $num_successful_bids++;
                }
            }

            $vacancies = $capacity - $num_successful_bids;

        } elseif($capacity < count($array_of_bids)) { // if section is OVER booked

            $clearing_price = $array_of_bids[$capacity-1][1];

            $num_successful_bids = 0;

            // find out number of bids with amount = clearing price
            $number_of_clearing_price_bids = 0;
            foreach($array_of_bids as $idx => [$userid, $amount]) {
                if($amount == $clearing_price) {
                    $number_of_clearing_price_bids++;
                }
                if($number_of_clearing_price_bids > 1) {
                    break;
                }
            }

            if($number_of_clearing_price_bids > 1) { // if more than 1 bid at clearing price
                for($i=0; $i<count($array_of_bids); $i++) { // loop through all bids
                    [$userid, $amount] = $array_of_bids[$i];
                    if($amount <= $clearing_price) { // if this bid amount <= clearing price
                        $studentdao->add_balance($userid, $amount); // bid fail, so refund
                        $unsuccessfuldao->add_unsuccessful($userid, $amount, $course, $section, 1);
                        echo "
                        Course: $course<br>
                        Section: $section<br>
                        User: $userid<br>
                        Bid Amount: $$amount<br>
                        Clearing Type: Section Overbooked<br>
                        Status: <span id='fail'>Fail</span>, refunded $$amount<br>
                        ------------------------------------------------------------<br>                   
                        ";
                    } else { // if this bid amount > clearing price
                        $successfuldao->add_success($userid, $amount, $course, $section, 1); // bid succeed
                        echo "
                        Course: $course<br>
                        Section: $section<br>
                        User: $userid<br>
                        Bid Amount: $$amount<br>
                        Clearing Type: Section Overbooked<br>
                        Status: <span id='success'>Success</span><br>
                        ------------------------------------------------------------<br>                   
                        ";
                        $num_successful_bids++;
                    }
                }
            } else { // if only 1 bid at clearing price
                for($i=0; $i<$capacity; $i++) { // all bids within $capacity will be successful
                    [$userid, $amount] = $array_of_bids[$i];
                    $successfuldao->add_success($userid, $amount, $course, $section, 1); // bid succeed
                    echo "
                    Course: $course<br>
                    Section: $section<br>
                    User: $userid<br>
                    Bid Amount: $$amount<br>
                    Clearing Type: Section Overbooked<br>
                    Status: <span id='success'>Success</span><br>
                    ------------------------------------------------------------<br>                   
                    ";
                    $num_successful_bids++;
                }
                
                for($i=$capacity; $i<count($array_of_bids); $i++) { // all bids outside $capacity will fail
                    [$userid, $amount] = $array_of_bids[$i];
                    $studentdao->add_balance($userid, $amount); // bid fail, so refund
                    $unsuccessfuldao->add_unsuccessful($userid, $amount, $course, $section, 1);
                    echo "
                    Course: $course<br>
                    Section: $section<br>
                    User: $userid<br>
                    Bid Amount: $$amount<br>
                    Clearing Type: Section Overbooked<br>
                    Status: <span id='fail'>Fail</span>, refunded $$amount<br>
                    ------------------------------------------------------------<br>                   
                    ";
                }
            }

            $vacancies = $capacity - $num_successful_bids;

        } else { // if section is UNDER booked, aka everyone succeeded

            $clearing_price = $array_of_bids[count($array_of_bids)-1][1];

            foreach($array_of_bids as $idx => [$userid, $amount]) {
                $successfuldao->add_success($userid, $amount, $course, $section, 1);
                echo "
                Course: $course<br>
                Section: $section<br>
                User: $userid<br>
                Bid Amount: $$amount<br>
                Clearing Type: Section Underbooked<br>
                Status: <span id='success'>Success</span><br>
                ------------------------------------------------------------<br>                   
                ";
            }

            $vacancies = $capacity - count($array_of_bids);
        }

        $sectionresultsdao->update_results($course, $section, 10, $vacancies);
    }
}

?>