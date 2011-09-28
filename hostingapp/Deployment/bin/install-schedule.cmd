@echo off
ECHO Starting APP update schedule setup >> log.txt

REM start the scheduler
net start "task scheduler"
REM delete task if it exists
schtasks /DELETE /TN "App update" /F
REM create app update task, runs every minute
schtasks /CREATE /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F /RU SYSTEM
REM force run it
schtasks /RUN /TN "App update"

ECHO Completed APP update schedule setup >> log.txt
