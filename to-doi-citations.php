<?php

// Summarise results as list of publication DOIs that cite datasets

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

$dois = array();

foreach ($datasets as $k => $v)
{
	foreach ($v->doi as $doi)
	{
		$dois[] = strtolower($doi);
	}
}


print_r($dois);


?>
