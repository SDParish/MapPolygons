<?php
    //both methods now set for editting/creating LineStyles. NB I assume there is only one LineStyle per kml
//takes the kml file at $urlString, and sets its fields to make those specified, and returns the new kml file using XML DOM
function setKmlColourDOM(){
	global $urlString,$lcolourString,$fcolourString,$lmode,$fmode,$fill,$outline,$width;
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($urlString);
    if(($lcolourString!=null)||($lmode!=null)||($width!=null)){//i.e. if something in LineStyle needs to be set/changed
        if($xmlDoc->getElementsByTagName("Style")->item(0)->getElementsByTagName("LineStyle")->length==0){ 
           $xmlDoc->getElementsByTagName("Style")->item(0)->appendChild($xmlDoc->createElement ("LineStyle"));
        }
        $LineStyle = $xmlDoc->getElementsByTagName("Style")->item(0)->getElementsByTagName("LineStyle")->item(0);
        setTagDOM("color",$lcolourString, $LineStyle,$xmlDoc);
        setTagDOM("colorMode",$lmode, $LineStyle,$xmlDoc);
        setTagDOM("width",$width, $LineStyle,$xmlDoc);
        //As assuming polygon, not adding gx:outerColor [kml:color], or gx:outerWidth [float],also not adding gx:physicalWidth [float], or gx:labelVisibility [bool]  
    }
    if(($fcolourString!=null)||($fmode!=null)||($fill!=null)||($outline!=null)){//i.e. if something in PolyStyle needs to be set/changed
	    if($xmlDoc->getElementsByTagName("Style")->item(0)->getElementsByTagName("PolyStyle")->length==0){ 
           $xmlDoc->getElementsByTagName("Style")->item(0)->appendChild($xmlDoc->createElement ("PolyStyle"));
        }
        $PolyStyle = $xmlDoc->getElementsByTagName("PolyStyle")->item(0);
        setTagDOM("color",$fcolourString, $PolyStyle,$xmlDoc);
        setTagDOM("colorMode",$fmode, $PolyStyle,$xmlDoc);
        setTagDOM("fill",$fill, $PolyStyle,$xmlDoc);
        setTagDOM("outline",$outline, $PolyStyle,$xmlDoc); 
    }  
    return $xmlDoc;
}
//if the value to go in the tag, in the given parent, is non-null, either set the value of the tag to this, or if the tag does not exist, create it with this value, in the xml DOM 
function setTagDOM($tagName,$tagValue, $parent, $xmlDoc){
    if($tagValue!=null){
        if($parent->getElementsByTagName($tagName)->length==0){ 
           $parent->appendChild($xmlDoc->createElement ($tagName,$tagValue));
         }else{
            $parent->getElementsByTagName($tagName)->item(0)->nodeValue = $tagValue;
         }
    }
}
//if the value to go in the tag, in the given parent, is non-null, either set the value of the tag to this, or if the tag does not exist, create it with this value, using SimleXml 
function setTagSimple($tagName,$tagValue, $parent){
    if($tagValue!=null){
         $changed=FALSE;
         foreach ($parent->children() as $child){
            if($child->getName()==$tagName){
                $parent->{$tagName}=$tagValue;
                $changed=TRUE;
            } 
         }
         if(!($changed)){
             $parent->addChild($tagName,$tagValue);
         }
    }
}
//takes the kml file at the given url, and sets its colour to the given colour, and returns the new kml file using SimpleXML
function setKmlColourSimple(){
	global $urlString,$lcolourString,$fcolourString,$lmode,$fmode,$fill,$outline,$width;
	$xml=simplexml_load_file($urlString);
	 if(($lcolourString!=null)||($lmode!=null)||($width!=null)){//i.e. if something in LineStyle needs to be set/changed
        $needAdd=TRUE;
         foreach ($xml->Placemark->Style->children() as $child){
            if($child->getName()=="LineStyle"){
                $needAdd=FALSE;
            } 
         }
         if($needAdd){
            $xml->Placemark->Style->addChild("LineStyle");
         }
        $LineStyle = $xml->Placemark->Style->LineStyle;
        setTagSimple("color",$lcolourString, $LineStyle);
        setTagSimple("colorMode",$lmode, $LineStyle);
        setTagSimple("width",$width, $LineStyle);
        //As assuming polygon, not adding gx:outerColor [kml:color], or gx:outerWidth [float],also not adding gx:physicalWidth [float], or gx:labelVisibility [bool]  
    }
    if(($fcolourString!=null)||($fmode!=null)||($fill!=null)||($outline!=null)){//i.e. if something in PolyStyle needs to be set/changed
	  $needAdd=TRUE;
         foreach ($xml->Placemark->Style->children() as $child){
            if($child->getName()=="PolyStyle"){
                $needAdd=FALSE;
            } 
         }
         if($needAdd){
            $xml->Placemark->Style->addChild("PolyStyle");
         }
        $PolyStyle = $xml->Placemark->Style->PolyStyle;
        setTagSimple("color",$fcolourString, $PolyStyle);
        setTagSimple("colorMode",$fmode, $PolyStyle);
        setTagSimple("fill",$fill, $PolyStyle);
        setTagSimple("outline",$outline, $PolyStyle); 
    }
    return $xml;
}
//make sure colour String is valid, and replace it with default otherwise (unless null)
//could extend to alphaless colour
function enforceColourForm(&$colour){//colour in form aabbggrr
   if($colour!=null){
        $valid=TRUE;
        if(strlen($colour) !=8){//if length != 8 invalid
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
}
//enforces that $mode is either "normal" or "random", or null (default to "normal" otherwise)
function enforceModeForm(&$mode){
    if($mode!=null){
       if($mode!="random"){
            $mode="normal";
        } 
    }     
}
//enforces that $val is either "1" or "0" or null, i.e. boolean/ not specified. defaults to false ("0" if specified)
function enforceBool(&$val){
    if($val!=null){
        if($val!="1"){
            $val="0";
        }
    }    
}
//enforces that $val is either null, or a float, defaults to 1
function enforceFloat(&$val){
    if($val!=null){
        if(is_numeric($val)){
            $val=0.0+$val;
        }else{
            $val=1.0;
        }
    }
}
 //will assume page provides full link in form: filename.php?url=urlString[&lcol=lcolourString][&xxx=var]...
        //assume urlstring is full http//www...../name.kml
        $urlString = htmlspecialchars($_GET["url"]);
        $lcolourString = htmlspecialchars($_GET["lcol"]);
        $width = htmlspecialchars($_GET["wid"]);
        $lmode = htmlspecialchars($_GET["lmod"]);
        $fill = htmlspecialchars($_GET["fill"]);
        $outline = htmlspecialchars($_GET["out"]);
        $fcolourString = htmlspecialchars($_GET["fcol"]);
        $fmode = htmlspecialchars($_GET["fmod"]);
        enforceColourForm($lcolourString);
        enforceColourForm($fcolourString);
        enforceModeForm($lmode);
        enforceModeForm($fmode);
        enforceBool($fill);
        enforceBool($outline);
        enforceFloat($width);
        $dummy = setKmlColourSimple(); 
        //$dummy2 = setKmlColourDOM();

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
