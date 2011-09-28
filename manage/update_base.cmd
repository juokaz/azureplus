@echo off

REM php setup
echo Downloading PHP 5.2
rd /s /q "..\hostingapp\Deployment\php\v5.2"
php download.php http://windows.php.net/downloads/releases/php-5.2.17-nts-Win32-VC6-x86.zip ..\hostingapp\Deployment\php\v5.2
copy "..\hostingapp\Deployment\php\v5.2\php.ini-recommended" "..\hostingapp\Deployment\php\v5.2\php.ini" > nul

echo Downloading PHP 5.3
rd /s /q "..\hostingapp\Deployment\php\v5.3"
php download.php http://windows.php.net/downloads/releases/php-5.3.8-nts-Win32-VC9-x86.zip ..\hostingapp\Deployment\php\v5.3
copy "..\hostingapp\Deployment\php\v5.3\php.ini-production" "..\hostingapp\Deployment\php\v5.3\php.ini" > nul


REM wincache extension
echo Adding WinCache extension for PHP 5.2
rd /s /q "C:\temp" > nul
md "C:\temp"
php download.php http://downloads.sourceforge.net/project/wincache/wincache-1.1.0/wincache-1.1.0-5.2-nts-vc6-x86.exe C:\temp\wincache.exe
C:\temp\wincache.exe /c /q /t:C:\temp
copy "C:\temp\php_wincache.dll" "..\hostingapp\Deployment\php\v5.2\ext\php_wincache.dll" > nul

echo Adding WinCache extension for PHP 5.3
rd /s /q "C:\temp" > nul
md "C:\temp"
php download.php http://downloads.sourceforge.net/project/wincache/wincache-1.1.0/wincache-1.1.0-5.3-nts-vc9-x86.exe C:\temp\wincache.exe
C:\temp\wincache.exe /c /q /t:C:\temp
copy "C:\temp\php_wincache.dll" "..\hostingapp\Deployment\php\v5.3\ext\php_wincache.dll" > nul

REM sqlsrv extension
echo Adding SqlSrv extension
rd /s /q "C:\temp" > nul
md "C:\temp"
php download.php "http://download.microsoft.com/download/C/D/B/CDB0A3BB-600E-42ED-8D5E-E4630C905371/SQLSRV20.EXE" C:\temp\sqlsrv.exe
C:\temp\sqlsrv.exe /c /q /t:C:\temp
copy "C:\temp\php_sqlsrv_52_nts_vc6.dll" "..\hostingapp\Deployment\php\v5.2\ext\php_sqlsrv.dll" > nul
copy "C:\temp\php_sqlsrv_53_nts_vc9.dll" "..\hostingapp\Deployment\php\v5.3\ext\php_sqlsrv.dll" > nul
copy "C:\temp\php_pdo_sqlsrv_52_nts_vc6.dll" "..\hostingapp\Deployment\php\v5.2\ext\php_pdo_sqlsrv.dll" > nul
copy "C:\temp\php_pdo_sqlsrv_53_nts_vc9.dll" "..\hostingapp\Deployment\php\v5.3\ext\php_pdo_sqlsrv.dll" > nul

REM Packaging app base
echo Creating Azure package
cd ..\hostingapp\
cspack ServiceDefinition.csdef > nul


REM Uploading to azure blob storage
echo Deploying Azure package
cd ..\manage\
php deploy.php store-base -f=..\hostingapp\ServiceDefinition.cspkg > nul
