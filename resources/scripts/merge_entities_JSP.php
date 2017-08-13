<?php
set_time_limit(0);
ini_set('memory_limit', '2000M');
abstract class entityMatch {
    protected $host;
    protected $src_doc_name;
    protected $target_doc_name;
    protected $entity_type;

    public abstract function find_matching_entities($entity_id,$entity_name,$vimsid);
    function __construct($host,$target_doc_name,$src_doc_name,$entity_type) {
        $this->host = $host;
        $this->target_doc_name = $target_doc_name;
        $this->src_doc_name = $src_doc_name;
        $this->entity_type = $entity_type;
        $this->lat_path = "/csd:geocode/csd:latitude";
        $this->long_path = "/csd:geocode/csd:longitude";

        //set name_path
        switch ($this->entity_type) {
        case 'organization':
        case 'facility':
        case 'service':
            $this->name_path =
                "/csd:primaryName";
            break;
        case 'provider':
            $this->name_path =  "/csd:demographic/csd:name/csd:commonName";
            break;
        default:
            return array();

        }
    }

    public function retrieve_target_entitiy( $entity_id) {
        return $this->retrieve_entities($this->target_doc_name,$entity_id);
    }
    public function retrieve_src_entity( $doc_name,$entity_id) {
        $results =$this->retrieve_entities( $doc_name,$array($entity_id));
        if (!is_array($results) || count($results) != 1 ) {
            return false;
        }
        return array_pop($results);
    }


    protected $entity_cache = array();
    public function retrieve_src_entities( $entity_ids) {
        return $this->retrieve_entities($this->src_doc_name,$entity_ids);
    }
    public function retrieve_target_entities( $entity_ids) {
        return $this->retrieve_entities($this->target_doc_name,$entity_ids);
    }

    public function retrieve_entities( $doc_name,$entity_type) {
      $results =array();
              $csr = "
<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>

</csd:requestParams>";
              $urn ="urn:openhie.org:openinfoman-hwr:stored-function:{$entity_type}_get_all";
              $entity = $this->exec_request($doc_name,$csr,$urn);
      return $entity;
    }


    public function get_target_entity_ids( $page , $page_size ) {
        return $this->get_entity_ids($this->target_doc_name,$page,$page_size);
    }
    public function get_src_entity_ids( $page , $page_size ) {
        return $this->get_entity_ids($this->src_doc_name,$page,$page_size);
    }
    public function get_entity_ids( $doc_name,$page = 0, $page_size = 50) {
        $entity_ids = array();
        switch ($this->entity_type) {
        case 'provider':
            $urn = "urn:openhie.org:openinfoman-hwr:stored-function:health_worker_get_urns";
            break;
        case 'facility':
        case 'organization':
        case 'service':
            $urn = "urn:openhie.org:openinfoman-hwr:stored-function:{$this->entity_type}_get_urns";
            break;
        default:
            return $entity_ids;
        }
        $csr = "
<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
    <csd:start>$page</csd:start>
    <csd:max>$page_size</csd:max>
</csd:requestParams>";
        if (! $response = $this->exec_request($doc_name,$csr,$urn)) {
            return $entity_ids;
        }
        $response_xml = new SimpleXMLElement($response);
        $response_xml->registerXPathNamespace ( "csd" , "urn:ihe:iti:csd:2013");
        if (is_array($ids = $response_xml->xpath("/csd:CSD/*/*[@entityID]"))) {
            foreach ($ids as $id) {
            	$id=json_encode($id);
            	$id=json_decode($id,true);
               $entity_ids[] = $id["@attributes"]["entityID"];
            }
        }
        return $entity_ids;
    }

    public function get_docs() {
    	$csr = "<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
						<adhoc>db:list('provider_directory','service_directories')</adhoc>
				  </csd:requestParams>";
		$urn = "urn:ihe:iti:csd:2014:adhoc";
		$docs=$this->exec_request($this->target_doc_name,$csr,$urn);
		$docs=str_replace("service_directories/","",$docs);
		$docs=explode(".xml",$docs);
		return $docs;
    	}

    public function display_docs ($docs) {
    	foreach($docs as $doc) {
    		if($doc=="")
    		continue;
    		$options=$options."<option value='$doc'>$doc</option>";
    	}
    	return $options;
    }

