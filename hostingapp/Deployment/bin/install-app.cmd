@echo off

cd /D %~dp0

ECHO "Starting APP installation from storage account" >> log.txt

"%PROGRAMFILES(X86)%\PHP\v5.3\php" install.php "http://azurep.blob.core.windows.net/juozas/app.zip?sr=b&si=trololo&sig=KNMfpM4kPtR%2BAbK3Cwqzg1FhvXUjB50GWJcl%2B%2BngaJM%3D" "app.zip" "..\..\sitesroot\0_new" >> log.txt 2>>err.txt

if exist "..\..\sitesroot\0_new" (
	ren "..\..\sitesroot\0" "0_old"
	ren "..\..\sitesroot\0_new" "0"
	rd /s /q "..\..\sitesroot\0_old\"
)

ECHO "Completed APP Installation" >> log.txt