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
	
	$dataset_key = strtoupper($row->Dataset);
	
	if (!isset($datasets[$dataset_key]))
	{
		$datasets[$dataset_key] = new stdclass;
		$datasets[$dataset_key]->id = $dataset_key;
		$datasets[$dataset_key]->doi = '10.5883/' . strtolower($dataset_key);
		
		if (isset($row->dname))
		{
			$datasets[$dataset_key]->name = $row->dname;
		}
		
		$datasets[$dataset_key]->cited_by = [];
	}
	
	if (isset($row->Accept) && preg_match('/^Y/', $row->Accept))
	{
		$cited_by = new stdclass;
		
		$cited_by_pid = '';
		
		if (isset($row->doi))
		{
			$cited_by->doi = strtolower($row->doi);
			$cited_by_pid = $cited_by->doi;
		}
	
		if (isset($row->url))
		{
			$cited_by->url = $row->url;
			
			if ($cited_by_pid == '')
			{
				$cited_by_pid = $cited_by->url;
			}
		}
		
		if (isset($row->handle))
		{
			$cited_by->handle = $row->handle;
			
			if ($cited_by_pid == '')
			{
				$cited_by_pid = $cited_by->handle;
			}
		}
		
		$datasets[$dataset_key]->cited_by[$cited_by_pid] = $cited_by;

	}
	

}




print_r($datasets);


?>
