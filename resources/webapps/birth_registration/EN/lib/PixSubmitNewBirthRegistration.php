<?php
require_once('PixSubmit.php');

class PixSumbitNewBirthRegistration extends PixSumbit {
    function  create_submission($request) {
    	$client_uuid = $this->client_uuid;
	if (!is_array($request)
	    || !array_key_exists('birth_details',$request)
	    ) {
	    error_log("No birth details");
	    header('Content-Type: application/json');
            echo '{
		   "error":"No Birth Details Submitted",
		   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details Please"
		  }';
	    return false;
	}
	$gender ='';
	$birth_details=$request["birth_details"];
	$birth_details=explode(" ",$birth_details);
	if (count($birth_details) < 5) {
	   header('Content-Type: application/json');
            echo '{
                   "error":"SOme Birth Details Are Missing",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
	   error_log("Not enough birth details");
	   return false;
	}
	if((string)(int)$birth_details[0]==$birth_details[0]) {
            header('Content-Type: application/json');
            echo '{
                   "error":"Mother's First Name Is Missing",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
           error_log("Missing Mother's First Name");
           return false;
		}
	if((string)(int)$birth_details[1]==$birth_details[1]) {
           header('Content-Type: application/json');
            echo '{
                   "error":"Mother's Surname Is Missing",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
           error_log("Missing Mother's Surname");
           return false;
           }
	//check if year have been specified
	if((string)(int)$birth_details[4]==$birth_details[4]) {
		$year=(int) $birth_details[4];
		if($year<1000) {
			echo '{
                   		"error":"Invalid Year,Year Should Contain 4 Digits",
                                "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  	      }';
           		error_log("Wrong Year Format");
		        return false;
			}
		}
	else
	$year=date("Y");

	//capture gender and child name
        if((string)(int)$birth_details[4]==$birth_details[4]) {
                $gender=$birth_details[5];
                if(array_key_exists(6,$birth_details)) {
                	$child_fname=ucfirst($birth_details[6]);
			$child_surname=ucfirst($birth_details[7]);
			}
                }
        else if((string)(int)$birth_details[4]!=$birth_details[4]) {
                $gender=$birth_details[4];
                if(array_key_exists(5,$birth_details)) {
	                $child_fname=ucfirst($birth_details[5]);
			$child_surname=ucfirst($birth_details[6]);
			}
                }
	if(strtoupper($gender)=="ME" or strtoupper($gender)=="M"){
	    $gender="M";
	} else if(strtoupper($gender)=="KE" or strtoupper($gender)=="F") {
	    $gender="F";
	} else {
	    error_log("Bad gender");
	    header('Content-Type: application/json');
            echo '{
                   "error":"Invalid Gender",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
	    return false;
	}
	$mother_fname=ucfirst($birth_details[0]);
        $mother_surname=ucfirst($birth_details[1]);

	$dd=(int) $birth_details[2];
	$mm=(int) $birth_details[3];
	if ( $dd < 1 || $dd > 31) {
           header('Content-Type: application/json');
	   error_log("bad day ($dd)");
            echo '{
                   "error":"Invalid Birth Date(Day)",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
	   return false;
	}		
	if ($mm < 1 || $mm > 12) {
	   header('Content-Type: application/json');
	   error_log("bad month ($mm)");
            echo '{
                   "error":"Invalid Birth Date (Month)",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
	   return false;
	}		
	$dob=$year . sprintf("%02d", $mm). sprintf("%02d", $dd);
        $check_dob=date("Y-m-d",strtotime($year."-".$mm."-".$dd));
	$today=date("Y-m-d");
	if($check_dob > $today) {
                header('Content-Type: application/json');
		error_log("date of birth greater than current date");
		echo '{
                      "error":"Invalid Birth Date (After Today's Date",
                      "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                      }';
		return false;
		}
	if (! array_key_exists('orgid',$request) 
	    || !($orgid=$request["orgid"])
            || !($orgcode=$request["orgcode"])
	    || ! substr($orgid,0,9) == 'urn:uuid:'
	    || ! ($org_uuid = substr($orgid,9))
	    ) {
	   header('Content-Type: application/json');
	   error_log("No org id");
            echo '{
                   "error":"Cant See Your Village/Facility",
                   "response":"Birth Details Of the Child Of '.$request["birth_details"].' Not Registered,Resend Birth Details"
                  }';
	    return false;
	}
$submission='﻿<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:urn="urn:hl7-org:v3">
  <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
    <wsa:Action wsa:mustUnderstand="1">urn:hl7-org:v3:PRPA_IN201301UV02</wsa:Action>
    <wsa:MessageID>urn:uuid:22a0f112-4424-f00c-418e-17903edc6807</wsa:MessageID>
    <wsa:To wsa:mustUnderstand="1">https://ec2-54-148-48-20.us-west-2.compute.amazonaws.com:8443/PIXManager</wsa:To>
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
          <telecom value="https://ec2-54-148-48-20.us-west-2.compute.amazonaws.com:8443/PIXManager"/>
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
		 <name>
		   <family>'.$child_surname.'</family>
		   <given>'.$child_fname.'</given>
		 </name>
		 <administrativeGenderCode code="'.$gender.'"/>
		 <birthTime value="'.$dob.'"/>
		 <addr>
		   <censusTract>'.$orgcode.'</censusTract>
		   <country>TZ</country>
 		 </addr>
		 <personalRelationship classCode="PRS">
		   <code code="MTH"/>
    		   <statusCode code="active"/>
		   <relationshipHolder1 determinerCode="INSTANCE">
		     <name use="L">
		       <family>'.$mother_surname.'</family>
		       <given>'.$mother_fname.'</given>
		     </name>
		   </relationshipHolder1>
		 </personalRelationship>
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
                  <name>Rapidpro</name>
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
    
