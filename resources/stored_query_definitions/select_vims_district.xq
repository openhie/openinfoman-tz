import module namespace csd_bl = "https://github.com/openhie/openinfoman/csd_bl";
declare default element  namespace   "urn:ihe:iti:csd:2013";
declare variable $careServicesRequest as item() external;

let $districts :=
<json type='object'>
    <districts type="array">
    {
      for $facility in  /CSD/facilityDirectory/facility
      let $distr := lower-case(string(facility/extension))
      return
	if (true())
       then
         <_  type="object">
	   <name>{$distr}</name>
         </_>
       else ()
    }
    </districts>
  </json>

return json:serialize($districts,map{"format":"direct"})
