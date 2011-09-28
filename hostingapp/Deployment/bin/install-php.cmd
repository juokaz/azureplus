@echo off
ECHO Starting PHP Installation >> log.txt

REM Clear existing FastCGI mappers
%windir%\system32\inetsrv\appcmd clear config /section:system.webServer/fastCGI >> log.txt 2>>err.txt

REM PHP 5.2 settings
ECHO Setting PHP 5.2 Configuration >> log.txt
%windir%\system32\inetsrv\appcmd set config /section:system.webServer/fastCGI /+"[fullPath='%ROLEROOT%\approot\php\v5.2\php-cgi.exe',maxInstances='4',instanceMaxRequests='10000',requestTimeout='180',activityTimeout='180']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ROLEROOT%\approot\php\v5.2\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ROLEROOT%\approot\php\v5.2\php-cgi.exe'].environmentVariables.[name='PHPRC',value='%ROLEROOT%\sitesroot\0']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /"[fullPath='%ROLEROOT%\approot\php\v5.2\php-cgi.exe']".monitorChangesTo:"%ROLEROOT%\sitesroot\0\php.ini" /commit:apphost  >> log.txt 2>>err.txt

REM PHP 5.3 settings
ECHO Setting PHP 5.3 Configuration >> log.txt
%windir%\system32\inetsrv\appcmd set config /section:system.webServer/fastCGI /+"[fullPath='%ROLEROOT%\approot\php\v5.3\php-cgi.exe',maxInstances='4',instanceMaxRequests='10000',requestTimeout='180',activityTimeout='180']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ROLEROOT%\approot\php\v5.3\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ROLEROOT%\approot\php\v5.3\php-cgi.exe'].environmentVariables.[name='PHPRC',value='%ROLEROOT%\sitesroot\0']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /"[fullPath='%ROLEROOT%\approot\php\v5.3\php-cgi.exe']".monitorChangesTo:"%ROLEROOT%\sitesroot\0\php.ini" /commit:apphost  >> log.txt 2>>err.txt

ECHO Completed PHP Installation >> log.txt
