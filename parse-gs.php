<?php

require_once(dirname(__FILE__) . '/HtmlDomParser.php');
use Sunra\PhpSimple\HtmlDomParser;

$basedir = 'html';

$files = array('ds-260324.html');
//$files = array('ds-ffliesrc.html');
//$files = array('ds-goncolom.html');

foreach ($files as $filename)
{
	if (preg_match('/\.html/', $filename))
	{
		$html = file_get_contents($basedir . '/' . $filename);
		
		// echo $html;
		
		$dom = HtmlDomParser::str_get_html($html);
		
		if ($dom)
		{	
			foreach ($dom->find('div[class=gs_r gs_or gs_scl]') as $gs)
			{
	
				// metadata
				foreach ($gs->find('div[class=gs_ri]') as $gs_ri)
				{
					foreach ($gs_ri->find('h3[class=gs_rt]') as $h3)
					{
						// title
						echo $h3->plaintext . "\n";
					
						// link
						foreach ($h3->find('a') as $a)
						{
							echo $a->href . "\n";
						}
					}
		
					// abstract
					foreach ($gs_ri->find('div[class=gs_rs]') as $div)
					{
						echo $div->plaintext . "\n";
					}

				}		
				
				// PDF
				foreach ($gs->find('div[class=gs_ggs gs_fl] a') as $a)
				{
					echo "PDF=" . str_replace('&amp;', '&', $a->href) . "\n";
				}				
				
				
				echo "----\n\n";
			}
		}		
	}
}

?>
