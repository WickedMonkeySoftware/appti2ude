#!/bin/bash

docker build -t dev-appti2ude .
docker run -d -P -v $(pwd)/:/var/www/html/ --name dev-appti2ude dev-appti2ude