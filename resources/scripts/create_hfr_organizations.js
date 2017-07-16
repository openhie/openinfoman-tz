const winston = require('winston');
const request = require('request')
var https = require('https');
var http = require('http');

https.globalAgent.maxSockets = 32;
http.globalAgent.maxSockets = 32;
//const URI = require('urijs')
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
  winston.info(res);
  res.writeHead(200);
  res.end('Hi everybody!');
});

server.listen(8888);

  var url = "http://resourcemap.instedd.org/en/collections/409/fields/13274"
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

  function get_parent_details(orgs,parent_id) {
    return orgs.find(org => {
      return org.id == parent_id
    })
  }

  var loop_counter = {}
  function extract_orgs(org,parent) {
    loop_counter[parent] = org.length
    for(var k = 0;k<org.length;k++) {
      if("sub" in org[k]) {
        extract_orgs(org[k].sub,org[k].id)
      }
      loop_counter[parent]--
      if(loop_counter[parent] === 0)
      delete loop_counter[parent]
      orgs.push({"name":org[k].name,"id":org[k].id,"parent":parent})
    }

    if(!Object.keys(loop_counter).length) {
      return orgs;
    }
  }

  function create_orgs (orgs) {
    orgs.forEach(org=>{
      var parent_id = org.parent
      var org_id =org.id
      var org_name = org.name
      org_name = org_name.replace("&","&amp;")
      if(parent_id != "Top") {
        var parent = get_parent_details(orgs,org.parent)
        var parent_name = parent.name
        parent_name = parent_name.replace("&","&amp;")
      }
      var csd_msg = `<csd:requestParams xmlns:csd="urn:ihe:iti:csd:2013">
                      <csd:organization id="${org_id}">
                        <csd:name>${org_name}</csd:name>
                        <csd:parent id="${parent_id}" name="${parent_name}"/>
                      </csd:organization>
                    </csd:requestParams>`
      var url = "http://localhost:8984/CSD/csr/hfr/careServicesRequest/update/urn:openhie.org:openinfoman-tz:organization_create_hfr_tz"
      var options = {
        url: url.toString(),
        headers: {
          'Content-Type': 'text/xml'
        },
        body: csd_msg
      }

      request.post(options, (err, res, body) => {
        winston.info("\n" + csd_msg)
        if(body)
        logger.error(body)
      })

    })
  }

  request.get(options, (err, res, body) => {
    if (err) {
      return callback(err)
    }
    var body = JSON.parse(body)
    var orgs = extract_orgs(body.config.hierarchy,"Top")
    create_orgs(orgs)
  })
