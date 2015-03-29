<?php

require_once('PixSubmit.php');

class PixSumbitNewBirthRegistration extends PixSumbit {

    function  create_submission($request) {
    	$client_uuid = uuid_create();
	if (!is_array($request)
	    || !array_key_exists('birth_details',$request)
	    ) {
	    error_log("No birth details");
	    return false;
	}
	$gender ='';
	$birth_details=$request["birth_details"];
	$birth_details=explode(" ",$birth_details);
	if (count($birth_details) < 5) {
	   error_log("Not enough birth details");
	   return false;
	}	   
	if(strtoupper($birth_details[0])=="M") {
	    $gender="M";
	} else if(strtoupper($birth_details[0])=="K" or strtoupper($birth_details[0])=="F") {
	    $gender="F";
	} else {
	    error_log("Bad gender");
	    return false;
	}
	$mother_surname=$birth_details[3];	
	$mother_name=$birth_details[4];


	$year=date("Y");
	$dd=(int) $birth_details[1];
	$mm=(int) $birth_details[2];
	if ( $dd < 1 || $dd > 31) {
	   error_log("bad day ($dd)");
	   return false;
	}		
	if ($mm < 1 || $mm > 12) {
	   error_log("bad month ($mm)");
	   return false;
	}		
	
	$dob=$year . sprintf("%02d", $mm). sprintf("%02d", $dd);
	if (! array_key_exists('orgid',$request) 
	    || !($orgid=$request["orgid"])
	    || ! substr($orgid,0,9) == 'urn:uuid:'
	    || ! ($org_uuid = substr($orgid,9))
	    ) { 
	   error_log("No org id");
	    return false;
	}
	
$submission='﻿<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:urn="urn:hl7-org:v3">
  <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
    <wsa:Action wsa:mustUnderstand="1">urn:hl7-org:v3:PRPA_IN201301UV02</wsa:Action>
    <wsa:MessageID>urn:uuid:22a0f112-4424-f00c-418e-17903edc6807</wsa:MessageID>
    <wsa:To wsa:mustUnderstand="1">http://142.222.45.60:8080/PIXManager</wsa:To>
    <wsa:ReplyTo>
      <wsa:Address>http://www.w3.org/2005/08/addressing/anonymous</wsa:Address>
    </wsa:ReplyTo>
  </soap:Header>
  <soap:Body>
    <PRPA_IN201301UV02 xsi:schemaLocation="urn:hl7-org:v3 ../../schema/HL7V3/NE2008/multicacheschemas/PRPA_IN201301UV02.xsd" ITSVersion="XML_1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:hl7-org:v3">
      <id root="22a0f112-4424-f00c-418e-17903edc6807"/>
      <creationTime value="20150803130624"/>
      <interactionId root="2.16.840.1.113883.1.6" extension="PRPA_IN201301UV02"/>
      <processingCode code="P"/>
      <processingModeCode code="R"/>
      <acceptAckCode code="AL"/>
      <receiver typeCode="RCV">
        <device classCode="DEV" determinerCode="INSTANCE">
          <id root="1.3.6.1.4.1.33349.3.1.5.102.1"/>
          <telecom value="https://142.222.45.60:8080/PIXManager"/>
        </device>
      </receiver>
      <sender typeCode="SND">
        <device classCode="DEV" determinerCode="INSTANCE">
          <id root="'.$this->pix_client_id .'" />
        </device>
      </sender>
      <controlActProcess classCode="CACT" moodCode="EVN">
        <subject typeCode="SUBJ">
          <registrationEvent classCode="REG" moodCode="EVN">
            <id nullFlavor="NA"/>
            <statusCode code="active"/>
            <subject1 typeCode="SBJ">
              <patient classCode="PAT">
                <!--RAPIDPRO Identifier-->
                <id root="'.$this->pix_client_id.'" extension="' . $client_uuid . '"/>
                <statusCode code="active"/>
                <patientPerson>
                  <name use="L">
                    <family>'.$mother_surname.'</family>
                    <given>'.$mother_name.'</given>
                  </name>
                  <administrativeGenderCode code="'.$gender.'"/>
                  <birthTime value="'.$dob.'"/>
                  <addr>
                    <censusTract>'.$org_uuid.'</censusTract>
                    <postalCode>10293</postalCode>
                    <country>TZ</country>
                  </addr>
                </patientPerson>
                <providerOrganization classCode="ORG" determinerCode="INSTANCE">
                  <id root="'.$this->pix_client_id.'"/>
                  <name>RAPIDPRO</name>
                  <contactParty classCode="CON">
                    <telecom value="tel:+255683088392"/>
                  </contactParty>
                </providerOrganization>
              </patient>
            </subject1>
            <custodian typeCode="CST">
              <assignedEntity classCode="ASSIGNED">
                <id root="'.$this->pix_client_id.'"/>
                <assignedOrganization classCode="ORG" determinerCode="INSTANCE">
                  <name>RAPIDPRO</name>
                </assignedOrganization>
              </assignedEntity>
            </custodian>
          </registrationEvent>
        </subject>
      </controlActProcess>
    </PRPA_IN201301UV02>
  </soap:Body>
</soap:Envelope>';

return $submission;
    }
}
    
