<?php

// Extract data we need from publications

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM work';

$data = db_get($sql);


foreach ($data as $row)
{
	$json = $row->csl;
	
	$csl = json_decode($json);
	
	if (!$csl)
	{
		continue;
	}
	
	$work = new stdclass;
	
	$publication = 'https://doi.org/' . strtolower($csl->DOI);
	
	/*
	// title
	if (isset($csl->title))
	{
		if (is_array($csl->title))
		{
			$work->title = strip_tags($csl->title[0]);
		}
		else
		{
			$work->title = strip_tags($csl->title);
		}
	}
	*/

	// journal
	if (isset($csl->{'container-title'}))
	{
		if (is_array($csl->{'container-title'}))
		{
			if (count($csl->{'container-title'}) > 0)
			{
				$work->journal = $csl->{'container-title'}[0];
			}
		}
		else
		{
			$work->journal = $csl->{'container-title'};
		}
	}
	
	// date 
	if (isset($csl->issued))
	{
		$date = '';
		$d = $csl->issued->{'date-parts'}[0];

		// sanity check
		if (is_numeric($d[0]))
		{
			if ( count($d) > 0 ) $year = $d[0] ;
			if ( count($d) > 1 ) $month = preg_replace ( '/^0+(..)$/' , '$1' , '00'.$d[1] ) ;
			if ( count($d) > 2 ) $day = preg_replace ( '/^0+(..)$/' , '$1' , '00'.$d[2] ) ;
			if ( isset($month) and isset($day) ) $date = "$year-$month-$day";
			else if ( isset($month) ) $date = "$year-$month-00";
			else if ( isset($year) ) $date = "$year-00-00";
			
			$work->publishedDate = $date . 'T00:00:00+00:00';
		}				
	}		
	
	// publisher/institution
	if (!isset($work->publisher))
	{
		if (isset($csl->institution))
		{
			$work->publisher = $csl->institution[0]->name;
		}	
	
	}
	if (!isset($work->publisher))
	{
		if (isset($csl->publisher))
		{
			$work->publisher = $csl->publisher;
		}	
	}
	
	$terms = array();
	foreach ($work as $k => $v)
	{
		$terms[] = $k . '="' .  str_replace('"', '""', $v) . '"';
	}
	
	echo 'UPDATE data_citation_corpus SET ' . join(',', $terms)	. ' WHERE publication = "' . $publication . '";' . "\n";
}

?>

