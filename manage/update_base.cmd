@echo off

REM Packaging app base
cd ../hostingapp/
cspack ServiceDefinition.csdef > nul

REM Uploading to azure blob storage
cd ../manage/
php deploy.php store-base -f=../hostingapp/ServiceDefinition.cspkg > nul