@echo off
ECHO "Starting APP installation from github" >> log.txt

rd /s /q "..\Websites\Sample"
"..\Assets\GitSharp\Git" clone git://github.com/juokaz/SamplePHPApp.git "..\Websites\Sample"

ECHO "Completed APP Installation" >> log.txt