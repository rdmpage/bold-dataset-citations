<?php

// Extract DOi from URL

require_once(dirname(dirname(__FILE__)) . '/sqlite.php');


//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publication WHERE url IS NOT NULL AND doi IS NULL';

$data = db_get($sql);

foreach ($data as $row)
{
	$url = $row->url;
	
	echo "-- $url\n";
	
	$doi = '';
	
	if ($doi == '')
	{
		if (preg_match('/\/doi\/((abs|full)\/)?(?<doi>10\.\d+\/[^\/]+)/', $url, $m))
		{
			$doi = $m['doi'];
		}
	}
	
	if ($doi == '')
	{
		if (preg_match('/\/(?<doi>10\.\d+\/[^\/]+)/', $url, $m))
		{
			$doi = $m['doi'];
		}
	}
	
	// clean DOI
	if ($doi == '')
	{
		$doi = preg_replace('/\.abstract.*/', '', $doi);
	}							
	
	
	if ($doi != '')
	{
		$sql = 'UPDATE publication SET doi="' . $doi . '" WHERE url="' . $url . '";';
		
		echo $sql . "\n";
		
		db_put($sql);
	
	}
	
	

}

?>
