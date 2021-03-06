@echo off
ECHO Adding RoleRoot to environment variables >> log.txt

REM use powershell to add to values
powershell -command [Environment]::SetEnvironmentVariable('RoleRoot', '%RoleRoot%', 'Machine')

REM this is needed so IIS could access ENV properties like RoleRoot
%windir%\system32\inetsrv\appcmd set config -section:applicationPools -applicationPoolDefaults.processModel.loadUserProfile:true>> log.txt 2>>err.txt

ECHO Starting PHP Installation >> log.txt

REM Clear existing FastCGI mappers
%windir%\system32\inetsrv\appcmd clear config /section:system.webServer/fastCGI >> log.txt 2>>err.txt

REM PHP 5.2 settings
ECHO Setting PHP 5.2 Configuration >> log.txt
%windir%\system32\inetsrv\appcmd set config /section:system.webServer/fastCGI /+"[fullPath='%%RoleRoot%%\approot\php\v5.2\php-cgi.exe',maxInstances='4',instanceMaxRequests='10000',requestTimeout='180',activityTimeout='180']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%%RoleRoot%%\approot\php\v5.2\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%%RoleRoot%%\approot\php\v5.2\php-cgi.exe'].environmentVariables.[name='PHPRC',value='%RoleRoot%\sitesroot\0']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /"[fullPath='%%RoleRoot%%\approot\php\v5.2\php-cgi.exe']".monitorChangesTo:"%RoleRoot%\sitesroot\0\php.ini" /commit:apphost  >> log.txt 2>>err.txt

REM PHP 5.3 settings
ECHO Setting PHP 5.3 Configuration >> log.txt
%windir%\system32\inetsrv\appcmd set config /section:system.webServer/fastCGI /+"[fullPath='%%RoleRoot%%\approot\php\v5.3\php-cgi.exe',maxInstances='4',instanceMaxRequests='10000',requestTimeout='180',activityTimeout='180']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%%RoleRoot%%\approot\php\v5.3\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%%RoleRoot%%\approot\php\v5.3\php-cgi.exe'].environmentVariables.[name='PHPRC',value='%RoleRoot%\sitesroot\0']" >> log.txt 2>>err.txt

%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /"[fullPath='%%RoleRoot%%\approot\php\v5.3\php-cgi.exe']".monitorChangesTo:"%RoleRoot%\sitesroot\0\php.ini" /commit:apphost  >> log.txt 2>>err.txt

ECHO Completed PHP Installation >> log.txt

EXIT /B 0