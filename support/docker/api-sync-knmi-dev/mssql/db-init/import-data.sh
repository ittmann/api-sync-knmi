#!/usr/bin/env bash

for ((n=1;n<=50;n++))
do
    if /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U sa -P "$SA_PASSWORD" -d master -i /usr/config/setup.sql
    then
        echo "setup.sql completed"
        break
    else
        echo "not ready yet..."
        sleep 1
    fi
done

#import data from a csv file
#/opt/mssql-tools/bin/bcp [CathlabDataproxy].[dbo].[RCS_CL_patient] in "/usr/config/rcs_cl_patient.csv" -c -t',' -S localhost -U sa -P "$SA_PASSWORD"
#import data from a dump
#/opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -I -i /usr/config/rcs_cl_patient.sql
