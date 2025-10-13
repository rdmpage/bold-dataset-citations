<?php

// Export core data about datasets in RDF

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM dataset';

$data = db_get($sql);


foreach ($data as $row)
{

	$triples = array();
	
	// it's a dataset
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>',
		'<http://schema.org/Dataset>'
	);
	
	$triples[] = $triple;

	// its is the same as record with a DOI
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://schema.org/sameAs>',
		'<https://doi.org/' . $row->doi . '>'
	);
	
	$triples[] = $triple;
	
	// it has a DOI as an identifier
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://schema.org/identifier>',
		'<https://doi.org/' . $row->doi . '>'
	);
	
	$triples[] = $triple;
	

	// it has a name
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://schema.org/name>',
		'"' . addcslashes($row->name, '"') . '"'
	);
	
	$triples[] = $triple;

	// print_r($triples);
	
	foreach ($triples as $triple)
	{
		echo join(" ", $triple) . " .\n";
	}
}

?>

