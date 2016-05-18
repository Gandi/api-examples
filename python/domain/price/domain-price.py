#!/usr/bin/python
# 
# Output the creation price and phases of a specific domain name
# (premium domain names included)
# 
# Example: ./domain-pricy.py your-random-domain-name.net
# Needs environment variable "GANDI_API_KEY" set with your Gandi API key
# More details on this API: http://doc.rpc.gandi.net/domain/reference.html?#domain.price
# 
import xmlrpclib
import time
import sys
import os

def handle_error( error_code):
   print 'invalid parameters or internal error. Details: ' + str(error_code)
   sys.exit()
   return

try:  
   gandi_apikey = os.environ["GANDI_API_KEY"]
except KeyError: 
   print "Please set the environment variable GANDI_API_KEY with your Gandi API key."
   sys.exit(1)

if (len(sys.argv) > 1):
  domain = sys.argv[1]
else:
  sys.exit(1)

gandi_api = xmlrpclib.ServerProxy('https://rpc.gandi.net/xmlrpc/')

try:
  r = gandi_api.domain.price(gandi_apikey, [domain])
except (xmlrpclib.Fault, \
  xmlrpclib.ProtocolError, xmlrpclib.ResponseError), error_code:
  handle_error(error_code)
if len(r) == 0:
  print "Invalid request."
  sys.exit(1)
while (r[0]['available'] ==  'pending'):
  time.sleep(0.7)
  try:
    r = gandi_api.domain.price(gandi_apikey, [domain])
  except (xmlrpclib.Fault, \
    xmlrpclib.ProtocolError, xmlrpclib.ResponseError), error_code:
    handle_error(error_code)
if (r[0]['available'] == 'available'):
    for domains_avail in r[0]['prices']:
      r_phase = str(domains_avail['action']['param']['tld_phase'])
      for domain_prices in domains_avail['unit_price']:
        print domain + " Phase: " + r_phase + " Duration: " + \
	str(domain_prices['min_duration']) + "-" + str(domain_prices['max_duration']) \
	+ "(" + str(domain_prices['duration_unit']) + ") " \
	+ str(domain_prices['price']) + " (" + domain_prices['currency'] \
	+ "/" + domain_prices['grid'] + ")" + " Price type: " \
	+ str(domain_prices['price_type'])
    for phases in r[0]['phases']:
      tldphase = str(phases['phase']) + " ";
      tldphase += "Start: " + str(phases['date_start']) + " "
      tldphase += "Start Gandi: " + str(phases['date_start_gandi']) + " ";
      tldphase += "End: " + str(phases['date_end']);
      print tldphase
else:
  print domain + ': ' + (r[0]['available'])

