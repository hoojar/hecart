"C:\Program Files\7-Zip\7z.exe" e .\hoojar.7z

\nginx\mysql\bin\mysql.exe -h localhost -uroot -p"123456" --default-character-set=utf8 -e "DROP DATABASE hoojar;"

\nginx\mysql\bin\mysql.exe -h localhost -uroot -p"123456" --default-character-set=utf8 -e "CREATE DATABASE hoojar CHARACTER SET UTF8 COLLATE UTF8_GENERAL_CI;"

\nginx\mysql\bin\mysql.exe -h localhost -uroot -p"123456" --default-character-set=utf8 hoojar < .\hoojar.sql

del /Q .\hoojar.sql