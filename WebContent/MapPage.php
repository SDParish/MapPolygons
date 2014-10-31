<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<title>Webpage with map</title>
<style>
      html, body, #mapArea {
        height: 90%;
        margin: 0px;
        padding: 0px
      }
    </style>
<!-- Google maps javascript API -->
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
<script type="text/javascript">
var map;
var fullForm;
var kmls=[];//The KmlLayers created by the singleOn method, oldest in [0], in age order
var kmlNames=[];//The strings corresponding to the KmlLayers created by the singleOn method, oldest in [0], in age order

function setUpMap(){
	alert("loading page");//used to show/prove page is not reloading
	var mapOptions = {
		     zoom: 9,
		     center: new google.maps.LatLng(51.2, -0.8)
		  };
	map = new google.maps.Map(document.getElementById('mapArea'),mapOptions);
	setUpFullKmlList(<?php echo file_get_contents("https://sturents.com/geo/show-all")?>);//This is partly asynchronous, so may not be complete before subsequent actions
}
function setUpFullKmlList(showAll){
	fullForm = document.createElement("form");
	fullForm.name = "fullForm";
	var formElem;
	var place;
	for(place in showAll){//place goes through the numbers, we use the names showAll[place]
		//Create a checkbox
		formElem = document.createElement("input");
		formElem.type = "checkbox";
		formElem.name =showAll[place];
		formElem.setAttribute("onclick","if(checked){singleOn(name);}else{singleOff(name)}");
		//Add it to the form
		fullForm.appendChild(formElem);
		//Add the name after the checkbox
		fullForm.appendChild(document.createTextNode(showAll[place]));
	}
	document.getElementById("fullCheckboxDiv").appendChild(fullForm);
	//below here to ensure that the form is set up when it runs
	setUpInitialKmls();
}
function setUpInitialKmls(){//NB pop-up asking for js permission clears hash
	//format is: ...MapPage.html#aaaaa:lcol:out:wid:fcol:fill&bbbbb:lcol:out:wid:fcol:fill&cccc... where lcol,out,wid,fcol,fill can be ""
	//Note that a Google map can only display so many layers
	var targets = new Array(0);
	//Get an Array of all given locations
	if(window.location.hash.length>1){
		targets=window.location.hash.substring(1).split("&");		
	}
	var splitTarget;
	var colour;
	//Add new kmls
	for(i=0;i<targets.length;i++){
		//split into kml name and colour (if exists)
		splitTarget=targets[i].split(":");
		//check not already shown
		if(kmlNames.indexOf(splitTarget[0])==-1){
			//check checkbox and add to map
			addKml(splitTarget);	
			for(j=0;j<fullForm.elements.length;j++){
				if(fullForm.elements[j].name==splitTarget[0]){
					fullForm.elements[j].checked =!fullForm.elements[j].checked;
					j=fullForm.elements.length;
				}
			}
		}
	}
}
var colourSetterTags=["&lcol=","&out=","&wid=","&fcol=","&fill="];
function addKml(dataArray){//urlname, lcolourString, outString, widString, fcolourString, fillString){//separate method so can also be called when url is changed
	var newKml =new google.maps.KmlLayer();
	//have array of 'tags', map across it and dataArray[1 to end] to create request string
	var head="http://178.62.107.109/testUpload/kmlColourSetter.php?url=https://sturents.com/geo/";
	var tail=".kml";
	for(var i=1;i<6;i++){
		if(dataArray[i]!=""){
			tail=tail+colourSetterTags[i-1]+dataArray[i];
		}
	}
	alert(head+dataArray[0]+tail);
	newKml.setUrl(head+dataArray[0]+tail);
	newKml.setMap(map);
	kmls[kmls.length]=newKml;
	kmlNames[kmlNames.length]=dataArray[0];
}
//Don't bother with order, just stick on end, do random access - when doing 1 at a time order adds nothing of use.
function singleOn(urlname){
	//Append urlname + data to shown kmls in url
	//check not already shown
	if(kmlNames.indexOf(urlname)==-1){
		//get colourForm data
		var data = [urlname,document.colourForm.lineColour.value,document.colourForm.outline.value,document.colourForm.lineWidth.value,document.colourForm.fillColour.value,document.colourForm.fill.value];
		addKml(data);
		if(window.location.hash.length>1){//there are kmls in url
			window.location.hash+=("&"+data.join(":"));
		}else{//no current kmls in url
			window.location.hash+=(data.join(":"));
		}
	}
}
function singleOff(urlname){
	var index = kmlNames.indexOf(urlname);
	if(index!=-1){
		kmls[index].setMap(null);
		kmls.splice(index,1);
		kmlNames.splice(index,1);
		//remove urlname from url
		//find where the urlname starts in the hash, if there
		var paramStart = window.location.hash.indexOf(urlname);
		//added code to make sure have not found part of a longer urlname
		var isWholeName= false;
		//while have found a possible and have not found it to be right
		while((paramStart!=-1)&!isWholeName){
			//if instance of urlname is preceded by # or &, and followed by :
			if(((window.location.hash.charAt(paramStart-1)=='#')||(window.location.hash.charAt(paramStart-1)=='&'))&(window.location.hash.charAt(paramStart+urlname.length)==':')){
				//have found urlname on own, not as part of longer name
				isWholeName=true;
			}else{
				//urlname was part of a longer name, so lookk again after this location.
				paramStart = window.location.hash.indexOf(urlname, paramStart+1);
			}
		}
		if(paramStart!=-1){//i.e. it's in the url
			var newHashStart = window.location.hash.substring(0,paramStart);
			//find where the urlname's entry ends
			var paramEnd = window.location.hash.indexOf("&", paramStart);
			if(paramEnd==-1){//if no subsequent location in url, remove last symbol from newHashStart and set as the hash
				window.location.hash=newHashStart.substring(0,newHashStart.length-1); 
			}else{//get the subsequent hash contents without the '&', and set hash to be newHashStart followed by this
				window.location.hash=newHashStart+window.location.hash.substring(paramEnd+1);
			}
		}
	}	
}
google.maps.event.addDomListener(window, 'load', setUpMap);
</script>
</head>
<body>
<p>
Map will go below when put in.
</p>
<div id="mapArea"></div>
<form name="colourForm" onsubmit="return false;">
Line colour:<input type="text" name="lineColour" value="ffe89e40" size=8 maxlength=8>
Show outline:<select name="outline">
	<option value="" selected></option>
	<option value="1">Yes</option>
	<option value="0">No</option>
	</select>
Line width: <input type="number" name="lineWidth" value="5" size=4>
	<br/>
Fill colour : <input type="text" name="fillColour" size=8 maxlength=8>
Fill polygon : <select name="fill">
	<option value="" selected></option>
	<option value="1">Yes</option>
	<option value="0">No</option>
	</select>
If any entry is left blank, it is left as is in original file.
<br/>
Colours are in hex, of form aabbggrr, and width is a float.
</form>
<!-- Div to hold a dynamically generated form from https://sturents.com/geo/show-all -->
<div id="fullCheckboxDiv"></div>
</body>
</html>