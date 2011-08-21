@echo off
ECHO "Starting APP update schedule setup" >> log.txt

net start "task scheduler"

schtasks /DELETE /TN "App update"
schtasks /CREATE /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F /RU SYSTEM
schtasks /RUN /TN "App update"

ECHO "Completed APP update schedule setup" >> log.txt