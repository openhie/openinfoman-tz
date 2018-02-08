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

let $dhisid := $careServicesRequest/organization/@id
let $pid := $careServicesRequest/organization/parent/@id
let $urn := $uuid_generate($dhisid)
let $parent :=
     if ($pid)
     then
       let $parent_urn := $uuid_generate($pid)
       return <csd:parent entityID="{$parent_urn}"/>
     else ()
let $name := $careServicesRequest/organization/name
let $org :=
if (($name) and ($dhisid)  and ($urn) )
     then
       <csd:organization entityID="{$urn}">
	 <csd:otherID assigningAuthorityName='tanzania-hmis' code='id'>{string($dhisid)}</csd:otherID>
	 <csd:primaryName>{string($name)}</csd:primaryName>
	 {$parent}
       </csd:organization>
     else ()  (:no name or id or type :)

let $t0:= trace($org,"Org is ")
let $t0:= trace($dhisid/string(),"ID is ")
let $t0:= trace($parent,"Parent is ")
let $t1:= trace($urn,"URN IS")
let $t1:= trace($name/string(),"Name IS")
let $t1:= trace(/CSD/organizationDirectory,"Dir is ")
let $t1:= trace(/,"Doc is ")
let $t1:= trace($careServicesRequest,"CSR is ")

let $existing := if (exists($org/@entityID)) then csd_bl:filter_by_primary_id(/CSD/organizationDirectory/*,$org) else ()
return
if (exists($existing))
then replace node $existing with $org
else insert node $org into /CSD/organizationDirectory
