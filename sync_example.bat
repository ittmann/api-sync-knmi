cd /D "%~dp0"
.\PHP\php.exe .\bin\console knmi:sync --help
.\PHP\php.exe .\bin\console knmi:sync -s uurwaarneming --from-date "first day of last month" --to-date "today" -vvv
