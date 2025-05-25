<?php

// Get numbers of times datasets are cited

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

$values = array();

foreach ($datasets as $k => $v)
{
	$value = new stdclass;
	$value->type = "data";
	$value->doi = '10.5883/' . strtolower($k);
	$value->citations = count($v->doi);
	
	if ($value->citations > 0)
	{
		$values[] = $value;
	}

}


//print_r($values);

echo json_encode($values);


?>
