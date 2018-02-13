#!/bin/bash

pid=`ps -eaf |grep "RPC crontab Server:Master" | grep -v "grep"| awk '{print $2}'`

master_exit() {
  max_times=30
  success=0
  while [ $max_times -gt 0 ]
  do
    echo "waiting server to stop!"
    kill -0 $pid>/dev/null
    if [ $? -eq 0 ]; then
      max_times=`expr $max_times - 1`
      sleep 1
    else
      success=1
      break
    fi
  done
  if [ $success -eq 1 ]; then
    echo "stop server success!"
    exit 0
  else
    echo "stop server failed!"
    exit 255
  fi
}

if [ -z $pid ]; then
  echo "server is not running!!"
  exit 255
else
  kill -0 $pid>/dev/null
  if [ $? -eq 0 ]; then 
    kill -15 $pid
    master_exit
  else 
    echo "server is not running!!"
    exit 255
  fi 
fi
