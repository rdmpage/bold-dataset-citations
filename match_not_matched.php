<?php

// Attempt to match unmatched datasets

require_once (dirname(__FILE__) . '/sqlite.php');
require_once (dirname(__FILE__) . '/compare.php');



//----------------------------------------------------------------------------------------
function get($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
	$response = curl_exec($ch);
	
	
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		//die($errorText);
		return "";
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	//print_r($info);
		
	curl_close($ch);
	
	return $response;
}


//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM not_matched ORDER BY Dataset';

$data = db_get($sql);

$datasets = array();

$count = 1;

foreach ($data as $row)
{
	//print_r($row);
	
	// match to crossref
	
	echo "-- " . $row->id . "\n";
	echo "-- " . $row->name . "\n";
	
	$url = 'https://api.crossref.org/works?query=' . urlencode($row->name) . '&filter=type%3Ajournal-article';

	//echo $url . "\n";

	$json = get($url);

	//echo $json;

	$obj = json_decode($json);
	
	if ($obj)
	{
		$hit = $obj->message->items[0];
		
		$title = $hit->title;
		if (is_array($title))
		{
			$title = $hit->title[0];
		}
		
		echo "-- $title\n";
		echo "-- " . $obj->message->items[0]->DOI . "\n";
		
		// does it look like a good match?
		$title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$title = strip_tags($title);
		
		
		$result = compare_common_subsequence($row->name, $title, false);						
		
		echo "-- [" . $result->normalised[0] . ', ' . $result->normalised[1] ."]\n";
		
		$matched = false;

		if ($result->normalised[1] > 0.80)
		{
			// one string is almost an exact substring of the other
			if ($result->normalised[0] > 0.75)
			{
				// and the shorter string matches a good chunk of the bigger string
				$matched = true;	
			}
		}	
		
		if ($matched)	
		{
			$values = array();
			
			$values[] = '"' . $row->id . '"';
			$values[] = '"Y crossref"';
			$values[] = '"' . $obj->message->items[0]->DOI . '"';
			$values[] = '"' . str_replace('"', '""', $row->name) . '"';
			$values[] = '"Matched database title to work using CrossRef"';
		
		
			echo "INSERT INTO cleaned (Dataset, Accept, doi, dname, notes) VALUES("
			 . join(",", $values) . ");\n";
			
			
			
			
		}
		
		
	}
	
	if (($count++ % 10) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}
	
	
	echo "\n\n";
	
	

}

?>
