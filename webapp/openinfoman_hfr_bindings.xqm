module namespace page = 'http://basex.org/modules/web-page';

(:Import other namespaces.  :)
import module namespace csd_webconf =  "https://github.com/openhie/openinfoman/csd_webconf";
import module namespace csd_webui =  "https://github.com/openhie/openinfoman/csd_webui";
import module namespace csr_proc = "https://github.com/openhie/openinfoman/csr_proc";
import module namespace csd_dm = "https://github.com/openhie/openinfoman/csd_dm";
import module namespace csd_mcs = "https://github.com/openhie/openinfoman/csd_mcs";
import module namespace functx = "http://www.functx.com";

declare namespace csd = "urn:ihe:iti:csd:2013";

declare function page:is_hfr($search_name) {
  let $function := csr_proc:get_function_definition($search_name)
  let $ufunction := csr_proc:get_updating_function_definition($search_name)
  let $ext := $function//csd:extension[  @urn='urn:openhie.org:openinfoman:adapter' and @type='hfr']
  let $uext := $ufunction//csd:extension[  @urn='urn:openhie.org:openinfoman:adapter' and @type='hfr']
  return (count($uext) + count($ext) > 0) 
};


declare function page:get_actions($search_name) {
  let $function := csr_proc:get_function_definition($search_name)
  let $ufunction := csr_proc:get_updating_function_definition($search_name)
  return 
    (
    for $act in $function//csd:extension[  @urn='urn:openhie.org:openinfoman:adapter:hfr:action']/@type
    return string($act)
    ,for $act in $ufunction//csd:extension[  @urn='urn:openhie.org:openinfoman:adapter:hfr:action']/@type
    return string($act)
  )
};



declare
  %rest:path("/CSD/csr/{$doc_name}/careServicesRequest/{$search_name}/adapter/hfr")
  %output:method("xhtml")
  function page:show_endpoints($search_name,$doc_name) 
{  
    if (not(page:is_hfr($search_name)) ) 
      then ('Not a HFR Compatible stored function'    )
    else 
      let $actions := page:get_actions($search_name)
      let $contents := 
      <div>
        <h2>HFR Operations on {$doc_name}</h2>
        { 
          if ($actions = 'upload')  
	  then
	   <span>
             <h3>Upload Organizational Hierarchy</h3>
	     {
	       let $function := csr_proc:get_updating_function_definition($search_name)
	       let $oid := string($function/csd:extension[@urn='urn:openhie.org:openinfoman:adapter:hfr:action:upload:oid']/@type)		 
	       let $url := concat($csd_webconf:baseurl, "CSD/csr/" , $doc_name , "/careServicesRequest/",$search_name, "/adapter/hfr/upload")
	       return 
	         <form action="{$url}" method="POST" enctype="multipart/form-data">
		   <label for='csv' >HFR Organizational Hierarchy File</label>
		   <input type='file' name='csv'/>
		   <br/>
		   <label for='oid' >Root OID for SVS list ID</label>
		   <input type='text' size='60' value="{$oid}" name='oid'/>
		   <br/>
		   <input type='submit' value='Upload'/>
		 </form>
	     }
	   </span>
	  else ()
	}
      </div>
      return csd_webui:wrapper($contents)
};


 
declare updating
  %rest:path("/CSD/csr/{$doc_name}/careServicesRequest/{$search_name}/adapter/hfr/upload")
  %rest:POST
  %rest:form-param("csv", "{$csv}")
  %rest:form-param("oid", "{$oid}",'')
  function page:update_doc($search_name,$doc_name,$csv,$oid)
{
  if (not(page:is_hfr($search_name)) ) then
    db:output(<restxq:redirect>{$csd_webconf:baseurl}CSD/bad</restxq:redirect>)
  else 
    let $function := csr_proc:get_updating_function_definition($search_name)

    let $d_oid := string($function/csd:extension[@urn='urn:openhie.org:openinfoman:adapter:dhis2:action:uploadDXF:oid']/@type)
    
    let $s_oid := if ($oid = '') then $d_oid else $oid


    let $name :=  map:keys($csv)[1]
    let $content := convert:binary-to-string($csv($name))


    let $careServicesRequest := 
      <csd:careServicesRequest>
       <csd:function urn="{$search_name}" resource="{$doc_name}" base_url="{$csd_webconf:baseurl}">
         <csd:requestParams >
           <csv>{$content}</csv>
           <oid>{$s_oid}</oid>
         </csd:requestParams>
       </csd:function>
      </csd:careServicesRequest>
    return 
       (
        csr_proc:process_updating_CSR_results($csd_webconf:db, $careServicesRequest)
        ,db:output(<restxq:redirect>{$csd_webconf:baseurl}CSD</restxq:redirect>)
       )

};
