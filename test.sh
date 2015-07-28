#!/bin/bash
docker stop dev-appti2ude-test
docker rm dev-appti2ude-test
docker build -t dev-appti2ude-test ./docker
docker run -P -v $(pwd):/var/www/html --name dev-appti2ude-test dev-appti2ude-test scripts/run_test.sh