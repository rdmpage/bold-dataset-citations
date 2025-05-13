<?php

$basedir = dirname(dirname(__FILE__)) . '/json';

$files = scandir($basedir);

$files = array('ds-afropict.json');

/*
$basedir = dirname(__FILE__);
$files = array('10.5061-dryad.rjdfn2z9b.json');
*/

foreach ($files as $filename)
{
	if (preg_match('/\.json/', $filename))
	{
		$json = file_get_contents($basedir . '/' . $filename);
		$obj = json_decode($json);
		
		print_r($obj);
		
		$quads = array();
		
		
		if (isset($obj->data->attributes))
		{
			$s = 'https://doi.org/' . $obj->data->attributes->doi;
			
			// title
			if (isset($obj->data->attributes->titles))
			{
				$p = 'http://schema.org/name';
				$o = '"' . addcslashes($obj->data->attributes->titles[0]->title, '"') . '"';
				
				$quads[] = array($s, $p, $o);
			}
			
			// type
			if (isset($obj->data->attributes->types))
			{
				foreach ($obj->data->attributes->types as $k => $v)
				{
					if ($k == "schemaOrg")
					{
						$quads[] = array(
							$s, 
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
							'http://schema.org/' . $v);						
					}
				}
			}
						
			// creators
			if (isset($obj->data->attributes->creators))
			{
				$count = 0;
				foreach ($obj->data->attributes->creators as $creator)
				{
					// default is a fragment identifier
					$count++;					
					$creator_id = $s . '#' . $count;
					
					// do we have an ORCID?
					if (isset($creator->nameIdentifiers))
					{
						foreach ($creator->nameIdentifiers as $nameIdentifier)
						{
							if (isset($nameIdentifier->nameIdentifierScheme) && $nameIdentifier->nameIdentifierScheme == "ORCID")
							{
								$creator_id = $nameIdentifier->nameIdentifier;
							}
						}
					}					
					
					$quads[] = array(
						$s, 
						'http://schema.org/creator',
						$creator_id);
						
					if (isset($creator->name))
					{
						$o = '"' . addcslashes($creator->name, '"') . '"';
						
						$quads[] = array(
							$creator_id, 
							'http://schema.org/name',
							$o);						
					}
					
					if (isset($creator->givenName))
					{
						$o = '"' . addcslashes($creator->givenName, '"') . '"';
						
						$quads[] = array(
							$creator_id, 
							'http://schema.org/givenName',
							$o);						
					}

					if (isset($creator->familyName))
					{
						$o = '"' . addcslashes($creator->familyName, '"') . '"';
						
						$quads[] = array(
							$creator_id, 
							'http://schema.org/familyName',
							$o);						
					}
					
					
				}
			}
		
		}
		
		print_r($quads);
		

	}
}

?>
