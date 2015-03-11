openinfoman-tz
====================

OpenInfoMan TZ

Converting an organizational hierarchy for Tanzania into a CSD document.  


Ubuntu Installation
===================
You can easily install on Ubuntu 14.04 and Ubuntu 14.10 using the following commands
<pre>
sudo add-apt-repository ppa:openhie/release
sudo apt-get update
sudo apt-get install openinfoman-tz
</pre>

Once installed, you should be able to access OpenInfoMan at:
> http://localhost:8984/CSD



Manual Installation
===================

Assumes that you have installed BaseX and OpenInfoMan according to:
> https://github.com/openhie/openinfoman/wiki/Install-Instructions


Directions
==========
To get the libarary:
<pre>
cd ~/
git clone https://github.com/openhie/openinfoman-tz
</pre>

Library Module
--------------
There is no library module at the time of writing.


Stored Functions
----------------
To install the stored functions you can do: 
<pre>
cd ~/basex/resources/stored_query_definitions
ln -sf ~/openinfoman-tz/resources/stored_query_definitions/* .
ln -sf ~/openinfoman-tz/resources/stored_updating_query_definitions/* .
</pre>
Be sure to reload the stored functions: 
> https://github.com/openhie/openinfoman/wiki/Install-Instructions#Loading_Stored_Queries


RapidPro Endpoints
--------------
To make the GET endpoints available:  
<pre>
cd ~/basex/webapp
ln -sf ~/openinfoman-tz/webapp/openinfoman_resourcemap_bindings.xqm
</pre>

