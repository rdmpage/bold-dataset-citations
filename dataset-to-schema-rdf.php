<?php

// Export core data about datasets in RDF using schema.org vocabulary

require_once(dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM dataset';

$data = db_get($sql);

foreach ($data as $row)
{

	$triples = array();
	
	// it is a dataset
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . strtoupper($row->id) . '>',
		'<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>',
		'<http://schema.org/Dataset>'
	);
	
	$triples[] = $triple;

	// it is the same as DataCite record with a DOI
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://schema.org/sameAs>',
		'<https://doi.org/' . $row->doi . '>'
	);
	
	$triples[] = $triple;
	
	// it has a DOI as an identifier
	// handle this as a simple link rather than property value (cf. ORCID)
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
	
	// for ease of querying add the BOLD dataset id as alternateName,
	// hence we can easily look for "DS-SATYP1"
	$triple = array(
		'<https://portal.boldsystems.org/recordset/' . $row->id . '>',
		'<http://schema.org/alternateName>',
		'"' . strtoupper($row->id) . '"'
	);
	
	$triples[] = $triple;
	
	// print_r($triples);
	
	echo "# " . $row->id . "\n";
	
	foreach ($triples as $triple)
	{
		echo join(" ", $triple) . " .\n";
	}
	
	echo "\n";
}

?>
