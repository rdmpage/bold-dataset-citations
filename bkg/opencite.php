<?php

error_reporting(E_ALL);

if (file_exists(dirname(__FILE__) . '/env.php'))
{
	include 'env.php';
}

//----------------------------------------------------------------------------------------
function get($url)
{
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_HTTPHEADER => array (
	  	"Authorization: " .  getenv('OPENCITATIONS_API_KEY')
	  )
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	print_r($info);

	return $data;
}

//----------------------------------------------------------------------------------------

$dois=array(
//'10.3897/bdj.11.e100904',
//'10.1109/cibcb49929.2021.9562838',
//'10.1371/journal.pone.0277379',

'10.1108/jd-12-2013-0166'
);

// Seems broken

$count = 1;

foreach ($dois as $doi)
{
	$url = 'https://opencitations.net/index/api/v2/citations/doi:' . $doi;
	
	echo $url . "\n";
	
	$json = get($url);
	
	echo $json;
	
	$obj = json_decode($json);
	if ($obj)
	{
		print_r($obj);
	
		/*
		foreach ($obj as $item)
		{
			$keys = array();
			$values = array();
			
			$keys[] = 'oci';
			$values[] = "'" . $item->oci . "'";
						
			$keys[] = 'cited';
			$values[] = "'" . $item->cited . "'";
		
			$keys[] = 'citing';
			$values[] = "'" . $item->citing . "'";

			$keys[] = 'journal_sc';
			$values[] = "'" . $item->journal_sc . "'";
		
			$keys[] = 'creation';
			$values[] = "'" . $item->creation . "'";
			
			echo 'REPLACE INTO citation(' . join(",", $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";

		
		}
		*/
	
	}
	
	// Give server a break every 10 items
	if (($count++ % 5) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}
}

?>
