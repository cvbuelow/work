<?php

Class SearchEngine
{
	var $perpage = 10;	//number of records per page
	var $results;				//mysql result resource
	var $num_pages;			//number of pages of records
	var $page_num;			//current page being shown
	var $num_results;		//number or records returned by search
	
	function search($terms)
	{
		global $database;
				
		//query parameters
		$extra_weight_fields = array(
			array("field" => "public_notice_type", "weight" => 3),
			array("field" => "public_notice_city", "weight" => 3),
			array("field" => "public_notice_county", "weight" => 2),
			array("field" => "public_notice_zip_code", "weight" => 2),
			array("field" => "public_notice_address", "weight" => 1)
		);
		$fulltext_search_fields = array("public_notice_name","public_notice_type","public_notice_address","public_notice_city","public_notice_county","public_notice_zip_code","public_notice_attorney","public_notice_file_number");
		$like_search_fields = array("public_notice_name","public_notice_type","public_notice_address","public_notice_city","public_notice_zip_code","public_notice_attorney","public_notice_file_number");
		$words = explode(" ", $terms['q']);
		
		//build query for weighted keyword search
		$query = "SELECT SQL_CALC_FOUND_ROWS public_notice_test . *, 
					DATE_FORMAT(public_notice_first_date_published, '%M %e, %Y') AS public_notice_first_date_published_MeY, 
					DATE_FORMAT(public_notice_first_date_published, '%c/%e/%Y') AS public_notice_first_date_published_ceY,
					DATE_FORMAT(public_notice_last_date_published, '%c/%e/%Y') AS public_notice_last_date_published_ceY,
					DATE_FORMAT(public_notice_published_sale_date, '%c/%e/%Y') AS public_notice_published_sale_date_ceY,
					MATCH (" . implode(",", $fulltext_search_fields) . ") AGAINST (" . quote(" +" . implode(" +", $words)) . " IN BOOLEAN MODE)";
		foreach($extra_weight_fields as $extra_weight_field)
		{
			foreach($words AS $word)
				$query .= " + (" . $extra_weight_field["field"] . " LIKE " . quote($word) . ") * " . $extra_weight_field["weight"];
		}
		
		$query .= " AS score
			FROM public_notice_test
			WHERE MATCH (" . implode(",", $fulltext_search_fields) . ") AGAINST (" . quote(" +" . implode(" +", $words)) . " IN BOOLEAN MODE)";
		
		//advanced search
		
		if(isset($terms["foreclosures"]))
		{
			$typesql[] = "public_notice_type = 'Cancelled Foreclosures - No Post'";
			$typesql[] = "public_notice_type = 'FNF Foreclosures'";
			$typesql[] = "public_notice_type = 'Foreclosures'";
			$typesql[] = "public_notice_type = 'Mortgage'";
			$typesql[] = "public_notice_type = 'Foreclosures - No Post'";
		}
		if(isset($terms["probates"]))
		{
			$typesql[] = "public_notice_type = 'Probate'";
		}
		if(isset($terms["vehicleAbandonment"]))
		{
			$typesql[] = "public_notice_type = 'Car Abandonment'";
		}
		if(isset($terms["other"]))
		{
			$typesql[] = "public_notice_type = 'Circuit Court'";
			$typesql[] = "public_notice_type = 'Miscellaneous'";
			$typesql[] = "public_notice_type = 'Orders'";
			$typesql[] = "public_notice_type = 'Other'";
		}
		if(isset($terms["foreclosurePrevention"]))
		{
			$typesql[] = "public_notice_type = 'Mortgage Workout'";
		}
		
		if(is_array($typesql))
			$query .= " AND (" . implode(" OR ", $typesql) . ")";
		
		if($terms["county"] !== "all")
			$query .= " AND public_notice_county = " . quote($terms["county"]);
		
		if(isset($terms["emailupdate"]))	//just get records for today and ignore dates set in advanced search
		{
			$query .= " AND public_notice_first_date_published = CURDATE()";
		}
		else
		{
			foreach(array("first_date_published", "last_date_published", "published_sale_date") as $datefield)
			{
				if($terms[$datefield] !== "mm/dd/yyyy" && !empty($terms[$datefield]))
				{
					if($terms[$datefield . "_thru"] !== "mm/dd/yyyy" && !empty($terms[$datefield . "_thru"]))
					{
						$query .= " AND public_notice_$datefield >= " . quote(strToDate($terms[$datefield]));	//range
						$query .= " AND public_notice_$datefield <= " . quote(strToDate($terms[$datefield . "_thru"]));
					}
					else
						$query .= " AND public_notice_$datefield = " . quote(strToDate($terms[$datefield]));	//single date
				}
			}
		}
		
		$query .= " ORDER BY public_notice_first_date_published DESC, score DESC";
		
		//pagination
		$this->page_num = empty($terms["page_num"]) ? 1 : $terms["page_num"];
		$perpage = $this->perpage;
		$startindex = ($this->page_num - 1) * $this->perpage;
		if($terms["action"] == "search")
			$query .= " LIMIT $startindex, " . $this->perpage;
		
		$this->results = $database->database_query($query);
		$countresults = $database->database_fetch_assoc($database->database_query("SELECT FOUND_ROWS() AS num_rows"));
		$this->num_pages = ceil($countresults["num_rows"] / $this->perpage);
		$this->num_results = $countresults["num_rows"];
	}
	
	function make_excel()
	{
		global $database;
		$excelfile = "<table>";		//just output an html table. excel will convert it.
		$excelfile .= "<tr><th>Notice Number</th><th>First Published</th><th>Last Published</th><th>Published Sale Date</th><th>County</th><th>City</th><th>Address</th><th>Zip</th><th>Name</th></tr>";
		while($row = $database->database_fetch_assoc($this->results))
		{
			$excelrow = array($row["public_notice_file_number"],$row["public_notice_first_date_published_ceY"],$row["public_notice_last_date_published_ceY"],$row["public_notice_published_sale_date_ceY"],$row["public_notice_county"],$row["public_notice_city"],$row["public_notice_address"],$row["public_notice_zip_code"],$row["public_notice_name"]);
			$excelfile .= "<tr><td>" . implode("</td><td>",$excelrow) . "</td></tr>";
		}
		$excelfile .= "</table>";
		return $excelfile;
	}
	
	function get_image($width, $height)
	{
		global $imgpath, $public_notice_file_number, $public_notice_internal_id;
		
		//see if there's an image for this notice
		if(file_exists(_NOTICE_IMAGES . $public_notice_file_number . ".jpg"))
			$filename = $public_notice_file_number . ".jpg";
		elseif(file_exists(_NOTICE_IMAGES . $public_notice_file_number . ".JPG"))
			$filename = $public_notice_file_number . ".JPG";
		elseif(file_exists(_NOTICE_IMAGES . $public_notice_internal_id . ".jpg"))
			$filename = $public_notice_internal_id . ".jpg";
		elseif(file_exists(_NOTICE_IMAGES . $public_notice_internal_id . ".JPG"))
			$filename = $public_notice_internal_id . ".JPG";
		else
			$filename = "";
			
		return empty($filename) ? "" : "<img class=\"result-thumb\" src=\"$imgpath/picserve.php?type=notice&width=$width&height=$height&file=" . $filename . "\" alt\"\" />";
	}	
}

?>