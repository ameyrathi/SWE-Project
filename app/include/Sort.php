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

	function filename($a, $b)
	{
		return ($a > $b) ? -1 : 1;
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
		if($a["code"] == $b["code"]){
			if ($a["userid"] > $b["userid"]){
				return 1;
			}
		}
		return $a["code"] > $b["code"] ? 1 : -1;
	}

	function bid($a, $b){
		
		if($a["code"] == $b["code"]){
			if ($a["section"] > $b["section"]){
				return 1;
			}
			else{
				if($a["section"] == $b["section"]){
					if ($a["amount"] > $b["amount"]){
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

		return $a["code"] > $b["code"] ? 1 : -1;
	}

}

?>