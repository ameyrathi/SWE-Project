<?php

function close_bidding_round2(){
    $biddao = new BidDAO();
    $sectiondao = new SectionDAO();
    $sectionresultsdao = new SectionResultsDAO();
    $biddingrounddao = new BiddingRoundDAO();
    $successfuldao = new SuccessfulDAO();
    $unsuccessfuldao = new UnsuccessfulDAO();
    $studentdao = new StudentDAO();

    $round2_bids_with_status = $biddao->retrieve_sort_bids(2);

    foreach($round2_bids_with_status as $course_section_str => $bid_list) {
        [$this_course, $this_section] = explode(", ", $course_section_str);

        foreach($bid_list as [$this_userid, $this_amount, $this_status]) {
            if($this_status == "Pending, successful") {
                $successfuldao->add_success($this_userid, $this_amount, $this_course, $this_section, 2);
                echo "
                Course: $this_course<br>
                Section: $this_section<br>
                User: $this_userid<br>
                Bid Amount: $$this_amount<br>
                Status: <span id='success'>Success</span><br>
                ------------------------------------------------------------<br>                   
                ";
            } elseif($this_status == "Pending, fail") {
                $studentdao->add_balance($this_userid, $this_amount); // bid fail, so refund
                $unsuccessfuldao->add_unsuccessful($this_userid, $this_amount, $this_course, $this_section, 2);
                echo "
                Course: $this_course<br>
                Section: $this_section<br>
                User: $this_userid<br>
                Bid Amount: $$this_amount<br>
                Status: <span id='fail'>Fail</span>, refunded $$this_amount<br>
                ------------------------------------------------------------<br>                   
                ";
            }
        }
    }
}

?>