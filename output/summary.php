<?php

// Export matches for manual screening

require_once(dirname(dirname(__FILE__)) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM cleaned';

$data = db_get($sql);

$datasets = array();


foreach ($data as $row)
{
	//print_r($row);
	
	if (!isset($datasets[$row->Dataset]))
	{
		$datasets[$row->Dataset] = new stdclass;
		$datasets[$row->Dataset]->url = [];
		$datasets[$row->Dataset]->doi = [];
	}
	
	if (isset($row->Accept) && preg_match('/^Y/', $row->Accept))
	{
		if (isset($row->url))
		{
			$datasets[$row->Dataset]->url[] = $row->url;
		}
		
		if (isset($row->doi))
		{
			$datasets[$row->Dataset]->doi[] = $row->doi;
		}
	}
	

}

$count_doi = 0;
$count_link = 0;
$count = 0;

$not_found = array();

foreach ($datasets as $k => $dataset)
{
	$count++;
	
	$ok = false;
	
	if (count($dataset->url) > 0)
	{
		$count_link++;
		$ok = true;
	}
	
	if (count($dataset->doi) > 0)
	{
		$count_doi++;
		$ok = true;
	}
	
	if (!$ok)
	{
		$not_found[$k] = $dataset;
	}
	
}

echo "$count datasets of which $count_link have links and $count_doi have DOIs\n";

echo "Number of unmatched datasets is " . ($count - $count_link) . "\n";

//print_r($not_found);

?>
