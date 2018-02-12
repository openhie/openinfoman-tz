const winston = require('winston');
const request = require('request')
const xpath = require('xpath')
const Dom = require('xmldom').DOMParser
var https = require('https');
var http = require('http');

https.globalAgent.maxSockets = 32;
http.globalAgent.maxSockets = 32;
winston.remove(winston.transports.Console)
winston.add(winston.transports.Console, {level: 'info', timestamp: true, colorize: true})
var logger = new (winston.Logger)({
    transports: [
      new (winston.transports.Console)({ level: 'info' }),
      new (winston.transports.File)({
        filename: 'logs.log',
        level: 'error'
      })
    ]
  });

var server = http.createServer(function(req, res) {
  res.writeHead(200);
  res.end('Hi everybody!');
});

server.listen(8888);

  get_facilities("http://resourcemap.instedd.org/api/collections/409.json?human=false")

  function get_facility_parent(admin_div,callback) {
    var csd_msg = `<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
                    <csd:otherID assigningAuthorityName="http://hfrportal.ehealth.go.tz" code="code">${admin_div}</csd:otherID>
                   </csd:requestParams>`
     var url = "http://localhost:8984/CSD/csr/hfr/careServicesRequest/urn:openhie.org:openinfoman-hwr:stored-function:organization_get_urns"
     var options = {
       url: url.toString(),
       headers: {
         'Content-Type': 'text/xml'
       },
       body: csd_msg
     }

     request.post(options, (err, res, body) => {
       callback(body)
     })
  }

  function create_facility(facilities) {
    facilities.forEach(facility=>{
      get_facility_parent(facility.properties.Admin_div,(parent)=>{
        const doc = new Dom().parseFromString(parent)
        const select = xpath.useNamespaces({'csd': 'urn:ihe:iti:csd:2013'})
        var parent_id = select('string(/csd:CSD/csd:organizationDirectory/csd:organization/@entityID)', doc)
        var name = facility.name
        name = name.replace("&","&amp;")
        var id = facility.id
        var code = facility.properties.Fac_IDNumber
        var fac_type = facility.properties.Fac_Type
        var url = "http://localhost:8984/CSD/csr/hfr_php/careServicesRequest/update/urn:openhie.org:openinfoman-tz:facility_create_hfr_tz"
        var csd_msg = `<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
                        <csd:facility id="${id}" code="${code}">
                          <csd:parent id="${parent_id}" />
                          <csd:name>${name}</csd:name>
                          <csd:type>${fac_type}</csd:type>
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
          winston.error(name)
          if(body)
          logger.error(body)
        })
      })
    })
  }

  function get_facilities(url) {
    var username = ""
    var password = ""
    var orgs = []
    var auth = "Basic " + new Buffer(username + ":" + password).toString("base64");
    var options = {
      url: url.toString(),
      headers: {
        Authorization: auth
      }
    }
    request.get(options, (err, res, body) => {
      if (err) {
        return callback(err)
      }
      var body = JSON.parse(body)
      if(body.nextPage)
      get_facilities(body.nextPage)
      create_facility(body.sites)
    })
  }
