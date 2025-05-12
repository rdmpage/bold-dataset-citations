<?php

// Try and automate checking by loading manually checked dataset and resolving URls to papers,
// then getting either HTML or DOI and lookign for dataset identifiers.

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
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$filename = 'results/manually-checked-latest.csv';

$keys = array('Dataset','Accept','url','doi','handle','dname','matched','score','notes');


$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		translate_quoted(','),
		translate_quoted('"') 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
			
			if (!isset($obj->Accept) && isset($obj->url))
			{
				if (0) // done
				{
					// PMC
					if (preg_match('/PMC/', $obj->url))
					{
						//print_r($obj);	
						
						$html = get($obj->url);
						
						//echo $html;
						
						if (preg_match('/' . $obj->Dataset . '/', $html, $m))
						{
							//print_r($m);
							$obj->Accept = 'Y';
						}
					}
				}

				if (0) // done
				{
					// springer
					if (preg_match('/springer/', $obj->url))
					{
						//print_r($obj);	
						
						$html = get($obj->url);
						
						//echo $html;
						
						if (preg_match('/' . $obj->Dataset . '/', $html, $m))
						{
							//print_r($m);
							$obj->Accept = 'Y';
						}
					}
				}

				if (0) // done
				{
					// nature
					if (preg_match('/nature/', $obj->url))
					{
						//print_r($obj);	
						
						$html = get($obj->url);
						
						//echo $html;
						
						if (preg_match('/' . $obj->Dataset . '/', $html, $m))
						{
							//print_r($m);
							$obj->Accept = 'Y';
						}
					}
				}

				if (0) // others
				{
					// 
					//if (preg_match('/peerj/', $obj->url))
					if (preg_match('/europepmc.org/', $obj->url))
					{
						//print_r($obj);	
						
						$html = get($obj->url);
						
						//echo $html;
						
						if (preg_match('/' . $obj->Dataset . '/', $html, $m))
						{
							//print_r($m);
							$obj->Accept = 'Y';
						}
					}
				}
				
				
				if (1) // done
				{
					//if (preg_match('/wiley/', $obj->url)) // works
					if (preg_match('/www.frontiersin.org/', $obj->url)) // fails
					{
						// print_r($obj);	
						
						$html_filename = 'temp.html';
											
						$command = "'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'"
							. "  --headless --disable-gpu --dump-dom"
							. " --user-agent=\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36\""
							. " '" . $obj->url . "'"
							. ' > ' . $html_filename;
						
						system($command);
						
						$html = file_get_contents($html_filename);
						//exit();
						
						//$html = get($obj->url);
						
						//echo $html;
						
						if (preg_match('/' . $obj->Dataset . '/', $html, $m))
						{
							print_r($m);
							$obj->Accept = 'Y';
						}
						
						if (preg_match('/<meta\s+name="citation_doi"\s+content="(?<doi>[^"]+)"/', $html, $m))
						{
							print_r($m);
							
							echo 'UPDATE cleaned SET doi="' . $m['doi'] . '" WHERE url="' . $obj->url . '";' . "\n";
							
						}
						
						unlink($html_filename);
					}
				}
				
				// PDF
				if (0) 
				{
					if (preg_match('/article\/view/', $obj->url)) // fails
					{
						// print_r($obj);	
						
						echo "-- $obj->url\n";
						
						$html = get($obj->url);
						
						$dom = HtmlDomParser::str_get_html($html);
						
						if ($dom)
						{	
								
							// meta
							foreach ($dom->find('meta') as $meta)
							{
								if (isset($meta->name))
								{
									//echo $meta->name . ' ' . $meta->content . "\n";
									
									if ($meta->name == 'citation_pdf_url')
									{
										$pdf_url = $meta->content;
										
										$pdf_filename = 'x.pdf';
										$txt_filename = 'x.txt';
										
										$pdf = get($pdf_url);
										
										file_put_contents($pdf_filename, $pdf);
									
										$command = 'pdftotext ' . $pdf_filename;
										system($command);
										
										$text = file_get_contents($txt_filename);
										
										if (preg_match('/' . $obj->Dataset . '/', $text, $m))
										{
											$obj->Accept = 'Y';
										}
									
										unlink($pdf_filename);
										unlink($txt_filename);
										
										
									}
									
									
								}
								
							}
						}
						
						
					}
				}				
				
				if (isset($obj->Accept))
				{
					echo 'UPDATE cleaned SET Accept="Y" WHERE Dataset="' . $obj->Dataset . '" AND url="' . $obj->url . '";' . "\n";
					//print_r($obj);
				}
			}
			
			$output = array();
			foreach ($keys as $k)
			{
				if (isset($obj->{$k}))
				{
					$output[] = $obj->{$k};
				}
				else
				{
					$output[] = '';
				}
				
				//echo join("\t", $output) . "\n";				
			}
		}
	}	
	$row_count++;
}
?>

