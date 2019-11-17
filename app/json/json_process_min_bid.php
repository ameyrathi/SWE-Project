<?php
require_once '../include/common.php';

// this function should only be used in Round 2

function process_min_bid($course, $section) {
    $successfuldao = new SuccessfulDAO();
    $biddao = new BidDAO();
    $sectionresultsdao = new SectionResultsDAO();


    $current_min_bid = $sectionresultsdao->get_min_bid($course, $section);
    $current_available_seats = $sectionresultsdao->get_available_seats($course, $section);

    $round1_successful_bids = $successfuldao->retrieve_sort_this_section_bids($course, $section, 1);
    $round2_pending_bids = $biddao->retrieve_sort_this_section_bids($course, $section, 2);

    $num_round2_pending_bids = count($round2_pending_bids);

    // echo "<h2>Round 2 list of bids:</h2>";
    // var_dump($round2_pending_bids);

    // echo "<br>";

    // echo "Round 2 bids: $num_round2_pending_bids<br>";
    // echo "Round 2 available seats: $current_available_seats<br>";

    // if no pending bids in round 2
    if($num_round2_pending_bids == 0) {
        return $current_min_bid;
    }

    // if 0 vacancies in course
    if($current_available_seats == 0) {
        foreach($round2_pending_bids as [$this_userid, $this_amount]) {
            $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
        }
        return $current_min_bid;
        
    // Case 1: If there are less than N bids for the section (where N is the total available seats)
    // The minimum bid value remains the same
    } elseif($num_round2_pending_bids < $current_available_seats) { // if num of round 2 bids < round 2 available seats
        foreach($round2_pending_bids as [$this_userid, $this_amount]) {
            $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
        }

        return $current_min_bid;

    } elseif($num_round2_pending_bids == $current_available_seats) { // if num of round 2 bids = round 2 available seats
        $clearing_price = $round2_pending_bids[$num_round2_pending_bids-1][1];

        $num_clearing_price_bids = 0;

        foreach($round2_pending_bids as [$this_userid, $this_amount]) {
            if($this_amount == $clearing_price) {
                $num_clearing_price_bids++;
            }
            if($num_clearing_price_bids > 1) {
                break;
            }
        }

        if($num_clearing_price_bids == 1) { // only 1 bid at clearing price, so all succeed
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
            }
        } else { // if more than 1 bid at clearing price, aka all clearing price bids fail
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
            }
        }

        // return new minimum bid
        if($current_min_bid < ($clearing_price+1)) {
            $new_min_bid = $clearing_price + 1;
            $sectionresultsdao->update_min_bid($course, $section, $new_min_bid);
            return $new_min_bid;
        } else {
            return $current_min_bid;
        }

    } else { // if num of round 2 bids > round 2 available seats (but vacancies > 0)
        $clearing_price = $round2_pending_bids[$current_available_seats-1][1];

        $num_clearing_price_bids = 0;

        foreach($round2_pending_bids as [$this_userid, $this_amount]) {
            if($this_amount == $clearing_price) {
                $num_clearing_price_bids++;
            }
            if($num_clearing_price_bids > 1) {
                break;
            }
        }

        if($num_clearing_price_bids == 1) { // only 1 bid at clearing price, so all within capacity succeed
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                if($this_amount >= $clearing_price) {
                    $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                } else {
                    $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
                }
            }
        } else { // if more than 1 bid at clearing price, aka all clearing price bids fail
            foreach($round2_pending_bids as [$this_userid, $this_amount]) {
                if($this_amount > $clearing_price) {
                    $biddao->update_round2_bid_status($this_userid, $course, "Pending, successful");
                } else {
                    $biddao->update_round2_bid_status($this_userid, $course, "Pending, fail");
                }
            }
        }

        if($current_min_bid < ($clearing_price+1)) {
            $new_min_bid = $clearing_price + 1;
            $sectionresultsdao->update_min_bid($course, $section, $new_min_bid);
            return $new_min_bid;
        } else {
            return $current_min_bid;
        }
    }
}

// echo "New Min Bid: " . process_min_bid("IS100", "S1");

?>