    public function is_marked_duplicate($id1,$id2 ) {
        $entity2 = $this->retrieve_entities($this->target_doc_name,array($id2));
        foreach($entity2 as $ent2)
        if ($this->extract($ent2,"/csd:otherID[@assigningAuthorityName='https://vims.moh.go.tz' and @code='id' and ./text()='{$id1}']",false)) {
            return true;
        } else {
            return false;
        }
    }


    public function is_marked_not_duplicate($id1,$id2 ) {
        $entity1 = $this->retrieve_entities($this->target_doc_name,$id1);
        if ($this->extract($entity1,"/csd:otherID[@assigningAuthorityName='urn:openhie.org:openinfoman' and @code='not-duplicate' and ./text()='{$id2}']",false)) {
            return true;
        } else {
            return false;
        }
    }

    protected $entity_xml_cache = array();
    public function extract($entity,$xpath,$implode = true) {
            //$entity_xml = new SimpleXMLElement($entity);
            $entity_xml = $entity;
            $entity_xml->registerXPathNamespace ( "csd" , "urn:ihe:iti:csd:2013");
        $xpath_pre = "(/csd:CSD/*/csd:{$this->entity_type})[1]";
        $results =  $entity_xml->xpath($xpath_pre . $xpath);
        if ($implode && is_array($results)) {
            $results = implode($results);
        }
        foreach($results as $res)
        return $res;
        return;
    }
    protected $curl_opts = array(
	    'CURLOPT_HEADER'=>0,
	    'CURLOPT_POST'=>1,
	    'CURLOPT_HTTPHEADER'=>array('content-type'=>'content-type: text/xml'),
            'CURLOPT_RETURNTRANSFER'=>1
	    );
    public function exec_request($doc_name,$csr,$urn) {
        $curl =  curl_init($this->host . "/csr/{$doc_name}/careServicesRequest/{$urn}");
        foreach ($this->curl_opts as $k=>$v)  {
                curl_setopt($curl,@constant($k) ,$v);
            }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $csr);
        $curl_out = curl_exec($curl);
        if ($err = curl_errno($curl) ) {
            return false;
        }
        curl_close($curl);
        return $curl_out;
    }
  }


class entityMatchPotentialDuplicates extends entityMatch {
    public function find_matching_entities($entity_id,$entity_name,$vimsid) {
        $rankings =array(0=>$this->get_potential_duplicates($entity_id)); //flat rankings - no preference in these
        return $rankings;
    }

    public function get_potential_duplicates($entity_id) {
        $entity1 = $this->retrieve_entities($this->target_doc_name,$id1);
        if (is_array($results = $this->extract($entity1,"/csd:otherID[@assigningAuthorityName='urn:openhie.org:openinfoman' and @code='potential-duplicate']"))) {
            $dups = array();
            foreach ($results as $result) {
                $dups[] = $result->__toString();
            }
            return $dups;
        } else {
            return false;
        }
    }

}

class entityMatchLevenshtein extends entityMatch {
	 public $target_entities=array();
    public function cache_vims_facilities($doc_name) {
      $limit_districts = array("Handeni DC","Bumbuli","Handeni TC","Kilindi","Korogwe DC","Korogwe TC","Lushoto","Mkinga","Muheza","Pangani","Tanga CC");
      $entities = $this->retrieve_entities($doc_name,$this->entity_type);
      $entities = new SimpleXMLElement($entities);
      foreach ($entities->facilityDirectory->children("urn:ihe:iti:csd:2013") as $facility) {
        $uuid = (string)$facility->attributes()->entityID;
        $name = (string)$facility->primaryName;
        $parents = array();
        foreach($facility->otherID as $otherID) {
          if($otherID->attributes()->code == "code")
          $code = (string)$otherID;
          else if($otherID->attributes()->code == "id")
          $id = (string)$otherID;
        }

        foreach($facility->extension as $extensions) {
          if($extensions->attributes()->type == "geographicZone")
          $district = (string)$extensions;
          if($extensions->attributes()->type == "facilityType")
          $facilityType = (string)$extensions;
        }

        if(!in_array($district,$limit_districts))
        continue;

        $facDetails[$doc_name][$uuid]["name"] = $name;
        $facDetails[$doc_name][$uuid]["id"] = $id;
        $facDetails[$doc_name][$uuid]["code"] = $code;
        $facDetails[$doc_name][$uuid]["district"] = $district;
        $facDetails[$doc_name][$uuid]["facilityType"] = $facilityType;
      }
      return $facDetails;
    }

