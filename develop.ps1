docker stop dev-appti2ude
docker rm dev-appti2ude
docker rmi dev-appti2ude
docker build -t dev-appti2ude ./docker
$direct=Get-Location
$direct=$direct.Path.Replace('C:\', '/c/').Replace('\', '/')
echo $pwd
docker run -d -P -v ${direct}:/var/www/html --name dev-appti2ude dev-appti2ude
