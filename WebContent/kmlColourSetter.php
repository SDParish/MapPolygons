<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <meta charset=\"utf-8\" />
        <title>Some page</title>
   </head>
    <body>
        <p>dummy</p>
        <p>
<?php
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using XML DOM
function setKmlColourDOM($url, $colour="ffe89e40"){
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($url);
	//now access color field, and set to $colour
	$colorTags = $xmlDoc->getElementsByTagName("color");
    $colorTags->item(0)->nodeValue = $colour;
    return $xmlDoc;
}
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using SimpleXML
function setKmlColourSimple($url, $colour="ffe89e40"){
	$xml=simplexml_load_file($url);
	//now access color field, and set to $colour
	$xml->Placemark->Style->PolyStyle->color= $colour;
	return $xml;
}
/*
name kml, child([0])=place, childchild[1]=style (0=name,2=multigeom), child^3([0])=PolyStyle, child^4[0]=color
*/

        $dummy = setKmlColourSimple("farnham-bourne-ward.kml");
       // print_r($dummy);
         print $dummy->saveXML();
       echo "</p><p>dummy2</p><p>";
        $dummy2 = setKmlColourDOM("farnham-bourne-ward.kml");
        print $dummy2->getElementsByTagName("color")->item(0)->nodeValue;  
        print "<br/>";  
        print $dummy2->saveXML();
        echo "<br/> end";
?>
</p>
    </body>
</html>