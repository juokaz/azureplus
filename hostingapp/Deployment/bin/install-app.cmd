@echo off

cd /D %~dp0

ECHO "Starting APP installation from storage account" >> log.txt

for /f "tokens=*" %%a in ('powershell -ExecutionPolicy Unrestricted .\config.ps1 -name "APP_URL"') do @set APP_URL=%%a

"%PROGRAMFILES(X86)%\PHP\v5.3\php" install.php "%APP_URL%" "app.zip" "..\..\sitesroot\0_new" >> log.txt 2>>err.txt

if exist "..\..\sitesroot\0_new" (
    ROBOCOPY "..\..\sitesroot\0_new" "..\..\sitesroot\0" /PURGE /NJS /NJH >> log.txt 2>>err.txt
	rd /s /q "..\..\sitesroot\0_new\" >> log.txt 2>>err.txt
)

ECHO "Completed APP Installation" >> log.txt
