<html>
<head>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<link rel="shortcut icon" href="static/favicon.ico">

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>

<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<script>
function blink() {
 var blinks = document.getElementsByTagName('blink');
 for (var i = blinks.length - 1; i >= 0; i--) {
 var s = blinks[i];
 s.style.visibility = (s.style.visibility === 'visible') ? 'hidden' : 'visible';
}
window.setTimeout(blink, 500);
}
if (document.addEventListener) document.addEventListener("DOMContentLoaded", blink, false);
else if (window.addEventListener) window.addEventListener("load", blink, false);
else if (window.attachEvent) window.attachEvent("onload", blink);
else window.onload = blink;

$(function() {

    $('#chkveg').multiselect({
				enableCaseInsensitiveFiltering: true,
        includeSelectAllOption: true
    });
});

function get_districts() {
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
	    document.getElementById("districts").innerHTML=xmlhttp.responseText;
	    }
	  }
	xmlhttp.open("GET","merge_entities_JSP.php",true);
	xmlhttp.send();
}

function clear_search(id) {
	document.getElementById("search"+id).innerHTML = ""
}

function search_target(search_target,id)
{
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else

  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
			if(xmlhttp.responseText == null || xmlhttp.responseText == undefined || xmlhttp.responseText == false)
			document.getElementById("search"+id).innerHTML = ""
			else
    	document.getElementById("search"+id).innerHTML=xmlhttp.responseText;
    }
  }
var target_doc = document.docs.target_doc_name.value
xmlhttp.open("GET","merge_entities_JSP.php?search_target="+search_target+"&vimsid="+id+"&target_doc="+target_doc,true);
xmlhttp.send();
}

function display_report(from,change_page,total_page)
{
	document.getElementById("report").innerHTML="<center><font><img width=\"70\" height=\"70\" src=\"static/loading.gif\"></center>"
	var src_doc = document.docs.src_doc_name.value
	var target_doc = document.docs.target_doc_name.value
	var entity_type = document.docs.entity_type.value
	var districts = $('#chkveg').val()
	var max_rows = document.docs.max_rows.value
	var favorite = [];

if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("report").innerHTML=xmlhttp.responseText;
    }
  }
  if(from=="page") {
	xmlhttp.open("GET","merge_entities_JSP.php?page="+change_page+"&total_page="+total_page+"&districts="+districts+"&src_doc="+src_doc+"&target_doc="+target_doc+"&entity_type="+entity_type+"&max_rows="+max_rows,true);
  }
  else
xmlhttp.open("GET","merge_entities_JSP.php?districts="+districts+"&src_doc="+src_doc+"&target_doc="+target_doc+"&entity_type="+entity_type+"&max_rows="+max_rows,true);
xmlhttp.send();
}

function show(id,level_message) {
      var label="label"+id
      if(document.getElementById(id).style.display=="inline") {
          document.getElementById(label).innerHTML="<b>+</b>Show The "+level_message
          document.getElementById(id).style.display="none"
      }
      else {
          document.getElementById(label).innerHTML="<b>-</b>Hide The "+level_message
          document.getElementById(id).style.display="inline"
      }
  }
</script>
</head>
</html>
<body>
<div class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <img class="pull-left" height="38px" style="margin-top:8px; margin-right:5px" src="static/oim_logo_48p.png">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <table>
            <tbody><tr>
              <td>
                <a class="navbar-brand" href="#">OpenInfoMan - VIMS-HFR Facility Mapping</a>
              </td>
            </tr>
            <tr>
              <td>
                <span style="font-size:0.5em; color:white">
	        part of the iHRIS family of health workforce data solutions
		</span>
              </td>
            </tr>
          </tbody></table>
        </div>
        <span class="pull-right">
          <img src="static/openhie.png" style="height:60px; padding-right:10px; padding-top:8px;">
        </span>
      </div>
    </div>
