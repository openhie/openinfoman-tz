import module namespace dxf2csd = "http://dhis2.org/csd/dxf/2.0";
import module namespace csd_webconf =  "https://github.com/openhie/openinfoman/csd_webconf";
import module namespace csd_dm = "https://github.com/openhie/openinfoman/csd_dm";
import module namespace functx = "http://www.functx.com";

declare namespace csd = "urn:ihe:iti:csd:2013";

declare variable $careServicesRequest as item() external;


let $doc_name := string($careServicesRequest/@resource)
let $doc := csd_dm:open_document($csd_webconf:db,$doc_name)


let $oid := $careServicesRequest/oid
let $csv_src := $careServicesRequest/csv  
let $csv :=    csv:parse($csv_src, map{ 'header': true()})/csv/record



let $namespace_uuid := "7ee93e32-78da-4913-82f8-49eb0a618cfc"

let $generate_UUID_v3 := function($name) {
  concat('urn:uuid:' , dxf2csd:generate_UUID_v3($name,$namespace_uuid))
}



let $csd_orgs := 
  for $record in $csv
   let $village := $record/VillMtaa
   let $ward  := $record/Ward
   let $council  := $record/Council
   let $district  := $record/District
   let $region  := $record/Region
   let $zone  := $record/Zone 
   let $country  := $record/Country
   let $name  := $record/Name
   let $id  := $record/NodeID
   let $urn := $generate_UUID_v3(concat('organization:',$id))
   let $level := 
      if (not(functx:all-whitespace($village)))
      then  '6'
      else if (not(functx:all-whitespace($ward)))
      then  '5'
      else if (not(functx:all-whitespace($council)))
      then  '4'
      else if (not(functx:all-whitespace($district)))
      then  '3'
      else if (not(functx:all-whitespace($region)))
      then  '2'
      else if (not(functx:all-whitespace($zone)))
      then  '1'
      else if (not(functx:all-whitespace($country)))
      then  '0'
      else ()
   let $pid := 
      if ( not(functx:all-whitespace($village)) )
      then  $csv[./Country = $country and ./Zone = $zone and ./Region = $region and ./District = $district and ./Council = $council and ./Ward = $ward and functx:all-whitespace(./VillMtaa)]/NodeID/text()
      else if (not(functx:all-whitespace($ward)))
      then  $csv[./Country = $country and ./Zone = $zone and ./Region = $region and ./District = $district and ./Council = $council and functx:all-whitespace(./VillMtaa) and functx:all-whitespace(./Ward)]/NodeID/text()
      else if (not(functx:all-whitespace($council)))
      then  $csv[./Country = $country and ./Zone = $zone and ./Region = $region and ./District = $district and functx:all-whitespace(./Council) and functx:all-whitespace(./VillMtaa) and functx:all-whitespace(./Ward) ]/NodeID/text()
      else if (not(functx:all-whitespace($district)))
      then  $csv[./Country = $country and ./Zone = $zone and ./Region = $region and functx:all-whitespace(./District) and functx:all-whitespace(./Council) and functx:all-whitespace(./VillMtaa) and functx:all-whitespace(./Ward) ]/NodeID/text()
      else if (not(functx:all-whitespace($region)))
      then  $csv[./Country = $country and ./Zone = $zone and functx:all-whitespace(./Region) and functx:all-whitespace(./District) and functx:all-whitespace(./Council) and functx:all-whitespace(./VillMtaa) and functx:all-whitespace(./Ward)]/NodeID/text()
      else if (not(functx:all-whitespace($zone)))
      then  $csv[./Country = $country  and functx:all-whitespace(./Zone) and functx:all-whitespace(./Region) and functx:all-whitespace(./District) and functx:all-whitespace(./Council) and functx:all-whitespace(./VillMtaa) and functx:all-whitespace(./Ward)]/NodeID/text()
      else ()
   let $parent := 
     if ($pid)
     then
       let $parent_urn := $generate_UUID_v3(concat('organization:',$pid))
       return <csd:parent entityID="{$parent_urn}"/>
     else ()
   return 
     if (($name) and ($level)  and ($urn) )
     then 
       <csd:organization entityID="{$urn}">
	 <csd:otherID assigningAuthorityName='resource_map_tanzania' code="{$id}"/>
	 <csd:codedType code="{$level}" codingScheme="{$oid}"/>
	 <csd:primaryName>{$name/text()}</csd:primaryName>
	 {$parent}
	 {
	   if ( not(functx:all-whitespace($village)) )
	     then  
	       (
		 concat('Want parent ward ' , $ward)
		 , 
		 $csv[./Ward = $ward ] (:and functx:all-whitespace(./VillMtaa)]:)
		 )
	     else ()
	 }

       </csd:organization>
     else ($record)  (:no name or id or type :)



let $csd :=
  <csd:CSD>
    <csd:organizationDirectory>
      {$csd_orgs}
    </csd:organizationDirectory>
    <csd:serviceDirectory/>
    <csd:facilityDirectory/>  
    <csd:providerDirectory/>
  </csd:CSD>

return  csd_dm:add($csd_webconf:db,$csd,$doc_name)


