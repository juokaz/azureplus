@echo off
ECHO "Starting APP installation from storage account" >> log.txt

php.exe storage.php update -c=apps -e=app.zip -n=sample.zip -t="..\..\sitesroot\0_new" >> log.txt 2>>err.txt

if exist "..\..\sitesroot\0_new" (
	rd /s /q "..\..\sitesroot\0\"
	ren "..\..\sitesroot\0_new" "0"
)

ECHO "Completed APP Installation" >> log.txt