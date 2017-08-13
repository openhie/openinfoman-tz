'use strict'
const winston = require('winston')
const request = require('request')
const util = require('util')
const XmlReader = require('xml-reader')
const xmlQuery = require('xml-query')
const fs = require('fs')
var execPhp = require('exec-php')
const express = require('express')
const app = express()
const URI = require('urijs')

winston.remove(winston.transports.Console)
winston.add(winston.transports.Console, {level: 'info', timestamp: true, colorize: true})

var url = 'http://localhost:8984/CSD/csr/facOrgsRegistry/careServicesRequest/urn:openhie.org:openinfoman-hwr:stored-function:organization_get_all'
var username = ""
var password = ""
var auth = "Basic " + new Buffer(username + ":" + password).toString("base64")
var csd_msg = `<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
                <csd:codedType code="4" codingScheme="2.25.123494412831734081331965080571820180508"/>
               </csd:requestParams>`
var options = {
  url: url,
  headers: {
    'Content-Type': 'text/xml'
     },
     body: csd_msg
}
request.post(options, function (err, res, body) {
  if (err) {
    return callback(err)
  }
  var ast = XmlReader.parseSync(body)
  var orgsLength = xmlQuery(ast).find("organizationDirectory").children().size()
  var orgs = xmlQuery(ast).find("organizationDirectory").children()
  for(var counter=0;counter<orgsLength;counter++){
    var uuid = orgs.eq(counter).attr("entityID")
    var name = orgs.eq(counter).children().find("csd:primaryName").text()
    var url = "http://localhost:8984/CSD/csr/facOrgsRegistry/careServicesRequest/update/urn:openhie.org:openinfoman-tz:facility_create_dvs_tz"
    var csd_msg = `<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
                    <csd:facility id="${uuid}">
                      <csd:parent id="${uuid}" />
                      <csd:name>${name}</csd:name>
                      <csd:type>DVS</csd:type>
                    </csd:facility>
                   </csd:requestParams>`
     var options = {
       url: url.toString(),
       headers: {
         'Content-Type': 'text/xml'
       },
       body: csd_msg
     }
     request.post(options, (err, res, body) => {
       winston.error(csd_msg)
       if(body)
       winston.error(body)
     })

  }


})
