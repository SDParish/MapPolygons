<?php
    //both methods now set for editting/creating LineStyles. NB I assume there is only one LineStyle per kml
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using XML DOM
function setKmlColourDOM($url, $colour="ffe89e40"){
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($url);
	//now access color field, and set to $colour
    $LineStyleTags = $xmlDoc->getElementsByTagName("LineStyle");
	 if($LineStyleTags->length==0){
	    //add linestyle with desired color
        $LineStyle=$xmlDoc->createElement ("LineStyle");
	    $LineStyle->appendChild($xmlDoc->createElement ("color",$colour));
	    $LineStyle->appendChild($xmlDoc->createElement ("colorMode","normal"));
	    $LineStyle->appendChild($xmlDoc->createElement ("width","5"));//may be changed
        //As assuming polygon, not adding gx:outerColor [kml:color], or gx:outerWidth [float],also not adding gx:physicalWidth [float], or gx:labelVisibility [bool] 
	   $xmlDoc->getElementsByTagName("Style")->item(0)->appendChild($LineStyle);
    }else{
	   $colorTags = $LineStyleTags->item(0)->getElementsByTagName("color");
	   $colorTags->item(0)->nodeValue = $colour;
    }   
     return $xmlDoc;
}
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using SimpleXML
function setKmlColourSimple($url, $colour="ffe89e40"){
	$xml=simplexml_load_file($url);
	//now access color field, and set to $colour
	$style =$xml->Placemark->Style;
    $flag = FALSE;
    foreach ($style->children() as $child)
    {
        $flag =($child->getName()=="LineStyle")||$flag ;
    }
   if($flag){
        $xml->Placemark->Style->LineStyle->color= $colour;
    }else{
        //if no linestyle
        $xml->Placemark->Style->addChild("LineStyle");
        $xml->Placemark->Style->LineStyle->addChild("color",$colour); 
        $xml->Placemark->Style->LineStyle->addChild("colorMode","normal");
        $xml->Placemark->Style->LineStyle->addChild("width","5");//may be changed
        //As assuming polygon, not adding gx:outerColor [kml:color], or gx:outerWidth [float],also not adding gx:physicalWidth [float], or gx:labelVisibility [bool]     
    }
    return $xml;
}
/*
name kml, child([0])=place, childchild[1]=style (0=name,2=multigeom), child^3([0])=PolyStyle, child^4[0]=color
*/

        $dummy = setKmlColourSimple("farnham-bourne-ward.kml","ffffffff");
        print_r($dummy);
        echo "============\n";
       $dummy = setKmlColourSimple("temp.kml","ff000000");
        print_r($dummy);
       //header("Content-Type: text/plain");
       //$dummy->saveXML(/*"temp.kml"*/);
      // $dummy->
      //header("Content-disposition: attachment; filename=farnham-bourne-ward.kml");
//header("Content-type: application/xml");
      //readfile("temp.kml");
      //unlink("temp.kml");
       echo "============\n";
        $dummy2 = setKmlColourDOM("farnham-bourne-ward.kml","ff00ff00");
       print $dummy2->saveXML();
       echo "============\n";
       $dummy2 = setKmlColourDOM("temp.kml","ff0000ff");
       print $dummy2->saveXML();
?>
