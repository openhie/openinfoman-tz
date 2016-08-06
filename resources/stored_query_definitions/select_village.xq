import module namespace functx = "http://www.functx.com";
declare namespace csd = "urn:ihe:iti:csd:2013";
declare variable $careServicesRequest as item() external;

let $t_top_orgid  :=  $careServicesRequest/query[@name = 'orgid']/text()
let $top_org :=  (/csd:CSD/csd:organizationDirectory/csd:organization[@entityID = $t_top_orgid])[1]



let $selected_text := $careServicesRequest/query[@name = 'input']/text()
(:
let $selections := $careServicesRequest/values/item[./pair[@name = 'label' and ./text() = 'facilityselection']]
let $selected_text := 
  if (count($selections) > 0)
  then
    let $max_time := max(for $time in $selections/pair[@name='time'] return xs:dateTime($time))
    return $selections[./pair[@name = 'time'] = $max_time]/pair[@name='text']/text()
   else ()
:)

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
	
    let $select_facs := 
      if (exists($top_org))
      then /csd:CSD/csd:facilityDirectory/csd:facility[./csd:organizations/csd:organization[@entityID = $top_org/@entityID]]
      else ()
    return ($select_orgs,$select_facs)[ $selected_index]
  else ()

    
let $orgs := 
  if (exists($selected) and  local-name($selected ) = 'organization')
  then /csd:CSD/csd:organizationDirectory/csd:organization[./csd:parent/@entityID = $selected/@entityID]
  else /csd:CSD/csd:organizationDirectory/csd:organization[count(./csd:parent[@entityID]) = 0]
	
let $facs := 
  if (exists($selected) and  local-name($selected ) = 'organization')
  then /csd:CSD/csd:facilityDirectory/csd:facility[./csd:organizations/csd:organization[@entityID = $selected/@entityID]]
  else ()



let $output := 
  if ( local-name($selected ) = 'facility')
  then
    <json type='object'>
      <menutext>You already selected a facility.  You shouldn't see this message</menutext>
      <facility>{string($selected/@entityID)}</facility>
      <facilityname>{$selected/csd:primaryName/text()}</facilityname>
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
	{ for $ent at $pos in ($orgs,$facs)  return concat( $pos, ") ", $ent/csd:primaryName/text())    }
      </menutext>
      <orgid>{if (exists($selected)) then string($selected/@entityID) else ()}</orgid>
      <orgcode>{if (exists($selected)) then string($selected/csd:otherID/@code) else ()}</orgcode>
      <village>{if ($selected/csd:codedType/@code=6) then string($selected/@entityID) else ()}</village>
      <villagename>{if ($selected/csd:codedType/@code=6) then string($selected/csd:primaryName/text()) else ()}</villagename>      
    </json>
   else <json type='object'/>

(:return $output :)
return json:serialize($output,map{'format':'direct'})