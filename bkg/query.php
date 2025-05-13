<?php

error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/shared.php');

putenv('PGGSSENCMODE=disable');

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
// retrieve data from database
function db_get($sql)
{
	global $config;
	global $db;
	
	$data = array();
	
	if ($config['database_engine'] == 'sqlite')
	{	
		$stmt = $db->query($sql);
	
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
	
			$item = new stdclass;
			
			$keys = array_keys($row);
		
			foreach ($keys as $k)
			{
				if ($row[$k] != '')
				{
					$item->{$k} = $row[$k];
				}
			}
		
			$data[] = $item;
		}	
	}
	
	if ($config['database_engine'] == 'postgres')
	{	
		$result = pg_query($db, $sql);
		
	
		while ($row = pg_fetch_assoc($result))
		{
			$item = new stdclass;
			
			$keys = array_keys($row);
		
			foreach ($keys as $k)
			{
				if ($row[$k] != '')
				{
					$item->{$k} = $row[$k];
				}
			}
		
			$data[] = $item;
		}
	}
	
	return $data;	
}


//----------------------------------------------------------------------------------------
// If $paginate is true we are getting sets of triples, set to false if getting counts
function query_to_sql ($query, $paginate = true)
{
	$terms = array();

	foreach ($query as $k => $v)
	{
		switch ($k)
		{
			case 's':
			case 'p':
			case 'o':
				$terms[] = "$k = '" . str_replace("'", "''", $v) . "'";
				break;
				
			default:
				break;
		}
	}
	
	if (count($terms) > 0)
	{	
		$sql = join(" AND ", $terms);
	}
	else
	{
		$sql = '';
	}
	
	if ($paginate)
	{
		
		// pagination
		if (isset($query['limit']))
		{
			$sql .= ' LIMIT ' . $query['limit'];	
		}	
		if (isset($query['skip']))
		{
			$sql .= ' OFFSET ' . $query['skip'];
		
		}
		
	}
	
	return $sql;
}

/*
SPO *
S.. *
.P. *
..O *
... *

SP. *
.PO *
S.O

*/

//----------------------------------------------------------------------------------------
// get triple [S,P,O]
function querySPO($query)
{	
	$result = array();
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($query);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}

	$result->count = match_query_count('spo', $query);
	
	return $result;
}

//----------------------------------------------------------------------------------------
// get triple [S,P]
function querySPX($query)
{	
	$result = array();
	
	$q = array('s' => $query['s'], 'p' => $query['p']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}

	$result->count = match_query_count('spo', $q);
	
	return $result;
}

//----------------------------------------------------------------------------------------
// get triple [S]
function querySXX($query)
{	
	$result = array();
	
	$q = array('s' => $query['s']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);
	
	return $result;
}

//----------------------------------------------------------------------------------------
// get triple [P]
function queryXPX($query)
{	
	$result = array();
	
	$q = array('p' => $query['p']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);

	return $result;
}

//----------------------------------------------------------------------------------------
// get triple [P,0]
function queryXPO($query)
{	
	$result = array();
	
	$q = array('p' => $query['p'], 'o' => $query['o']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);

	return $result;
}

//----------------------------------------------------------------------------------------
// // get triple [O]
function queryXXO($query)
{	
	$result = array();
	
	$q = array('o' => $query['o']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);	
	
	return $result;
}

//----------------------------------------------------------------------------------------
// // get triple [S,O]
function querySXO($query)
{	
	$result = array();
	
	$q = array('s' => $query['s'], 'o' => $query['o']);
	
	$sql = 'SELECT * FROM spo WHERE ' . query_to_sql ($q);
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);	
	
	return $result;
}

//----------------------------------------------------------------------------------------
// get triple []
function queryXXX($query)
{	
	$result = array();
	
	$q = array();
	
	$sql = 'SELECT * FROM spo';
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$result = new stdclass;
	$result->triples = array();
	
	
	foreach ($data as $row)
	{
		$triple = array($row->s, $row->p, $row->o);
		$result->triples[] = $triple;
	}
	
	$result->count = match_query_count('spo', $q);
	
	return $result;
}

//----------------------------------------------------------------------------------------
// Total number of triples that match pattern
function match_query_count($table, $query)
{	
	$count = 0;
	
	$sql = 'SELECT COUNT(*) AS c FROM ' . $table;
	
	if (count($query) > 0)
	{
		$sql .= ' WHERE ' . query_to_sql ($query, false);
	}
	
	//echo $sql . "\n";
	
	$data = db_get($sql);
	
	$count = $data[0]->c;
	
	return $count;
}

//----------------------------------------------------------------------------------------
function query_result_to_triples ($result)
{
	$data = array();

	foreach ($result->triples as $triple)
	{
		$row = array();
		$row[] = curie_to_uri($triple[0]);
		$row[] = curie_to_uri($triple[1]);
	
		if (preg_match('/^</', $triple[2]))
		{					
			$row[] = $triple[2];
		}
		elseif (preg_match('/^"/', $triple[2]))
		{
			// literal
			$literal = $triple[2];
			
			// Ensure quotes are escaped
			$literal = preg_replace('/^"/', '', $literal);
			$literal = preg_replace('/"$/', '', $literal);
			$literal = preg_replace('/"/', '\"', $literal);
			
			$literal = '"' . $literal . '"';
			
			$row[] = $literal;
		}
		else
		{
			$row[] = curie_to_uri($triple[2]);
		}
		
		$data[] = $row;
	}

	return $data;
}

//----------------------------------------------------------------------------------------

if (0)
{
	$query = array(
	's' => 'orcid:0000-0003-1134-7918',
	'p' => 'a',
	'o' => ':Person',
	'limit' => 10
	);
	
	/*
	$result = querySPO($query);
	
	print_r($result);
	
	$result = querySPX($query);
	
	print_r($result);
	
	$result = querySXX($query);
	
	print_r($result);
	
	$result = queryXPX($query);
	
	print_r($result);
	*/
	
	$result = queryXXO($query);
	
	// print_r($result);
	
	// $result = queryXXX($query);
	
	print_r($result);
}

if (0)
{
	$query = array(
	's' => 'doi:10.5281/zenodo.1000555',
	'p' => ':identifier',
	'limit' => 10
	);
	

	
	$result = querySPX($query);
	
	print_r($result);
}

if (0)
{
	$query = array();
		
	$result = queryXXX($query);
	
	print_r($result);
}

?>
