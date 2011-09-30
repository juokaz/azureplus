@echo off

ECHO Starting APP update setup >> log.txt

..\Assets\installutil /u ..\Assets\AzureDownloader.exe >> log.txt
..\Assets\installutil /i ..\Assets\AzureDownloader.exe >> log.txt

ECHO Completed APP update setup >> log.txt