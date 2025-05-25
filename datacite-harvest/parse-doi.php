<?php

require_once (dirname(dirname(__FILE__)) . '/sqlite.php');

$basedir = 'json';

$files = scandir($basedir);

//$files = array('ds-afropict.json');

foreach ($files as $filename)
{
	if (preg_match('/\.json/', $filename))
	{
		echo $filename . "\n";
	
		$record = new stdclass;
	
		$record->id = str_replace('.json', '', $filename);
		$record->doi = '10.5883/' . $record->id;
		$record->id = strtoupper($record->id);
	
		$json = file_get_contents($basedir . '/' . $filename);
		
		//echo $json . "\n";
		
		$obj = json_decode($json);
		
		//print_r($obj);
		
		$record->name =  $obj->data->attributes->titles[0]->title;
		
		$record->url =  $obj->data->attributes->url;
		
		$record->json = $json;
		
		//print_r($record);
		
		$sql = obj_to_sql($record, 'dataset');
		
		db_put($sql);
	}
}

?>
