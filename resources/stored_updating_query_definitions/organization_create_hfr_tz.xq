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

let $id := $careServicesRequest/organization/@id
let $name := $careServicesRequest/organization/name/text()
let $pid := $careServicesRequest/organization/parent/@id
let $pname := $careServicesRequest/organization/parent/@name
let $urn := $uuid_generate(concat($name,$id))
let $parent :=
     if ($pid)
     then
       let $parent_urn := $uuid_generate(concat($pname,$pid))
       return <csd:parent entityID="{$parent_urn}"/>
     else ()
let $level := count(tokenize($id,"[\s.]"))-1
let $org :=
if (($name) and ($level)  and ($urn) )
     then
       <csd:organization entityID="{$urn}">
	 <csd:otherID assigningAuthorityName='resource_map_tanzania' code="{$id}"/>
	 <csd:codedType code="{$level}" codingScheme='2.25.123494412831734081331965080571820180508'/>
	 <csd:primaryName>{$name}</csd:primaryName>
	 {$parent}
       </csd:organization>
     else ()  (:no name or id or type :)

let $t0:= trace($org,"Org is ")
let $t1:= trace(/CSD/organizationDirectory,"Dir is ")
let $t1:= trace(/,"Doc is ")
let $t1:= trace($careServicesRequest,"CSR is ")

return insert node $org into /CSD/organizationDirectory
