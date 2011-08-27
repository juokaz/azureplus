@echo off
ECHO "Starting APP update schedule setup" >> log.txt

net start "task scheduler"

schtasks /DELETE /TN "App update" /F
schtasks /CREATE /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F /RU SYSTEM

ECHO "Completed APP update schedule setup" >> log.txt