"\nginx\mysql\bin\mysqldump.exe" -h localhost -uroot -p"123456" --opt -R --default-character-set=utf8 hoojar > .\hoojar.sql

"C:\Program Files\7-Zip\7z.exe" a .\hoojar.7z .\hoojar.sql

del /Q .\hoojar.sql
