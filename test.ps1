docker stop dev-appti2ude-test
docker rm dev-appti2ude-test
docker build -t dev-appti2ude-test ./docker
$direct=Get-Location
$direct=$direct.Path.Replace('C:\', '/c/').Replace('\', '/')
echo $pwd
docker run -P -v ${direct}:/var/www/html --name dev-appti2ude-test dev-appti2ude-test phpunit
