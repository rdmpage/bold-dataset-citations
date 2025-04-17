<?php

require_once(dirname(__FILE__) . '/sqlite.php');

require_once(dirname(__FILE__) . '/HtmlDomParser.php');
use Sunra\PhpSimple\HtmlDomParser;

$basedir = 'html-title';

$files = scandir($basedir);

$files = array('ds-260324.html');
$files = array('ds-ffliesrc.html');
$files = array('ds-goncolom.html');
$files = array('ds-adelo1.html');
//$files = array('ds-afropict.html');

//$files = array('ds-ctenscia.html');
//$files = array('ds-agromit.html');
$files = array('ds-bolma.html');

foreach ($files as $filename)
{
	if (preg_match('/\.html/', $filename))
	{
		$id = strtoupper(str_replace('.html', '', $filename));
	
		$html = file_get_contents($basedir . '/' . $filename);
		
		// echo $html;
		
		$dom = HtmlDomParser::str_get_html($html);
		
		if ($dom)
		{	
			$pattern = 'gs_r gs_or gs_scl gs_fmar';
			$pattern = 'gs_r gs_or gs_scl';
			
			foreach ($dom->find('div[class=' . $pattern . ']') as $gs)
			{
				$citation = new stdclass;

				$citation->source = 'googleScholarTitle';
				
				$citation->dataset_id = strtoupper(str_replace('.html', '', $filename));				
				
				// metadata
				foreach ($gs->find('div[class=gs_ri]') as $gs_ri)
				{
					foreach ($gs_ri->find('h3[class=gs_rt]') as $h3)
					{
						// title
						$citation->name = $h3->plaintext;
						$citation->name = preg_replace('/^(\[[A-Z]+\]\s+)+/', '', $citation->name);
					
						// link
						foreach ($h3->find('a') as $a)
						{
							$citation->url = $a->href;
							
							if (!isset($citation->doi))
							{
								if (preg_match('/\/doi\/((abs|full)\/)?(?<doi>10\.\d+\/[^\/]+)/', $a->href, $m))
								{
									$citation->doi = $m['doi'];
								}
							}
							
							if (!isset($citation->doi))
							{
								if (preg_match('/\/(?<doi>10\.\d+\/[^\/]+)/', $a->href, $m))
								{
									$citation->doi = $m['doi'];
								}
							}
							
							// clean DOI
							if (isset($citation->doi))
							{
								$citation->doi = preg_replace('/\.abstract.*/', '', $citation->doi);
							}							
							
						}
					}
		
					// abstract
					foreach ($gs_ri->find('div[class=gs_rs]') as $div)
					{
						$citation->text = trim($div->plaintext);
						
						if (preg_match('/' . $id . '/i', $div->plaintext))
						{
							$citation->matched = true;
						}
						
						if ($citation->text == '')
						{
							unset($citation->text);
						}
					}

				}		
				
				// PDF
				foreach ($gs->find('div[class=gs_ggs gs_fl] a') as $a)
				{
					$citation->pdf = str_replace('&amp;', '&', $a->href);
				}	
				
				$citation->id = md5(json_encode($citation));
				
				print_r($citation);
				
				$sql = obj_to_sql($citation, 'citation');
				
				echo $sql . "\n";
				
				db_put($sql);
				
				echo "----\n\n";
			}
		}		
	}
}

?>
