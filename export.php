<?php

// Export matches for manual screening

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT dataset.id, citation.url, publication.doi, dataset.name AS dname, citation.name, citation.matched, citation.score FROM citation 
INNER JOIN dataset ON citation.dataset_id = dataset.id
INNER JOIN publication ON citation.url = publication.url
ORDER BY dataset.id;';

$data = db_get($sql);

$datasets = array();

foreach ($data as $row)
{
	//print_r($row);
	
	if (!isset($datasets[$row->id]))
	{
		$datasets[$row->id] = array();
	}
	$datasets[$row->id][] = $row;

}

//print_r($datasets);

// html
if (0)
{
	echo '<html>
	<head>
	<style>
		body {
			font-family:sans-serif;
			margin:1em;
		}
	
		tr:nth-child(even) {background-color: #f2f2f2;}
		
		td {
			word-break: break-all;
			padding:0.5em;
			font-size:1em;
		}
	</style>
	</head>	
	<body>
	<table width="100%">';
	
	$keys = array('url', 'doi', 'dname', 'matched', 'score');
	
	echo '<tr><th>Dataset</th><th>';
	echo join('</th><th>', $keys);
	echo '</th</tr>';
	
	
	foreach ($datasets as $id => $matches)
	{
		echo '<tr>';
		
		echo '<td style="word-break:keep-all;" rowspan="' . count($matches) . '">';
		echo $id;
		echo '</td>';
		
		foreach ($matches as $match)
		{
			
			foreach ($keys as $k)
			{
				echo '<td>';
				
				if (isset($match->{$k}))
				{
					switch ($k)
					{
						case 'url':
							echo '<a href="' . $match->{$k} . '" target="_new">' . htmlspecialchars($match->{$k}) . '</a>';
							break;
						
						case 'doi':
							echo '<a href="https://doi.org/' . $match->{$k} . '" target="_new">' . $match->{$k} . '</a>';
							break;
							
						case 'matched':
							echo '<span style="padding:1em;background-color:rgb(0,200,10);">' . $match->{$k} . '</span>';
							break;					
							
						case 'score':
							if ($match->{$k} > 0.8)
							{
								echo '<span style="padding:1em;background-color:rgb(0,200,10);">' . $match->{$k} . '</span>';
							}
							else
							{
								echo $match->{$k};
							}
							break;
											
						default:
							echo htmlspecialchars($match->{$k});
							break;
					}
				}
				echo '</td>';
			}
			
			echo '</tr>';
			
		}
	
	}
	
	
	echo '</table>
	</body>
	</html>';
}

if (1)
{
	
	$keys = array('url', 'doi', 'dname', 'matched', 'score');
	
	echo "Dataset\tAccept\t" . join("\t", $keys) . "\n";
	
	foreach ($datasets as $id => $matches)
	{
		foreach ($matches as $match)
		{
			echo $id . "\t";
			
			foreach ($keys as $k)
			{
				echo "\t";
				
				if (isset($match->{$k}))
				{
					switch ($k)
					{	
						default:
							echo  $match->{$k};
							break;
					}
				}
			}
			
			echo "\n";
			
		}
	}

}
?>
