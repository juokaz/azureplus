@echo off
ECHO "Starting PHP Installation" >> log.txt

md "%~dp0appdata"
cd "%~dp0appdata"
cd..

reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d "%~dp0appdata" /f
"..\Assets\WebPICmdLine\webpicmdline" /Products:PHP52,PHP53 /AcceptEula >>log.txt 2>>err.txt
reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d %%USERPROFILE%%\AppData\Local /f

ECHO "Adding PHP to PATH environment variable" >> log.txt
powershell -command [Environment]::SetEnvironmentVariable('Path', [Environment]::GetEnvironmentVariable('Path', 'Machine') + ';%PROGRAMFILES(X86)%\PHP\v5.3\', 'Machine')

ECHO "Completed PHP Installation" >> log.txt