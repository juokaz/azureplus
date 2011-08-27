@echo off

cd /D %~dp0

ECHO "Starting APP installation from storage account" >> log.txt

SET APP_URL = powershell -ExecutionPolicy Unrestricted .\config.ps1 -name "APP_URL"

"%PROGRAMFILES(X86)%\PHP\v5.3\php" install.php "%APP_URL%" "app.zip" "..\..\sitesroot\0_new" >> log.txt 2>>err.txt

if exist "..\..\sitesroot\0_new" (
	ren "..\..\sitesroot\0" "0_old"
	ren "..\..\sitesroot\0_new" "0"
	rd /s /q "..\..\sitesroot\0_old\"
)

ECHO "Completed APP Installation" >> log.txt