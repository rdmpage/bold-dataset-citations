<?php

//----------------------------------------------------------------------------------------
function uri2curie($uri)
{
	$uri = preg_replace('/^</', '', $uri);
	$uri = preg_replace('/>$/', '', $uri);

	$curie = $uri;
	
	if ($uri == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type')
	{
		$curie = 'a';
	} 
	else
	{
		// Ensure we only handle cases where URL starts with http because some URLs may be literals
		if (preg_match('/^https?:\/\/(dx\.)?doi.org\/(?<value>.*)/', $uri, $m))
		{
			$curie = 'doi:' . $m['value'];
		}
		else
		{
			if (preg_match('/^https?:\/\/(([^\/]+\/)+([^\#]+\#)?)(?<value>.*)$/', $uri, $m))
			{
				// print_r($m);
			
				switch ($m[1])
				{
					case 'schema.org/':
						$curie = ':' . $m['value'];
						break;
		
					case 'doi.org/':
						$curie = 'doi:' . $m['value'];
						break;
	
					case 'orcid.org/':
						$curie = 'orcid:' . $m['value'];
						break;
		
					case 'pubmed.ncbi.nlm.nih.gov/':
						$curie = 'pubmed:' . $m['value'];
						break;
				
					default:
						// no prefix available
						break;
				}
			}
		}
	}
		
	return $curie;
}

//----------------------------------------------------------------------------------------
function curie_to_uri($curie)
{
	$uri = $curie;
	
	
	if (preg_match('/^(https?|urn):/', $curie))
	{
		$uri = '<' . $curie . '>';
		return $uri;
	}
	
	// a
	if (preg_match('/^a$/', $curie))
	{
		$uri = '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>';
	}
	
	// schema is default
	if (preg_match('/^:(.*)/', $curie, $m))
	{
		$uri = '<http://schema.org/' . $m[1] . '>';
	}
	
	//echo __LINE__ . " uri=$uri\n";
	
	if (preg_match('/^([a-z]+):([^\/].*)/', $curie, $m))
	{
		
	
		$prefix = '';
		
		switch ($m[1])
		{
			case 'doi':
				$prefix = 'https://doi.org/';
				break;

			case 'orcid':
				$prefix = 'https://orcid.org/';
				break;

			case 'pubmed':
				$prefix = 'https://pubmed.ncbi.nlm.nih.gov/';
				break;

			case 'schema':
				$prefix = 'http://schema.org/';
				break;
				
			default:
				break;
				
		
		}
		$uri = '<' . $prefix . $m[2] . '>';
	}
	
	//echo __LINE__ . " uri=$uri\n";
		
	return $uri;
}


?>
