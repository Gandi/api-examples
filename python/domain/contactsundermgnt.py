#!/usr/bin/env python

# Simplified Python example to find contacts which are not created/managed by the reseller
# Replace your-api-key with your API Key: https://wiki.gandi.net/en/xml-api

import xmlrpclib

api = xmlrpclib.ServerProxy('https://rpc.gandi.net/xmlrpc/')
apikey = 'your-api-key'
contacts = api.contact.list(apikey)
domains = api.domain.list(apikey)
for domain in domains:
    domaininfo = api.domain.info(apikey,domain['fqdn'])
    tech = str(domaininfo['contacts']['tech']['handle'])
    bill = str(domaininfo['contacts']['bill']['handle'])
    admin = str(domaininfo['contacts']['admin']['handle'])
    owner = str(domaininfo['contacts']['owner']['handle'])
    found_tech = False
    found_bill = False
    found_admin = False
    found_owner = False
    for contact in contacts:
        if (contact['handle'] == tech):
            found_tech = True
        if (contact['handle'] == bill):
            found_bill = True
        if (contact['handle'] == admin):
            found_admin = True
        if (contact['handle'] == owner):
            found_owner = True
    if (found_tech == False):
        print "Domain " + domain['fqdn'] + " Tech " + tech + " is not managed by us."
    if (found_bill == False):
        print "Domain " + domain['fqdn'] + " Bill " + bill + " is not managed by us."
    if (found_admin == False):
        print "Domain " + domain['fqdn'] + " Admin " + admin + " is not managed by us."
    if (found_owner == False):
        print "Domain " + domain['fqdn'] + " Owner " + owner + " is not managed by us."
print "All contacts under our management:"
for contact in contacts:
    print contact['handle']
