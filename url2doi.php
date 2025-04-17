<?php

// Get identifier(s) from URL for a work

require_once(dirname(__FILE__) . '/HtmlDomParser.php');
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


$urls = array(
//'https://pmc.ncbi.nlm.nih.gov/articles/PMC7015952/',
//'https://cir.nii.ac.jp/crid/1880865118193612416',

//'https://www.tandfonline.com/doi/abs/10.1080/00379271.2023.2215216',
//'https://onlinelibrary.wiley.com/doi/abs/10.1002/ece3.4194',

//'https://journals.plos.org/plosone/article?id=10.1371/journal.pone.0222119',

//'https://www.ingentaconnect.com/content/miiz/actac/2013/00000015/00000002/art00002',

//'https://www.nature.com/articles/s41598-021-83631-0',
//'https://cdnsciencepub.com/doi/abs/10.1139/gen-2016-0009',
//'https://repositorio.uasb.edu.ec/handle/10644/9873',

//'https://www.mdpi.com/1424-2818/11/1/2',
//'https://brill.com/view/journals/tve/156/2-3/article-p191_6.xml',

//'https://edoc.ub.uni-muenchen.de/27000/',

//'https://nbn-resolving.org/urn:nbn:de:bvb:19-270008',

//'https://pmc.ncbi.nlm.nih.gov/articles/PMC8599385/',

//'https://europeanjournaloftaxonomy.eu/index.php/ejt/article/view/532',

//'https://www.jstor.org/stable/24861908',

//'https://oulurepo.oulu.fi/handle/10024/43702',

//'https://www.kmae-journal.org/articles/kmae/abs/2020/01/kmae190147/kmae190147.html',

//'https://www.biotaxa.org/Zootaxa/article/view/zootaxa.4109.3.7/20444',
//'https://shilap.org/revista/article/view/651',

//'https://revistas.zamorano.edu/CEIBA/article/download/1224/1167',

//'https://link.springer.com/article/10.1007/s43236-024-00799-0',

//'https://www.scielo.org.mx/scielo.php?pid=S2007-42982024000100026&amp;script=sci_arttext',
//'https://www.scielo.org.mx/scielo.php?pid=S2007-42982024000100026&script=sci_arttext',

//'https://www.biotaxa.org/AEMNP/article/view/60813', // PDF link broken, actual PDF has DOI

//'https://hal.science/hal-02883398/',

//'https://atrium.lib.uoguelph.ca/bitstreams/4f62fcab-b435-4cbe-ae4f-28cfd81e86f2/download', // pdf
//'https://atrium.lib.uoguelph.ca/items/e6d1649f-b335-4652-93fb-f93ce62c7a5b',

//'http://colposdigital.colpos.mx:8080/jspui/handle/10521/4356',

//'http://revistaecosistemas.net/index.php/ecosistemas/article/view/1179', // failed but article exists
//'http://ojs3.uefs.br/index.php/sociobiology/article/view/1044',

//'https://dialnet.unirioja.es/servlet/articulo?codigo=9024079', // NO DOI on site, but link to PDF with DOI
//'https://doi.org/10.57065/shilap.462',

//'https://atrium.lib.uoguelph.ca/items/dbb9131f-faac-440f-893c-45d34b373e41',

//'https://repositorio.uasb.edu.ec/handle/10644/9873',
//'https://oulurepo.oulu.fi/handle/10024/43702',
//'https://repositorium.uminho.pt/handle/1822/90956',

'https://www.biorxiv.org/content/10.1101/2024.01.25.577180.abstract',

'https://contributions-to-entomology.arphahub.com/article_preview.php?id=127094',

);

foreach ($urls as $url)
{
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
			
			print_r($source);
			
		}

	}
}

?>