    public function cache_hfr_facilities($doc_name) {
      $entities = $this->retrieve_entities($doc_name,$this->entity_type);
      $entities = new SimpleXMLElement($entities);
      foreach ($entities->facilityDirectory->children("urn:ihe:iti:csd:2013") as $facility) {
        $uuid = (string)$facility->attributes()->entityID;
        $name = (string)$facility->primaryName;
        $parents = array();
        foreach($facility->organizations->children("urn:ihe:iti:csd:2013") as $organization) {
          $parents[] = (string)$organization->attributes()->entityID;
        }
        foreach($facility->otherID as $otherID) {
          if($otherID->attributes()->assigningAuthorityName == "https://vims.moh.go.tz" and $otherID->attributes()->code == "id")
            $vimsid = (string)$otherID;
        }
        $facDetails[$doc_name][$uuid]["name"] = $name;
        $facDetails[$doc_name][$uuid]["parents"] = $parents;
        $facDetails[$doc_name][$uuid]["uuid"] = $uuid;
        if($vimsid)
        $facDetails[$doc_name][$uuid]["vimsid"] = $vimsid;
      }
      return $facDetails;
    }

    public function cache_hfr_orgs ($doc_name) {
      $entities = $this->retrieve_entities($doc_name,"organization");
      $entities = new SimpleXMLElement($entities);
      foreach ($entities->organizationDirectory->children("urn:ihe:iti:csd:2013") as $organization) {
        $uuid = (string)$organization->attributes()->entityID;
        $name = (string)$organization->primaryName;
        $parent = (string)$organization->parent->attributes()->entityID;
        $level = (string)$organization->codedType->attributes()->code;
        $orgDetails[$doc_name][$uuid]["name"] = $name;
        $orgDetails[$doc_name][$uuid]["parent"] = $parent;
        $orgDetails[$doc_name][$uuid]["uuid"] = $uuid;
        $orgDetails[$doc_name][$uuid]["level"] = $level;
      }
      return $orgDetails;
    }

    public function get_org_hierarchy($orguuid) {
      $orgs = $this->target_orgs_entities[$this->target_doc_name][$orguuid];
      if($this->hierarchy)
      $this->hierarchy .= ",".$orgs["name"];
      else
      $this->hierarchy = $orgs["name"];
      if($orgs["level"] > 4) {
        $this->get_org_hierarchy($orgs["parent"]);
      }
      else {
        return $this->hierarchy;
      }
    }

    public function find_matching_entities($src_entity_id,$src_entity_name,$vimsid) {
      $strs=explode(" ",$src_entity_name);
		 foreach($strs as $k=>$str)
			if($str=="")
			unset($strs[$k]);
		 $src_entity_name=implode(" ",$strs);
		 $src_entity_name=str_replace(" ","",$src_entity_name);
		 $this->shortest = -1;
		 $rankings=array();
       foreach ($this->target_entities[$this->target_doc_name] as $target_entity_id=>$target_entity) {
         $this->hierarchy = "";
         if(!array_key_exists("hierarchy",$target_entity)) {
           $this->get_org_hierarchy($target_entity["parents"][0]);
           error_log("getting parent for ".$target_entity["name"]);
           $this->target_entities[$this->target_doc_name][$target_entity_id]["hierarchy"] = $this->hierarchy;
           $target_entity["hierarchy"] = $this->hierarchy;
          }
       	//if it is already marked as duplicate then lev=0
        if(array_key_exists("vimsid",$target_entity) and $target_entity["vimsid"] == $vimsid) {
      		$lev=0;
      		$rankings['dup'][$target_entity_id]=$target_entity;
          return $rankings;
        }

       	$strs=explode(" ",$target_entity["name"]);
			foreach($strs as $k=>$str)
			if($str=="")
			unset($strs[$k]);
			$target_entity_name=implode(" ",$strs);
			if(count($strs)==2) {
        $strs = array_values($strs);
				$str1=implode(" ",$strs);
				$str2=$strs[1]." ".$strs[0];
				//join the two words by removing space
				$str1=str_replace(" ","",$str1);
				$str2=str_replace(" ","",$str2);
				//end of joining words

				$lev1 = levenshtein(trim(strtolower($src_entity_name)),trim(strtolower($str1)));
				$lev2 = levenshtein(trim(strtolower($src_entity_name)),trim(strtolower($str2)));
				if($lev1<$lev2)
				$lev=$lev1;
				else
				$lev=$lev2;
			}
			else
			$lev = levenshtein(trim(strtolower($src_entity_name)), trim(strtolower($target_entity_name)));

			if(count($rankings)==0) {
			     $rankings[$lev][$target_entity_id]=$target_entity;
      }
			else {
				if(array_key_exists($lev,$rankings) or count($rankings)<5) {
					$rankings[$lev][$target_entity_id]=$target_entity;
				}
				else {
					$existing_levs=array_keys($rankings);
					$max=max($existing_levs);
					$rank=$rankings;
					if($lev<$max) {
						unset($rankings[$max]);
						$rankings[$lev][$target_entity_id]=$target_entity;
					}
				}
			}
       }
       return $rankings;
    }
}

