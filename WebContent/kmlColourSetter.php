<?php
    //both methods now set for editting/creating LineStyles. NB I assume there is only one LineStyle per kml
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using XML DOM
function setKmlColourDOM($url, $colour="ffff0000"){
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
function setKmlColourSimple($url, $colour="ff00ff00"){
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
//make sure colour String exists and is valid, and replace it with default otherwise
//could extend to alphaless colour
function enforceColourForm(&$colour){//colour in form aabbggrr
    $valid =TRUE;
    if($colour===null){
       $valid=FALSE;
    }elseif(strlen($colour) !=8){//if length != 8 invalid
         $valid=FALSE;
    }else{//check all in range 0-f
        $i=0;
        $colourArray= str_split(strtolower($colour));
        while(($valid)&&($i<8)){
            //if the single character string is a substring of "0123456789abcdef", then it is a valid part of a colour
            $valid =(strpos("0123456789abcdef",$colourArray[$i])!==FALSE);
            $i++;
        }
    }
    if(!($valid)){
        $colour="ff0000ff";
    }
}
/*
name kml, child([0])=place, childchild[1]=style (0=name,2=multigeom), child^3([0])=PolyStyle, child^4[0]=color
*/
    //below causes single access from address bar to trigger download as desired, but fails to work for KmlLayer - INVALID_REQUEST is thrown
    //However note that google can render it -> see https://maps.google.com/maps?q=http://178.62.107.109/testUpload/kmlColourSetter.php?url%3Dhttps://sturents.com/geo/greenwich-london-boro.kml&col=ffff00ff%22&output=classic&dg=feature
    // https://maps.google.com/maps?q= can be fed any accessible kml to see if it works.    
    //will assume page provides full link in form: filename.php?url=urlString&col=colourString
        //assume urlstring is full http//www...../name.kml
        $urlString = htmlspecialchars($_GET["url"]);
        $colourString = htmlspecialchars($_GET["col"]);
        if($colourString==null){
             $dummy = setKmlColourSimple($urlString); 
           // $dummy2 = setKmlColourDOM($urlString);
        }else{
            enforceColourForm($colourString);
            $dummy = setKmlColourSimple($urlString,$colourString); 
            //$dummy2 = setKmlColourDOM($urlString,$colourString);
        }
        //output as kml file, first trim $urlString to just the file name
        $handle = strrchr($urlString, "/");
        if($handle===FALSE){//no path - i.e. all we were given is the name
            $handle = $urlString;
        }else{//have removed path, now drop the "/"
            $handle = substr($handle,1);
        }
        //set to be a download, with the given url's name as the new file name
         header("Content-disposition: attachment; filename=".$handle);
         //Set file type to kml properly
         header('Content-type: application/vnd.google-earth.kml+xml');
        print $dummy->saveXML();
        //print $dummy2->saveXML();
?>
