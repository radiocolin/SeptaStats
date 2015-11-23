#!/bin/bash
#
# This script is used to run a query against Splunk
#

#
# Errors are fatal
# 
set -e

UN="admin"
PW="adminpw"

# xmllint is part of the libxml2-utils package

URL="https://localhost:8089/services/search/jobs"
QUERY="search index=septa_analytics earliest=-5m | head limit=10"


echo "# "
echo "# Executing query: ${QUERY}"
echo "# "

JOBID=$(curl -4 -s -u ${UN}:${PW} -k ${URL} -d search="${QUERY}"  | xmllint --xpath "/response/sid/text()" -)

echo "# "
echo "# Got Job ID: ${JOBID}"
echo "# "

echo "# Fetching results... "
echo "# "


#
# Get the state of our job, and keep looping as long as it's not done.
#
KEY=".entry[0].content.dispatchState"
STATE=$(curl -4 -s -u ${UN}:${PW} -k ${URL}/${JOBID} --get -d output_mode=json | jq ${KEY})

while true
do
	if test ${STATE} == '"DONE"'
	then
		break
	fi

	echo "# State is ${STATE}. Sleeping and trying again..."
	STATE=$(curl -4 -s -u ${UN}:${PW} -k ${URL}/${JOBID} --get -d output_mode=json | jq ${KEY})
	sleep 1

done

curl -4 -s -u ${UN}:${PW} -k ${URL}/${JOBID}/results --get -d output_mode=json | jq .


