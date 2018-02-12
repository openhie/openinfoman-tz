import module namespace csd_bl = "https://github.com/openhie/openinfoman/csd_bl";
declare default element  namespace   "urn:ihe:iti:csd:2013";
declare variable $careServicesRequest as item() external;

let $districts :=
<json type='object'>
    <districts type="array">
    {
      for $organization in  /CSD/organizationDirectory/organization
      let $distr := $organization[extension[@type='orgType' and ./text()="district"]]
      return
  if (exists($distr))
       then
         <_  type="object">
     <name>{$distr/primaryName/text()}</name>
     <id>{$organization/@entityID/string()}</id>
         </_>
       else ()
    }
    </districts>
  </json>

return json:serialize($districts,map{"format":"direct"})
