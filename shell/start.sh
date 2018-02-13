#!/bin/bash

start()
{

  cur_dir=$(cd "$(dirname "$0")"; pwd)
  cd $cur_dir
  cd ..

  php swoole_crontab.php

  sleep 2

  log_file=`ls -lt service/runtime/logs/server.* | head -1 | awk '{print $9}'`
  tail -3 $log_file | grep 'Server shutdown' $log_file > /dev/null

  pid=`ps -eaf|grep "RPC crontab Server:Master" | grep -v "grep"| awk '{print $2}'`
  if [ -z $pid ]; then
    echo "server start failed!!"
    echo "*********************ERROR*********************"
    tail -10 $log_file
    echo "*********************ERROR*********************"
  else 
    echo "server start success!!"
    ps -ef|grep "RPC crontab"|grep -v "grep"
  fi 
}

check_pid=`ps -eaf|grep "RPC crontab Server:Master" | grep -v "grep"| awk '{print $2}'`
if [ -z $check_pid ]; then
  start
else
  echo "server has already been started!!"
  exit 255
fi
