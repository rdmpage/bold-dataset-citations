<?php

// Seed the table with the citation pairs

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM cleaned ORDER BY Dataset';

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
		$datasets[$row->Dataset]->handle = [];
		$datasets[$row->Dataset]->urn = [];
	}
	
	if (isset($row->Accept) && preg_match('/^Y/', $row->Accept))
	{
		if (isset($row->url))
		{
			$datasets[$row->Dataset]->url[] = $row->url;
		}
		else
		{
			$datasets[$row->Dataset]->url[] = '';
		}
		
		if (isset($row->doi))
		{
			$datasets[$row->Dataset]->doi[] = strtolower($row->doi);
		}
		else
		{
			$datasets[$row->Dataset]->doi[] = '';
		}
		
		if (isset($row->handle))
		{
			$datasets[$row->Dataset]->handle[] = $row->handle;
		}
		else
		{
			$datasets[$row->Dataset]->handle[] = '';
		}

		if (isset($row->urn))
		{
			$datasets[$row->Dataset]->urn[] = $row->urn;
		}
		else
		{
			$datasets[$row->Dataset]->urn[] = '';
		}
		
	}
	

}

//print_r($datasets);

echo "dataset,doi,handle,urn,url\n";


foreach ($datasets as $k => $v)
{
	$dataset = 'https://doi.org/10.5883/' . strtolower($k);
	$dataset = $k;
	
	$num_rows = count($v->doi);
	
	for ($i = 0; $i < $num_rows; $i++)
	{
		$row = array(
			$dataset,
			$v->doi[$i],
			$v->handle[$i],
			$v->urn[$i],
			$v->url[$i],
		);
		
		echo join(",", $row) . "\n";
	}
	
	

}


?>

