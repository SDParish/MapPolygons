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

        $dummy = setKmlColourSimple("guildford.kml","ffffffff");
       // print_r($dummy);
       //below line worked on localhost, not on remote server -> probably write permissions
        if( $dummy->saveXML("temp.kml")){
            echo "success";
            header("Content-disposition: attachment; filename=farnham-bourne-ward.kml");
            header("Content-type: application/kml");
            readfile("temp.kml");
        }else{
            echo  $dummy->saveXML();
        };
      //unlink("temp.kml");
       // $dummy2 = setKmlColourDOM("farnham-bourne-ward.kml");
       //print $dummy2->saveXML();
?>
