import module namespace dxf2csd = "http://dhis2.org/csd/dxf/2.0";
import module namespace csd_webconf =  "https://github.com/openhie/openinfoman/csd_webconf";
import module namespace csd_dm = "https://github.com/openhie/openinfoman/csd_dm";
import module namespace functx = "http://www.functx.com";

declare namespace csd = "urn:ihe:iti:csd:2013";

declare variable $careServicesRequest as item() external;

let $doc_name := string($careServicesRequest/@resource)
let $doc := csd_dm:open_document($csd_webconf:db,$doc_name)
let $org_dir := $doc/csd:CSD/csd:organizationDirectory

let $oid := $careServicesRequest/oid
let $csv_src := $careServicesRequest/csv  
let $csv :=    csv:parse($csv_src, map{ 'header': true()})/csv/record



let $namespace_uuid := "7ee93e32-78da-4913-82f8-49eb0a618cfc"

let $generate_UUID_v3 := function($name) {
  concat('urn:uuid:' , dxf2csd:generate_UUID_v3($name,$namespace_uuid))
}

let $zone_csd :=
for $zone_record in distinct-values($csv/Zone)
 let $now := current-dateTime()
 let $zone_urn := $generate_UUID_v3(concat('organization:',$zone_record))
  return
   <csd:organization entityID="{$zone_urn}">
     <csd:codedType code="1" codingScheme="{$oid}"/>
     <csd:primaryName>{$zone_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
   </csd:organization>

let $region_csd :=
for $region_record in distinct-values($csv/Region)
 let $now := current-dateTime()
 let $region_urn := $generate_UUID_v3(concat('organization:',$region_record))
 let $parent_urns :=
 for $record in $csv  
  let $zone := $record/Zone
  let $region := $record/Region
  return if((contains($region_record,$region)))
   then $generate_UUID_v3(concat('organization:',$zone))
   else ()
 return
   <csd:organization entityID="{$region_urn}">
     <csd:codedType code="2" codingScheme="{$oid}"/>
     <csd:primaryName>{$region_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urns[last()]}"/>
   </csd:organization>

let $district_csd :=
for $district_record in distinct-values($csv/District)
 let $now := current-dateTime()
 let $district_urn := $generate_UUID_v3(concat('organization:',$district_record))
 let $parent_urns :=
 for $record in $csv
  let $region := $record/Region
  let $district := $record/District
  return if((contains($district_record,$district)))
   then $generate_UUID_v3(concat('organization:',$region))
   else ()
 return
   <csd:organization entityID="{$district_urn}">
     <csd:codedType code="3" codingScheme="{$oid}"/>
     <csd:primaryName>{$district_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urns[last()]}"/>
   </csd:organization>

let $council_csd :=
for $council_record in distinct-values($csv/Council)
 let $now := current-dateTime()
 let $council_urn := $generate_UUID_v3(concat('organization:',$council_record))
 let $parent_urns :=
 for $record in $csv
  let $district := $record/District
  let $council := $record/Council
  return if((contains($council_record,$council)))
   then $generate_UUID_v3(concat('organization:',$district))
   else ()
 return
   <csd:organization entityID="{$council_urn}">
     <csd:codedType code="4" codingScheme="{$oid}"/>
     <csd:primaryName>{$council_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urns[last()]}"/>
   </csd:organization>

let $ward_csd :=
for $ward_record in distinct-values($csv/Ward)
 let $now := current-dateTime()
 let $ward_urn := $generate_UUID_v3(concat('organization:',$ward_record))
 let $parent_urns :=
 for $record in $csv
  let $council := $record/Council
  let $ward := $record/Ward
  return if((contains($ward_record,$ward)))
   then $generate_UUID_v3(concat('organization:',$council))
   else ()
 return if (not (contains($ward_record,"Not set"))) then
   <csd:organization entityID="{$ward_urn}">
     <csd:codedType code="5" codingScheme="{$oid}"/>
     <csd:primaryName>{$ward_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urns[last()]}"/>
   </csd:organization>
   else ()

let $village_csd :=
for $village_record in distinct-values($csv/VillageStreet)
 let $now := current-dateTime()
 let $village_urn := $generate_UUID_v3(concat('organization:',$village_record))
 let $parent_urns :=
 for $record in $csv
  let $ward := if ((contains($record/Ward,"Not set"))) then $record/Council else($record/Ward)
  let $village := $record/VillageStreet
  return if((contains($village_record,$village)))
   then $generate_UUID_v3(concat('organization:',$ward))
   else ()
 return if (not (contains($village_record,"Not set"))) then
   <csd:organization entityID="{$village_urn}">
     <csd:codedType code="6" codingScheme="{$oid}"/>
     <csd:primaryName>{$village_record}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urns[last()]}"/>
   </csd:organization>
   else ()

let $facility_csd :=
for $record in $csv
 let $facNum := $record/FacilityNumber
 let $facName  := $record/FacilityName
 let $now := current-dateTime()
 let $facility_urn := $generate_UUID_v3(concat('organization:',$facNum))
 let $parent := if ((contains($record/VillageStreet,"Not set"))) then 
                     (if ((contains($record/Ward,"Not set"))) then $record/Council else $record/Ward) 
                     else($record/VillageStreet)
 let $parent_urn := $generate_UUID_v3(concat('organization:',$parent))
 return if (not (contains($facName,"Not set"))) then
   <csd:organization entityID="{$facility_urn}">
     <csd:otherID assigningAuthorityName='health_facility_registry' code="{$facNum}"/>
     <csd:codedType code="7" codingScheme="{$oid}"/>
     <csd:primaryName>{$facName/text()}</csd:primaryName>
     <csd:record
       created="{$now}"
       updated="{$now}"
       sourceDirectory="http://hfrportal.ehealth.go.tz"/>
     <csd:parent entityID="{$parent_urn}"/>
   </csd:organization>
   else ()

let $csd :=
  <csd:CSD>
    <csd:organizationDirectory>
      {($zone_csd,$region_csd,$district_csd,$council_csd,$ward_csd,$village_csd,$facility_csd)}
    </csd:organizationDirectory>
    <csd:serviceDirectory/>
    <csd:facilityDirectory/>
    <csd:providerDirectory/>
  </csd:CSD>

return  csd_dm:add($csd_webconf:db,$csd,$doc_name)
