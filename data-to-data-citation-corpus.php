<?php

// Extract data from datasets

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM dataset';

$data = db_get($sql);


foreach ($data as $row)
{
	$json = $row->json;
	
	$obj = json_decode($json);
	
	$dataset_id = 'https://doi.org/' . strtolower($obj->data->attributes->doi);
	
	$dataset = new stdclass;
	
	$dataset->title = $obj->data->attributes->titles[0]->title;
	$dataset->repository = $obj->data->attributes->publisher;
	
	$subjects = array();
	foreach ($obj->data->attributes->subjects as $subject)
	{
		$subjects[] = strtolower($subject->subject);
	}
	if (count($subjects) > 0)
	{
		$dataset->subjects = join("; ", $subjects);
	}
	
	// none found for BOLD datasets
	$affiliations = array();
	foreach ($obj->data->attributes->creators as $creator)
	{
		if (isset($creator->affiliation))
		{
			foreach ($creator->affiliation as $affiliation)
			{
				$affiliations[] = $affiliation;
			}
		}		
	}
	if (count($affiliations) > 0)
	{
		$dataset->affiliations = join("; ", $affiliations);
	}
	
	// none found for BOLD datasets
	$funders = array();
	foreach ($obj->data->attributes->fundingReferences as $funder)
	{
		$funders[] = $funder;
	}
	if (count($funders) > 0)
	{
		$dataset->funders = join("; ", $funders);
	}
	
	//print_r($dataset);
	
	$terms = array();
	foreach ($dataset as $k => $v)
	{
		$terms[] = $k . '="' .  str_replace('"', '""', $v) . '"';
	}
	
	echo 'UPDATE data_citation_corpus SET ' . join(',', $terms)	. ' WHERE dataset = "' . $dataset_id . '";' . "\n";
}

?>

