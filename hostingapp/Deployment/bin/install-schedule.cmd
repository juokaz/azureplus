@echo off
ECHO "Starting APP update schedule setup" >> log.txt

net start "task scheduler"
schtasks /create /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F

ECHO "Completed APP update schedule setup" >> log.txt