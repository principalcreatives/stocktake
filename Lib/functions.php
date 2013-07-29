<?php

/*
 * REMOVE DUPLICATES
 */
function cleanList($list){
	$row_id = "";
	$new = array();
	
	if(!$list) return;
	
	foreach ($list as $key => $value){
		if($row_id == $list[$key]["RowID"])
			continue;
			
		array_push($new, $list[$key]);
		$row_id = $list[$key]["RowID"];
	}
	
	return $new;
}