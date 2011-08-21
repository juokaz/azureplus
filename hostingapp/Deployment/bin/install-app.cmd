@echo off
ECHO "Starting APP installation from storage account" >> log.txt

rd /s /q "..\..\sitesroot\0\"
php.exe storage.php update -c=apps -e=app.zip -n=sample.zip -t="..\..\sitesroot\0\" >> log.txt 2>>err.txt

ECHO "Completed APP Installation" >> log.txt