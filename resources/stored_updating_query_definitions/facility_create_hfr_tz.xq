import module namespace util = "https://github.com/openhie/openinfoman-dhis/util";
import module namespace csd_bl = "https://github.com/openhie/openinfoman/csd_bl";
declare namespace csd = "urn:ihe:iti:csd:2013";
declare default element  namespace   "urn:ihe:iti:csd:2013";
declare variable $careServicesRequest as item() external;
(:
   The query will be executed against the root element of the CSD document.

   The dynamic context of this query has $careServicesRequest set to contain any of the search
   and limit paramaters as sent by the Service Finder
:)

let $namespace_uuid := "7ee93e32-78da-4913-82f8-49eb0a618cfc"

let $uuid_generate := function($name) {
  concat('urn:uuid:' , util:uuid_generate($name,$namespace_uuid))
}

let $id := $careServicesRequest/facility/@id
let $pid := $careServicesRequest/facility/parent/@id
let $code := $careServicesRequest/facility/@code
let $urn := $uuid_generate($id)
let $parent := <csd:organizations><csd:organization entityID="{$pid}"/></csd:organizations>
let $name := $careServicesRequest/facility/name
let $type := $careServicesRequest/facility/type
let $time := current-dateTime()
let $fac :=
if (($name) and ($id)  and ($urn) )
     then
       <csd:facility entityID="{$urn}">
				 <csd:otherID assigningAuthorityName='http://hfrportal.ehealth.go.tz' code='id'>{string($id)}</csd:otherID>
			   <csd:otherID assigningAuthorityName='http://hfrportal.ehealth.go.tz' code='Fac_IDNumber'>{string($code)}</csd:otherID>
			   <csd:extension type='facilityType' urn='urn:uuid:hfrportal:ehealth:go:tz'><facilityType>{string($type)}</facilityType></csd:extension>
				 <csd:primaryName>{string($name)}</csd:primaryName>
				 {$parent}
			   <csd:record created="{$time}" updated="{$time}" status="Active" sourceDirectory="http://hfrportal.ehealth.go.tz"/>
       </csd:facility>
     else ()  (:no name or id or type :)

let $existing := if (exists($fac/@entityID)) then csd_bl:filter_by_primary_id(/CSD/facilityDirectory/*,$fac) else ()
return
  if (exists($existing))
  then (
    if(exists($fac/extension[@type = "facilityType"]) and exists($existing/extension[@type = "facilityType"]) )
    then (replace node $existing/extension[@type = "facilityType"] with $fac/extension[@type = "facilityType"])
    else 
      if(exists($fac/extension[@type = "facilityType"]))
      then insert node $fac/extension[@type = "facilityType"] into $existing
      else (),
    if(exists($fac/primaryName) and exists($existing/primaryName) )
    then (replace node $existing/primaryName with $fac/primaryName)
    else 
      if(exists($fac/primaryName))
      then insert node $fac/primaryName into $existing
      else (),

    if(exists($fac/organizations) and exists($existing/organizations) )
    then (replace node $existing/organizations with $fac/organizations)
    else 
      if(exists($fac/organizations))
      then insert node $fac/organizations into $existing
      else ()
  )
  else
  insert node $fac into /CSD/facilityDirectory