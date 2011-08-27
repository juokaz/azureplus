@echo off

cd ../hostingapp/
cspack ServiceDefinition.csdef

cd ../manage/
php deploy.php store-base -f=../hostingapp/ServiceDefinition.cspkg