if($_SERVER["REQUEST_METHOD"]=="POST") {
	$entity_match = new entityMatchLevenshtein($_POST["host"],$_POST["target_doc_name"],$_POST["src_doc_name"],$_POST["entity_type"]);
	$entity_type=$_POST["entity_type"];
	foreach ($_POST["target"] as $source_id=>$target_id) {
		//if not duplicate
		if($target_id=="no_duplicate") {
			$csr = "<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
						<adhoc>declare namespace csd = 'urn:ihe:iti:csd:2013';
						//csd:CSD/*/csd:{$entity_type}[./csd:otherID[@code='duplicate' and ./text()='{$source_id}']]</adhoc>
					  </csd:requestParams>";
			$urn = "urn:ihe:iti:csd:2014:adhoc";
			$entities = $entity_match->exec_request($_POST["target_doc_name"],$csr,$urn);

		}
		//if duplicate
		else {
			$csr = "<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
							<csd:id entityID='$target_id'/>
							<csd:otherID assigningAuthorityName='https://vims.moh.go.tz' code='id'>$source_id</csd:otherID>
					  </csd:requestParams>";
			$urn = "urn:openhie.org:openinfoman-hwr:stored-function:facility_create_otherid";
			$entity_match->exec_request($_POST["target_doc_name"],$csr,$urn);
		}
	}
echo "<a href='merge_entities.php'>Return</a>";
}

