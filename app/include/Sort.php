<?php
class Sort {
	function file($a, $b)
	{
		if($a["file"] == $b["file"]){
			if ($a["line"] > $b["line"]){
				return 1;
			}
		}
		return $a["file"] > $b["file"] ? 1 : -1;
	}

	function sort_it($list,$sorttype)
	{
		usort($list,array($this,$sorttype));
		return $list;
	}

	function course($a, $b){
		return $a["course"] > $b["course"] ? 1 : -1;
	}

	function student($a, $b){
		return $a["userid"] > $b["userid"] ? 1 : -1;
	}

	function section($a, $b){
		if($a["course"] == $b["course"]){
			if ($a["section"] > $b["section"]){
				return 1;
			}
		}
		return $a["course"] > $b["course"] ? 1 : -1;
	}

	function prerequisite($a, $b){
		if($a["course"] == $b["course"]){
			if ($a["prerequisite"] > $b["prerequisite"]){
				return 1;
			}
		}
		return $a["course"] > $b["course"] ? 1 : -1;
	}

	function course_completed($a, $b){
		if($a["course"] == $b["course"]){
			if ($a["userid"] > $b["userid"]){
				return 1;
			}
		}
		return $a["course"] > $b["course"] ? 1 : -1;
	}

	function bid($a, $b){
		if($a["course"] == $b["course"]){
			if ($a["section"] > $b["section"]){
				return 1;
			}
			else{
				if($a["section"] == $b["section"]){
					if ($a["amount"] < $b["amount"]){
						return 1;
					}
					else{
						if ($a["amount"] == $b["amount"]){
							if ($a["userid"] > $b["userid"]){
								return 1;
							}
						}
					}
				}
			}
		}
		return $a["course"] > $b["course"] ? 1 : -1;
	}

	function bid_dump($a, $b){
		if($a[1] == $b[1]){
			if($a[0] > $b[0]){
				return 1;
			}
		}
		return ($a[1] < $b[1]) ? 1 : -1;
	}

	function section_dump($a, $b){
		return ($a[0] > $b[0]) ? 1 : -1;
    }
    
    function search_bid_course($a, $b) {
        return ($a[0] > $b[0]) ? 1 : -1;
	}
	
	function bid_status($a, $b){
		if($a["amount"] == $b["amount"]){
			if ($a["userid"] > $b["userid"]){
				return 1;
			}
		}
		return $a["amount"] < $b["amount"] ? 1 : -1;
	}

	function min_bid($a, $b){
		return $a[1] < $b[1] ? 1 : -1;
    }
    
    function common_validation($a, $b) {
        $a_field = explode(" ", $a)[1];
        $b_field = explode(" ", $b)[1];

        return $a_field > $b_field ? 1 : -1;
    }

}

?>