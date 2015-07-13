#!/bin/bash

docker stop dev-appti2ude
docker rm dev-appti2ude
docker rmi dev-appti2ude
docker build -t dev-appti2ude ./docker/
docker run -d -P -v $(pwd)/:/var/www/html/ --name dev-appti2ude dev-appti2ude