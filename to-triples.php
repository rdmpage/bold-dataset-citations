<?php

// Summarise results as triples work cites data

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

$triples = array();

foreach ($datasets as $k => $v)
{
	$o = 'https://doi.org/10.5883/' . strtolower($k);
	$p = 'http://schema.org/citation';
	
	foreach ($v->doi as $doi)
	{
		$s = 'https://doi.org/' . strtolower($doi);
	}
	
	$triples[] = array($s, $p, $o);

}


print_r($triples);


?>
