@echo off

ECHO Starting APP update setup >> log.txt

..\Assets\installutil /u /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt
..\Assets\installutil /i /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt

ECHO Completed APP update setup >> log.txt