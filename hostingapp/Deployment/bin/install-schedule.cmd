@echo off

ECHO Starting APP update setup >> log.txt

IF EXIST %WINDIR%\Microsoft.NET\Framework64 (
%WINDIR%\Microsoft.NET\Framework64\v4.0.30319\installutil.exe /u /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt
%WINDIR%\Microsoft.NET\Framework64\v4.0.30319\installutil.exe /i /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt
) ELSE (
%WINDIR%\Microsoft.NET\Framework\v4.0.30319\installutil.exe /u /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt
%WINDIR%\Microsoft.NET\Framework\v4.0.30319\installutil.exe /i /LogToConsole=false ..\Assets\AzureDownloader.exe >> log.txt 2>>err.txt
)

net start "Azure Downloader" >> log.txt 2>>err.txt

ECHO Completed APP update setup >> log.txt

EXIT /B 0