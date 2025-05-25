<?php

// Get identifier(s) from URL for a work

require_once(dirname(dirname(__FILE__)) . '/sqlite.php');

require_once(dirname(dirname(__FILE__)) . '/HtmlDomParser.php');
use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $accept = "text/html")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	// Cookies 
	//curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies.txt');
	//curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies.txt');	
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Accept: " . $accept,
		"Accept-Language: en-us",
		"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 	
		));
	
	$response = curl_exec($ch);
	
	
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		//die($errorText);
		return "";
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	//print_r($info);
		
	curl_close($ch);
	
	return $response;
}

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM citation WHERE url IS NOT NULL';

$data = db_get($sql);

foreach ($data as $row)
{
	$url = $row->url;
	
	$go = true;

	if ($go)
	{
		echo "\nURL=$url\n\n";
	
		$html = get($url);
		
		// echo $html;
		
		//$html = substr($html, 0, 32000);
				
		// meta tags, need to convert to linked data for a subset of tags that
		// will add value
		$dom = HtmlDomParser::str_get_html($html);
		
		if ($dom)
		{	
			$source = new stdclass;
			$source->type = 'work';
			$source->url = $url;
			
			if (preg_match('/handle\/(\d+\/\d+)/', $url, $m))
			{
				$source->handle = $m[1];
			}
				
			// meta
			foreach ($dom->find('meta') as $meta)
			{
				if (isset($meta->name))
				{
					echo $meta->name . ' ' . $meta->content . "\n";
				}

				if (isset($meta->property))
				{
					echo $meta->property . ' ' . $meta->content . "\n";
				}
				
				switch ($meta->name)
				{			
					case 'citation_doi':
					case 'bepress_citation_doi':
						$source->doi = $meta->content;
						break;
		
					case 'citation_title':
					case 'bepress_citation_title':
					case 'DC.title':
						$source->title = $meta->content;
						break;
						
					case 'citation_pdf_url':
					case 'bepress_citation_pdf_url':
					case 'citation_additional_version_url': // https://www.revistas.usp.br
						$source->pdf = $meta->content;
						$source->pdf = str_replace('&amp;', '&', $source->pdf );
						
						// journal-specific hacks
						// https://www1.montpellier.inra.fr/CBGP/acarologia/article.php?id=4710
						$source->pdf = str_replace('inrae.r', 'inrae.fr', $source->pdf);
						break;
						
					case 'citation_abstract_html_url':
						if (preg_match('/https?:\/\/(hdl\.)?handle.net\/(?<handle>.*)/', $meta->content, $m))
						{
							$source->handle = $m['handle'];
						}
						break;
						
					case 'dc.Identifier': // TandF untested
						if (preg_match('/(https?:\/\/(dx\.)?doi.org\/)?(?<doi>10\.\d+.*)/', $meta->content, $m))
						{
							$source->doi = $m['doi'];
						}
						break;	
						
					case 'DC.identifier':	
						//echo "|" . $meta->content . "|\n";				
						if (preg_match('/info:doi\/(?<doi>10\.\d+.*)/', $meta->content, $m))
						{
							$source->doi = $m['doi'];
						}
						if (preg_match('/nbn-resolving.(de|org)\/(?<id>.*)/', $meta->content, $m))
						{
							$source->nbn = $m['id'];
						}
						
						break;	

						// potentially a preprint
					case 'DC.relation':	
						if (preg_match('/(https?:\/\/(dx\.)?doi.org\/)?(?<doi>10\.\d+.*)/', $meta->content, $m))
						{
							if (!isset($source->doi))
							{
								$source->related_doi = $m['doi'];
								$source->type = 'preprint';
							}
						}
						break;	
					
					case 'eprints.document_url':
						if (preg_match('/\.pdf$/', $meta->content))
						{
							$source->pdf = $meta->content;
						}
						break;
						
					case 'citation_dissertation_name':
					case 'citation_dissertation_institution':
						$source->type = 'thesis';
						break;
						
					case 'DC.type':
						if ($meta->content == 'masterThesis')
						{
							$source->type = 'thesis';
						}
						if ($meta->content == 'doctoralThesis')
						{
							$source->type = 'thesis';
						}
						break;
																		
					default:
						break;
				}
				
				// e.g. https://catalog.lib.kyushu-u.ac.jp/opac_detail_md/?lang=1&amode=MD100000&bibid=2398
				// key info such as DOI and access rights are in table
				switch ($meta->property)
				{				
					case 'citation_doi':
						$source->doi = $meta->content;
						break;
						
					case 'citation_pdf_url':
						$source->pdf = $meta->content;
						$source->pdf = str_replace('&amp;', '&', $source->url);
						break;						
						
					default:
						break;
				}
							
			}
			
			// identifiers in body of document
			
			foreach ($dom->find('div[class=ep_block_doi] a') as $a)
			{
				if (preg_match('/(https?:\/\/(dx\.)?doi.org\/)?(?<doi>10\.\d+.*)/', $a->href, $m))
				{
					if (!isset($source->doi))
					{
						$source->doi = $m['doi'];
					}
				}
			}
			
			if (isset($source->doi))
			{
				$source->doi = strtolower($source->doi);
			}
			
			print_r($source);
			
			$pub_sql = obj_to_sql($source, 'publication');
			
			echo $pub_sql . "\n";
			
			db_put($pub_sql);
			
		}

	}
}

?>
