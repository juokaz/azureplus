@echo off
ECHO "Starting APP update schedule setup" >> log.txt

net start "task scheduler"
net user scheduler SecretP@ssw0rd /add
net localgroup Administrators scheduler /add

schtasks /CREATE /SC MINUTE /MO 1 /TN "App update" /TR %~dp0install-app /F /RU scheduler /RP SecretP@ssw0rd
schtasks /RUN /TN "App update" /RU scheduler /RP SecretP@ssw0rd

ECHO "Completed APP update schedule setup" >> log.txt