#!/bin/bash

cur_dir=$(cd "$(dirname "$0")"; pwd)
cd $cur_dir

/bin/bash ./stop.sh
if [ $? -eq 0 ]; then 
  /bin/bash ./start.sh
else 
  echo "restart failed!!"
fi 
