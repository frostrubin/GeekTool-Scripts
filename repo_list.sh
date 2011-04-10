#!/bin/bash

if [ "$1" == '' ];then
  echo -e "Please specify a username to use:\nrepo_list.sh username"
  exit
fi

# Get a list of all my repos:
reps=$(curl --silent http://github.com/api/v2/yaml/repos/show/"$1" | grep ":name:" | sed s/"  :name: "//g)

for i in $(echo "$reps"); do 
  echo "$i"|sed s/\\-/\ /g
done