import module namespace functx = "http://www.functx.com";
declare namespace csd = "urn:ihe:iti:csd:2013";
declare variable $careServicesRequest as item() external;

let $t_top_orgid  :=  $careServicesRequest/csd:requestParams/query[@name = 'orgid']/text()
let $top_org :=  (/csd:CSD/csd:organizationDirectory/csd:organization[@entityID = $t_top_orgid])[1]

let $selected_text := $careServicesRequest/csd:requestParams/query[@name = 'input']/text()

let $selected_index := if ($selected_text castable as xs:integer) then xs:integer($selected_text) else -1

let $selected :=
  if ($selected_index = 0)
  then
    /csd:CSD/csd:organizationDirectory/csd:organization[
      @entityID = /csd:CSD/csd:organizationDirectory/csd:organization[@entityID = $top_org/@entityID]/csd:parent/@entityID
    ]
  else if ($selected_index > 0)
  then
    let $select_orgs := 
      if (exists($top_org))
      then /csd:CSD/csd:organizationDirectory/csd:organization[./csd:parent/@entityID = $top_org/@entityID]
      else /csd:CSD/csd:organizationDirectory/csd:organization[count(./csd:parent[@entityID]) = 0]
    return ($select_orgs)[$selected_index]
  else ()

    
let $orgs := 
  if (exists($selected) and  local-name($selected ) = 'organization')
  then /csd:CSD/csd:organizationDirectory/csd:organization[./csd:parent/@entityID = $selected/@entityID]
  else /csd:CSD/csd:organizationDirectory/csd:organization[count(./csd:parent[@entityID]) = 0]

let $output := 
  if ( $selected[./csd:codedType/@code=7])
  then
    <json type='object'>
      <menutext>Facility Selected</menutext>
      <facility>{string($selected/@entityID)}</facility>
      <facilityname>{$selected/csd:primaryName/text()}</facilityname>
      <orgcode>{string($selected/csd:otherID/@code)}</orgcode>
    </json>
  else  if ( local-name($selected ) = 'organization'  or not(exists($selected)))
  then
    <json type='object'>
      <menutext >
 	{ if (exists($selected)) then concat("In ", $selected/csd:primaryName/text(), ". ")  else () }
	{
	  let $parent := (/csd:CSD/csd:organizationDirectory/csd:organization[@entityID = $selected/csd:parent/@entityID])[1]/csd:primaryName/text()
	  return
	    if ($parent)
	    then concat("O) Up to ", $parent, " ") 
	    else ()
        }
	{ for $ent at $pos in ($orgs)  return concat( $pos, ") ", $ent/csd:primaryName/text())    }
      </menutext>
      <orgid>{if (exists($selected)) then string($selected/@entityID) else ()}</orgid>
      <orgcode>{string($selected/csd:otherID/@code)}</orgcode>
      <facility/>
      <facilityname/>      
    </json>
   else <json type='object'/>

(:return $output :)
return json:serialize($output,map{'format':'direct'})
