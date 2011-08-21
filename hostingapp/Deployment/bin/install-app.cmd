@echo off
ECHO "Starting APP installation from storage account" >> log.txt

rd /s /q "..\Websites\Sample"
php.exe storage.php retrieve-archive -c=apps -n=sample.zip -t=..\Websites\Sample\

ECHO "Completed APP Installation" >> log.txt