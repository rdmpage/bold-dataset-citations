<?php

// Seed the table with the citation pairs

require_once(dirname(__FILE__) . '/sqlite.php');

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

foreach ($datasets as $k => $v)
{
	$dataset = 'https://doi.org/10.5883/' . strtolower($k);
	
	foreach ($v->doi as $doi)
	{
		$row = [$dataset, 'https://doi.org/' . strtolower($doi)];
		
		echo 'REPLACE INTO data_citation_corpus(dataset,publication) VALUES("' . $row[0] . '","' . $row[1] . '");' . "\n";
	}

}


?>

