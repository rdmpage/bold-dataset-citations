<?php

// Export citations of datasets in RDF. Citations are linked to the DOI for the dataset,
// consustent with the Data Citation Corpus

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
	
	// Filter by matches that are accepted
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

$triples = array();

foreach ($datasets as $k => $v)
{
	$triples = array();
	
	$num_rows = count($v->doi);
	
	for ($i = 0; $i < $num_rows; $i++)
	{
		if ($v->doi[$i] != '')
		{
			// work with DOI cites dataset DOI
			$triple = array(
				'<https://doi.org/' . strtolower($v->doi[$i]) . '>',
				'<http://schema.org/citation>',
				'<https://doi.org/10.5883/' . strtolower($k) . '>',
			);
			
			$triples[] = $triple;
		}
		elseif ($v->handle[$i] != '')
		{
			// work with Handle cites dataset DOI
			$triple = array(
				'<https://hdl.handle.net/' . strtolower($v->handle[$i]) . '>',
				'<http://schema.org/citation>',
				'<https://doi.org/10.5883/' . strtolower($k) . '>',
			);
			
			$triples[] = $triple;
		}
		elseif ($v->url[$i] != '')
		{
			// work with URL cites dataset DOI
			$triple = array(
				'<' . $v->url[$i] . '>',
				'<http://schema.org/citation>',
				'<https://doi.org/10.5883/' . strtolower($k) . '>',
			);
			
			$triples[] = $triple;
		}
		elseif ($v->urn[$i] != '')
		{
			// work with URN cites dataset DOI
			$triple = array(
				'<' . $v->urn[$i] . '>',
				'<http://schema.org/citation>',
				'<https://doi.org/10.5883/' . strtolower($k) . '>',
			);
			
			$triples[] = $triple;
		}
		
	}
	
	foreach ($triples as $triple)
	{
		echo join(" ", $triple) . " .\n";
	}	

}

?>
