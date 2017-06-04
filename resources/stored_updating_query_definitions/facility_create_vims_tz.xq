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

let $vimsid := $careServicesRequest/facility/@id
let $code := $careServicesRequest/facility/code
let $name := $careServicesRequest/facility/name
let $district := $careServicesRequest/facility/district
let $gln := $careServicesRequest/facility/gln
let $ftype := $careServicesRequest/facility/ftype
let $urn := $uuid_generate(string($vimsid))
let $org :=
if (($name) and ($vimsid)  and ($urn) )
     then
       <csd:facility entityID='{$urn}'>
	 <csd:otherID assigningAuthorityName='https://vims.moh.go.tz' code='id'>{string($vimsid)}</csd:otherID>
   <csd:otherID assigningAuthorityName='https://vims.moh.go.tz' code='code'>{string($code)}</csd:otherID>
   <csd:otherID assigningAuthorityName='https://vims.moh.go.tz' code='GLN'>{string($gln)}</csd:otherID>
   <csd:extension type='geographicZone' urn='https://vims.moh.go.tz'>{string($district)}</csd:extension>
   <csd:extension type='facilityType' urn='https://vims.moh.go.tz'>{string($ftype)}</csd:extension>
	 <csd:primaryName>{string($name)}</csd:primaryName>
       </csd:facility>
     else ()  (:no name or id or urn :)

return insert node $org into /CSD/facilityDirectory