<center>
<form action="#" name="docs" method="POST">
<table><tr>
<?php
$host="http://localhost:8984/CSD";
$docs=get_docs();
echo "<td>Source CSD Document</td><td><select name='src_doc_name'>".display_docs($docs,'src_doc_name')."</select></td>
<td>Entity Type</td><td><select name='entity_type'>
<option value='facility'>Facility</option>
<option value='provider'>Provider</option>
<option value='organization'>Organization</option>
<option value='service'>Service</option>
</select></td></tr><tr>
<td>Target CSD Document</td><td><select name='target_doc_name'>".display_docs($docs,'target_doc_name')."</select></td>
<td>Rows Per Page</td><td><select name='max_rows'>
<option>2</option>
<option>5</option>
<option>10</option>
<option selected>20</option>
<option>40</option>
<option>60</option>
<option>80</option>
<option>100</option>
<option value='all'>All</option>
</select></td>
";
?>
<td></td><td><input type='submit' name='get_districts' value='Get Districts'></td>
</tr>
<?php
if(isset($_POST["get_districts"])) {
echo "<tr><td>";
display_districts();
?>
</td>
<td><input type='button' value='Load Facilities' name='set_docs' onclick='display_report("","","")'></td>
<?php
}
?>
</table>
</center></form>
<?php

	function get_docs() {
		global $host;
    	$csr = "<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
						<adhoc>db:list('provider_directory','service_directories')</adhoc>
					 </csd:requestParams>";
		$urn = "urn:ihe:iti:csd:2014:adhoc";
		$docs=exec_request("vims",$csr,$urn,$host);
		$docs=str_replace("service_directories/","",$docs);
		$docs=explode(".xml",$docs);
		return $docs;
    	}



   function display_docs ($docs,$location) {
   	$options="";
    	foreach($docs as $doc) {
    		if($doc=="")
    		continue;
				$doc = trim ($doc);
				if($doc == $_POST[$location])
    		$options=$options."<option value='$doc' selected>$doc</option>";
				else
				$options=$options."<option value='$doc'>$doc</option>";
    	}
    	return $options;
   }

	 function display_districts() {
		 global $host;
     	$csr = "<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
 					 </csd:requestParams>";
 		$urn = "urn:openhie.org:openinfoman-tz:select_vims_district";
 		$districts=exec_request($_POST["src_doc_name"],$csr,$urn,$host);
		$districts = json_decode($districts,true);
		$displayed = array();
		echo "Filter By Districts <select name='districts' id='chkveg' multiple='multiple'>";
		foreach($districts["districts"] as $distr) {
			$distr_name = $distr["name"];
			if(in_array($distr["name"],$displayed))
			continue;
			echo "<option value='$distr_name' name='district'>".$distr_name."</option>";
			$displayed[] = $distr["name"];
		}
	 }

   function exec_request($doc_name,$csr,$urn,$host) {
   	$curl_opts = array(
	    'CURLOPT_HEADER'=>0,
	    'CURLOPT_POST'=>1,
	    'CURLOPT_HTTPHEADER'=>array('content-type'=>'content-type: text/xml'),
            'CURLOPT_RETURNTRANSFER'=>1
	    );
        $curl =  curl_init($host . "/csr/{$doc_name}/careServicesRequest/{$urn}");
        foreach ($curl_opts as $k=>$v)  {
                curl_setopt($curl,@constant($k) ,$v);
            }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $csr);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $curl_out = curl_exec($curl);
        if ($err = curl_errno($curl) ) {
            return false;
        }
        curl_close($curl);
        return $curl_out;
   }
?>
<div id="report">

</div>
<div class="footer">
      <center>
        <img src="static/USAID_CP_IH_PEPFAR_logos.png" width="30%">
      </center>
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <!--
	    <a class='pull-right' href="http://www.youtube.com/watch?v=pBjvkHHuPHc"  style='color:rgb(0,0,0);text-decoration:none'>(tra-la-la)</a>
           -->
          </div>
        </div>
      </div>
    </div>
</body>
