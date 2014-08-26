#!/bin/sh

grep -h JText:: * -r | grep -vi "BINARY FILE" | sed -re "s/^.*JText::(_|sprintf|printf).[\"']?//" | sed -e "s/['\"][),].*//" | perl -e ' while (<>) { print uc($_) } ' | sort | uniq | sed -e 's/\(.*\)$/\1=\1/' > en-GB.com_prenotown.ini
grep -h JText:: * -r | grep -vi "BINARY FILE" | sed -re "s/^.*JText::(_|sprintf|printf).[\"']?//" | sed -e "s/['\"][),].*//" | perl -e ' while (<>) { print uc($_) } ' | sort | uniq | sed -e 's/$/=/' > it-IT.com_prenotown.ini
