#!/bin/bash
#```````````````````````````````````````````````````````````````````````````````````````````````|
# Author - Bandana Kumari                                                                       |
# Date -   5th  June 2021                                                                       |
# Aim -   This program will check the disk usage of server and if disk used                     |
#          exceeds thresold than it will send mail to admin                                     |
# Script Name - diskusages_alert.sh                                                             |
#```````````````````````````````````````````````````````````````````````````````````````````````|
#Fetch current time
date
# it will find disk usage 
NOW_USED=$(df / | grep / | awk '{ print $5}' | sed 's/%//g')
THRESHOLD=10
#loop to send email to admin
if [ "$NOW_USED" -gt "$THRESHOLD" ] ; then
  echo "Subject: you have consumed $NOW_USED percent " | sendmail  bandana.kumari@goolean.tech 
fi