cd /D "%~dp0"
.\PHP\php.exe .\bin\console doctrine:schema:update --dump-sql --force
