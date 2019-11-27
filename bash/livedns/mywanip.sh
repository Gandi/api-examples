#!/bin/bash
# 
# Updates a zone record using Gandi's LiveDNS.
# Ideally this script is placed into a crontab or when the WAN interface comes up.
# Replace APIKEY with your Gandi API Key and DOMAIN with your domain name at Gandi.
# Set RECORDS to which zone labels you wish to update.
# You will be able to query example.net, mywanip.example.net and mywanip2.example.net if everything went successful.
# 
# Live dns is available on www.gandi.net
# Obtaining your API Key: http://doc.livedns.gandi.net/#step-1-get-your-api-key
#

DOMAIN="example.net"
RECORDS=( "@" "mywanip" "mywanip2" )
APIKEY="my-api-key"


API="https://dns.api.gandi.net/api/v5/"
IP_SERVICE="http://me.gandi.net"

IP4=$(curl -s4 $IP_SERVICE)
IP6=$(curl -s6 $IP_SERVICE)

if [[ -z "$IP4" && -z "$IP6" ]]; then
    echo "Something went wrong. Can not get your IP from $IP_SERVICE "
    exit 1
fi


for RECORD in "${RECORDS[@]}"
do
    if [[ ! -z "$IP4" ]]; then
        DATA='{"rrset_values": ["'$IP4'"]}'
        curl -s -XPUT -d "$DATA" \
            -H"X-Api-Key: $APIKEY" \
            -H"Content-Type: application/json" \
            "$API/domains/$DOMAIN/records/$RECORD/A"
    fi

    if [[ ! -z "$IP6" ]]; then
        DATA='{"rrset_values": ["'$IP6'"]}'
        curl -s -XPUT -d "$DATA" \
            -H"X-Api-Key: $APIKEY" \
            -H"Content-Type: application/json" \
            "$API/domains/$DOMAIN/records/$RECORD/AAAA"
    fi
done

