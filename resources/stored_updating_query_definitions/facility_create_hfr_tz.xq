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
let $code := $careServicesRequest/facility/@code
let $urn := $uuid_generate($id)
let $parent := <csd:organizations><csd:organization entityID="{$pid}"/></csd:organizations>
let $name := $careServicesRequest/facility/name
let $fac :=
if (($name) and ($id)  and ($urn) )
     then
       <csd:facility entityID="{$urn}">
	 <csd:otherID assigningAuthorityName='http://hfrportal.ehealth.go.tz' code='id'>{string($code)}</csd:otherID>
   <csd:otherID assigningAuthorityName='http://hfrportal.ehealth.go.tz' code='Fac_IDNumber'>{string($id)}</csd:otherID>
	 <csd:primaryName>{string($name)}</csd:primaryName>
	 {$parent}
       </csd:facility>
     else ()  (:no name or id or type :)


let $t1:= trace($careServicesRequest,"CSR is ")

return insert node $fac into /CSD/facilityDirectory
