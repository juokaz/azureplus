@echo off

REM php setup
echo Downloading PHP 5.2
rd \s \q "..\hostingapp\Deployment\php\v5.2"
php download.php http://windows.php.net/downloads/releases/php-5.3.8-nts-Win32-VC9-x86.zip ..\hostingapp\Deployment\php\v5.2
copy "..\hostingapp\Deployment\php\v5.2\php.ini-production" "..\hostingapp\Deployment\php\v5.2\php.ini"
echo Downloading PHP 5.3
rd \s \q "..\hostingapp\Deployment\php\v5.3"
php download.php http://windows.php.net/downloads/releases/php-5.2.17-nts-Win32-VC6-x86.zip ..\hostingapp\Deployment\php\v5.3
copy "..\hostingapp\Deployment\php\v5.3\php.ini-recommended" "..\hostingapp\Deployment\php\v5.3\php.ini"

REM Packaging app base
echo Creating Azure package
cd ..\hostingapp\
cspack ServiceDefinition.csdef > nul

REM Uploading to azure blob storage
echo Deploying Azure package
cd ..\manage\
php deploy.php store-base -f=..\hostingapp\ServiceDefinition.cspkg > nul
