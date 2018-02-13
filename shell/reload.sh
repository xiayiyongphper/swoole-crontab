#!/bin/bash

pid=`ps -eaf|grep "RPC crontab Server:Master" | grep -v "grep"| awk '{print $2}'`
worker_pid=`ps -eaf|grep "RPC crontab Server"|grep "Worker"|grep -v "grep"|head -1|awk '{print $2}'`

if [ -z $pid ]; then
  echo "server is not running!!"
  exit 255
else
  kill -0 $pid > /dev/null
  if [ $? -eq 0 ]; then 
    kill -s USR1 $pid 
    sleep 2
    kill -0 $pid > /dev/null
    if [ $? -eq 0 ]; then
      new_worker_pid=`ps -eaf|grep "RPC crontab Server"|grep "Worker"|grep -v "grep"|head -1|awk '{print $2}'`
      if [ -z "$new_worker_pid" ]; then
        echo "reload failed!!"
        exit 255
      else 
        if [ "$new_worker_pid" -eq "$worker_pid" ]; then
          echo "reload failed!!"
          exit 255
        else
          echo "reload success!!"
          ps -ef|grep "RPC crontab"|grep -v "grep"
          exit 0
        fi
      fi
    else
      echo "reload failed!!"
      exit 255
    fi
  else 
    echo "server is not running!!"
    exit 255
  fi 
fi 
