<?php

// Upload batch of triples into SPO table in Postgres

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/shared.php');

$db = null;

if ($config['database_engine'] == 'sqlite')
{
	$db = new PDO($config['pdo']);
}

if ($config['database_engine'] == 'postgres')
{
	require_once(dirname(__FILE__) . '/pg.php');
}

//----------------------------------------------------------------------------------------
function sqlite_query($db, $sql)
{
	$stmt = $db->prepare($sql);
	
	if (!$stmt)
	{
		echo "\nPDO::errorInfo():\n";
		print_r($db->errorInfo());
	}	
	
	$stmt->execute();
	
	if (!$stmt)
	{
		echo "\nPDO::errorInfo():\n";
		print_r($db->errorInfo());
	}	
}

//----------------------------------------------------------------------------------------


$column_map = array(
0 => 's',
1 => 'p',
2 => 'o'
);

//$filename = 'test.nq';
$filename = 'triples.nt';
//$filename = 'z.nt';
//$filename = 'image.nt';
$filename = 'date.nt';
$filename = 'test.nt';
$filename = '1.nt';


$batchsize = 10000;
$batch = array();

$row_startTime = microtime(true);


$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	$quads = array();
	
	if (preg_match('/(?<s>_:[A-Za-z0-9]+|<[^>]+>)\s+(?<p><[^>]+>)\s+(?<o>(?<bnode>_:[A-Za-z0-9]+)|(?<uri><[^>]+>)|(?<literal>"[^\"]+"([^\"]+")*(?<language>@\w+)?)(?<type>\^\^<[^>]+>)?)(\s*(?<q><[^>]+>))?\s+\.\s*/', $line, $m))
	{
		// print_r($m);
		
		$quads = array(
			$m['s'],
			$m['p']			
		);
		
		if (isset($m['literal']))
		{
			$quads[] = $m['literal']; // ignore data type because that's how we roll
		}
		elseif (isset($m['uri']))
		{			
			$quads[]  = $m['uri'];
		}
		else
		{			
			$quads[]  = $m['bnode'];
		}		
		
		if (isset($m['q']))
		{
			$quads[] = $m['q'];
		}
	}
	
	if (count($quads) > 0)
	{		
		// print_r($quads);
		
		foreach ($quads as &$uri)
		{
			$uri = uri2curie($uri);
		}
		
		//print_r($quads);
		
		// SQL		
		$values = array();
		foreach ($quads as $k => $v)
		{
			$values[] = "'" . str_replace("'", "''", $v) . "'";
		}
		
		$batch[] = '(' . join(",", $values) . ')';
		
		if (count($batch) > $batchsize)
		{
			$row_endTime = microtime(true);
			$row_executionTime = $row_endTime - $row_startTime;
			$formattedTime = number_format($row_executionTime, 3, '.', '');
			echo "Took " . $formattedTime . " seconds to process " . count($batch) . " rows.\n";
		
			// print_r($batch);
			
			$sql = 'INSERT INTO spo (s,p,o) VALUES' . "\n";
			$sql .= join(",\n", $batch);
			$sql .= " ON CONFLICT DO NOTHING;";
		
			$startTime = microtime(true);
	
			echo "Uploading " . count($batch) . " rows to database\n";
			
			switch ($config['database_engine'])
			{
				case 'sqlite':
					sqlite_query($db, $sql);
					break;
			
				case 'postgres':
				default:
					$result = pg_query($db, $sql);
					break;
			}
				
			$endTime = microtime(true);
			$executionTime = $endTime - $startTime;
			$formattedTime = number_format($executionTime, 3, '.', '');
			echo "Execution time: " . $formattedTime . " seconds\n\n";
			
			$batch = array();
			$row_startTime = microtime(true);
			
			
		}	
	}
}	

if (count($batch) > 0)
{
	$row_endTime = microtime(true);
	$row_executionTime = $row_endTime - $row_startTime;
	$formattedTime = number_format($row_executionTime, 3, '.', '');
	echo "Took " . $formattedTime . " seconds to process " . count($batch) . " rows.\n";

	//print_r($batch);
	
	$sql = 'INSERT INTO spo (s,p,o) VALUES' . "\n";
	$sql .= join(",\n", $batch);
	$sql .= " ON CONFLICT DO NOTHING;";

	$startTime = microtime(true);

	echo "Uploading " . count($batch) . " rows to database\n";
	
	switch ($config['database_engine'])
	{
		case 'sqlite':
			sqlite_query($db, $sql);
			break;
	
		case 'postgres':
		default:
			$result = pg_query($db, $sql);
			break;
	}

	$endTime = microtime(true);
	$executionTime = $endTime - $startTime;
	$formattedTime = number_format($executionTime, 3, '.', '');
	echo "Execution time: " . $formattedTime . " seconds\n\n";
	
	$batch = array();
	$row_startTime = microtime(true);
}	


?>
