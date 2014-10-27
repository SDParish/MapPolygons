<?php
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using XML DOM
function setKmlColour($url, $colour="ffe89e40"){
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($url);
	//now access color field, and set to $colour
	//below line can be read as xmlDoc-> kml tag->Placemark tag -> Style tag -> PolyStyle tag ->color tag->entry in color tag (is set to $colour) 
	$xmlDoc->documentElement->childNodes[0]->childNodes[1]->childNodes[0]->childNodes[0]->nodeValue = $colour;

	return $xmlDoc;
}
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using SimpleXML
function setKmlColourSimple($url, $colour="ffe89e40"){
	$xml=simplexml_load_file($url);
	//now access color field, and set to $colour
	//below line may only work for immediate child
	$xml->color = $colour;
	//this might work instead
	//$xml->Placemark->Style->PolyStyle->color= $colour;
	return $xml;
}
/*
name kml, child([0])=place, childchild[1]=style (0=name,2=multigeom), child^3([0])=PolyStyle, child^4[0]=color
*/
?>