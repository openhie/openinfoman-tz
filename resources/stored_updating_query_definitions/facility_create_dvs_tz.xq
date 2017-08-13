import module namespace util = "https://github.com/openhie/openinfoman-dhis/util";
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
let $urn := $uuid_generate($id)
let $parent := <csd:organizations><csd:organization entityID="{$pid}"/></csd:organizations>
let $name := concat($careServicesRequest/facility/name," DVS")
let $type := $careServicesRequest/facility/type
let $time := current-dateTime()
let $fac :=
if (($name) and ($id)  and ($urn) )
     then
       <csd:facility entityID="{$urn}">
   <csd:extension type='facilityType' urn='urn:openhie.org:openinfoman-tz'><facilityType>{string($type)}</facilityType></csd:extension>
	 <csd:primaryName>{string($name)}</csd:primaryName>
	 {$parent}
   <csd:record created="{$time}" updated="{$time}" status="Active" sourceDirectory="openhie.org:openinfoman-tz"/>
       </csd:facility>
     else ()  (:no name or id or type :)


let $t1:= trace($careServicesRequest,"CSR is ")

return insert node $fac into /CSD/facilityDirectory
