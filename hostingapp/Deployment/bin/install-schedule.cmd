@echo off
ECHO "Starting APP update schedule setup" >> log.txt

net start "task scheduler"
schtasks /CREATE /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F
schtasks /RUN /TN "App update"

ECHO "Completed APP update schedule setup" >> log.txt