else {
$target_doc_name = $_REQUEST["target_doc"];
$src_doc_name = $_REQUEST["src_doc"];
$entity_type = $_REQUEST["entity_type"];
$max_rows = $_REQUEST["max_rows"];
$host = "http://localhost:8984/CSD";
$entity_match = new entityMatchLevenshtein($host,$target_doc_name,$src_doc_name,$entity_type);

$match=array("1<sup>st</sup> Best Matches","2<sup>nd</sup> Best Matches","3<sup>rd</sup> Best Matches","4<sup>th</sup> Best Matches","5<sup>th</sup> Best Matches");
$colors=array("FF3333","FF8633","6EBB07","07BBB6","0728BB");

//control page display
if (isset($_GET['page']))
{
$page=$_GET['page'];
$total_page=$_GET['total_page'];
}
else
{
$page=0;
$count=0;
}
$first_row=$page*$max_rows;
$csr = "
<csd:requestParams xmlns:csd='urn:ihe:iti:csd:2013'>
<adhoc>declare namespace csd = 'urn:ihe:iti:csd:2013';count(/csd:CSD/csd:{$entity_type}Directory/*)</adhoc>
</csd:requestParams>";
$urn = "urn:ihe:iti:csd:2014:adhoc";
$total_rows = $entity_match->exec_request($src_doc_name,$csr,$urn);
if($max_rows > $total_rows or $max_rows=="all")
$max_rows=$total_rows;

$total_page=ceil($total_rows/$max_rows)-1;
//end of controlling page display
echo "<form method='POST' action='merge_entities_JSP.php'>";
echo "<input type='hidden' name='target_doc_name' value='$target_doc_name'>";
echo "<input type='hidden' name='src_doc_name' value='$src_doc_name'>";
echo "<input type='hidden' name='host' value='$host'>";
echo "<input type='hidden' name='entity_type' value='$entity_type'>";
echo "<center><u><b><font color='green'>Page Number ".($page+1)."/".($total_page+1)."</font><font color='orange'> Showing ".$max_rows."/".$total_rows." Records</b><p></u>";
###########  displaying navigations  next,previous,last,first ############
echo "</table><table><tr>";
if($page>0)
{
echo"<td><a href='#' onclick='return display_report(\"page\",0,$total_page)' title='First Page'> |< First &nbsp;</a> &nbsp; &nbsp;</td>";
}
$next=$page+1;
if($page<$total_page)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$next,$total_page)' title='Next Page'> Next >> &nbsp;</a> &nbsp; &nbsp;</td>";
}
$prev=$page-1;
if ($page>0)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$prev,$total_page)' title='Previous Page'> << Previous &nbsp;</a> &nbsp; &nbsp;</td>";
}
if($page<$total_page)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$total_page,$total_page)' title='Last Page'> Last >| &nbsp;</a> &nbsp; &nbsp;</td>";
}
##########   end displaying navigations ##################
echo "<td align='center'><input type='submit' name='save' value='Save Changes'></td></tr>";
echo "</table>";
$count=++$first_row;
echo "<table border='1' cellspacing='0'><tr style='background-color:black;color:white'><th>SN</th><th>$src_doc_name</th><th>Marked As A Match In $target_doc_name</th><th>Possible Matches From $target_doc_name</th></tr>";

$entity_match->target_entities = $entity_match->cache_hfr_facilities($target_doc_name);
$entity_match->target_orgs_entities = $entity_match->cache_hfr_orgs($target_doc_name);
$entity_match->source_entities = $entity_match->cache_vims_facilities($src_doc_name);

foreach ($entity_match->source_entities[$src_doc_name] as $entity_id=>$entity) {
  $vimsid = $entity["id"];
  $vimscode = $entity["code"];
  $vimsdistrict = $entity["district"];
  $vimsftype = $entity["facilityType"];
  $vimsname = $entity["name"];
	$rankings = $entity_match->find_matching_entities($entity_id,$vimsname,$vimsid);
	echo "<tr><td>$count</td><td><input type='hidden' name=source[$vimsid] value=".$vimsid.">".$vimsname;
  echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;ID: $vimsid";
  echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Code: $vimscode";
  echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Type: $vimsftype";
  echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;District: $vimsdistrict";
  echo "</td>";
	$count++;
		//Display entity which is already marked as duplicate and unset the key
		if(array_key_exists('dup',$rankings)) {
      error_log("Dup Found");
			echo "<td>";
			foreach($rankings['dup'] as $uuid=>$values){
        error_log("Deeper");
			echo $values["name"];
      echo "<br>&nbsp;&nbsp;&nbsp;ID: ".$uuid;
      echo "<br>&nbsp;&nbsp;&nbsp;Hierarchy: ".$values["hierarchy"];
      echo "</br>";
      }
			unset($rankings['dup']);
			echo "</td>";
		}
		else
		echo "<td></td>";
   	echo "<td>";
   	ksort($rankings);
   	$match_index=0;
   	foreach($rankings as $lev=>$rankings_pull) {
   		$level_message=$match[$match_index];
   		echo "<font color='$colors[$match_index]'><label id=label$lev$vimsid onclick='show(`$lev$vimsid`,`$level_message`)'><b>+</b>Show The $level_message</label><br><div id='$lev$vimsid' style='display:none'>";
   		foreach($rankings_pull as $id=>$close) {
   			echo "<input type='radio' name='target[$vimsid]' value='$id'>".$close["name"];
        echo "<br>&nbsp;&nbsp;ID: ".$close["uuid"];
        echo "<br>&nbsp;&nbsp;Hierarchy: ".$close["hierarchy"];
        echo "<br>";
   		}
   		echo "</div></font>";
   		$match_index++;
   	}
   	echo "<input type='radio' name=target[$vimsid] value='no_duplicate'>No Match<br>";
   	echo "</td></tr>";
}

###########  displaying navigations  next,previous,last,first ############
echo "</table><table><tr>";
if($page>0)
{
echo"<td><a href='#' onclick='return display_report(\"page\",0,$total_page)' title='First Page'> |< First &nbsp;</a> &nbsp; &nbsp;</td>";
}
$next=$page+1;
if($page<$total_page)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$next,$total_page)' title='Next Page'> Next >> &nbsp;</a> &nbsp; &nbsp;</td>";
}
$prev=$page-1;
if ($page>0)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$prev,$total_page)' title='Previous Page'> << Previous &nbsp;</a> &nbsp; &nbsp;</td>";
}
if($page<$total_page)
{
echo"<td><a href='#' onclick='return display_report(\"page\",$total_page,$total_page)' title='Last Page'> Last >| &nbsp;</a> &nbsp; &nbsp;</td>";
}
##########   end displaying navigations ##################
echo "<td align='center'><input type='submit' name='save' value='Save Changes'></td></tr>";
echo "</table></center></form>";
}
?>
