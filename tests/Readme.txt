----------------------------
Per effettuare i test Gazie
deve essere configurato su 
di un Database di prova e
funzionante
----------------------------


Per utilizzare PHPUnit con Linux
-------------------------------------------------------------

- wget https://phar.phpunit.de/phpunit.phar
- chmod +x phpunit.phar
- sudo mv phpunit.phar /usr/local/bin/phpunit

Per utilizzare PHPUnit con Windows
-------------------------------------------------------------

- Creare un directory per eseguire binari, per esempio C:\bin
- Aggungere al PATH di sistema la directory ;C:\bin
- Scaricare da  https://phar.phpunit.de/phpunit.phar il file phpunit.phar 
  e salvarlo in C:\bin\
- Aprire la command line con CMD
- Creare il bash script C:\bin\phpunit.cmd eseguento:
    C:\Users\username> cd C:\bin
    C:\bin> echo @php "%~dp0phpunit.phar" %* > phpunit.cmd
    C:\bin> exit
- Verificare il funzionamento di phpunit eseguendo:

	C:\Users\username> phpunit --version
	PHPUnit x.y.z by Sebastian Bergmann and contributors.
	

Esempio di testing funzioni sul database:
-------------------------------------------------------------

Per utilizzare phpunit dal root di Gazie scrivere:

phpunit --colors  tests/database/testDb.php

