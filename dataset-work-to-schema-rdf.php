<?php

// Trivial dump of works that cite DNA datasets
// just do DOI and name so we have something to work with,
// use other code to 

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

// simplify CSL to just those elements we need to create a formatted record
function simplify_csl($csl)
{
	$keys = array(
	
	
	);
	
	foreach ($csl as $k => $v)
	{
		switch ($k)
		{
			case 'title': 
			case 'type':
			case 'container-title': 
			case 'volume': 
			case 'issue': 
			case 'page': 
			case 'issued': 
			case 'DOI': 
			case 'URL':
				break;
		
			case 'author': 
				$n = count($csl->$k);
				
				$authors = array();
				for ($i = 0; $i < $n; $i++)
				{
					$author = new stdclass;
					
					if (isset($csl->$k[$i]->family))
					{
						$author->family = $csl->$k[$i]->family;
					}
					if (isset($csl->$k[$i]->given))
					{
						$author->given = $csl->$k[$i]->given;
					}
					if (isset($csl->$k[$i]->literal))
					{
						$author->literal = $csl->$k[$i]->literal;
					}
					
					$authors[] = $author;
				}
				
				$csl->author = $authors;
				break;
		
			default:
				unset($csl->$k);
				break;
		}
	}

	return $csl;
}

//----------------------------------------------------------------------------------------

$sql = 'SELECT DISTINCT cleaned.doi, work.csl from cleaned 
INNER JOIN work ON cleaned.doi = work.id
WHERE cleaned.accept LIKE "Y%"';

$data = db_get($sql);

$datasets = array();

foreach ($data as $row)
{
	$doi = $row->doi;
	
	$csl = json_decode($row->csl);
	
	if ($csl)
	{
		$name_done = false; // flag for later if we add support for multiple languages
		
		if (!$name_done)
		{
			$name = '';
			
			if (isset($csl->title))
			{
				if (is_array($csl->title))
				{
					if (count($csl->title) > 0)
					{
						$name = strip_tags($csl->title[0]);
					}
				}
				else
				{
					$name = strip_tags($csl->title);
				}
			}
			
			if ($name != '')
			{
				// clean
				$name = preg_replace('/\R/u', ' ', $name);	
				$name = preg_replace('/\s\s+/', ' ', $name);	
				$name = addcslashes($name, '"');
				
				$triple = array(
					'<https://doi.org/' . strtolower($doi) . '>',
					'<http://schema.org/name>',
					'"' . $name . '"',
				);
				
				$triples[] = $triple;
			}
			
			$cleaned_csl = simplify_csl($csl);
			$text = json_encode($cleaned_csl);
			
			$text = preg_replace('/\R/u', ' ', $text);	
			$text = preg_replace('/\s\s+/', ' ', $text);	
			
			$text = str_replace(['\\', '"'], ['\\\\', '\\"'], $text);
			
			
			// $encoding_id = '_:' . md5($csl_json);
			
			$triple = array(
				'<https://doi.org/' . strtolower($doi) . '>',
				'<http://schema.org/description>',
				'"' . $text . '"'
			);
			
			$triples[] = $triple;
		}
	}
}	

foreach ($triples as $triple)
{
	echo join(" ", $triple) . " .\n";
}	



?>

