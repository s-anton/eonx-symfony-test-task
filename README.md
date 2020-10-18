# Its EONX test task which was built on Symfony 5.1 platform

## Used technologies
- Symfony 5.1 (with pack of bundles like doctrine, http-client and so on)
- Api-platform
- PostgreSQL 12
- Alice, PhpUnit, Faker, ...

## Installation
1. ```composer install```
2. create .env.local file and redefine DATABASE_URL
3. ```bin/console d:d:c``` (skip it if database already exist)
4. ```bin/console d:m:m```

## Test it 
* with bin/phpunit

## Usage
### Without symfony cli
1. Fill table with ```bin/console customer:import:australian```
2. Start dev-server with ```php -S 0.0.0.0:8000 -t public```
3. Open browser and go to ```http://127.0.0.1:8000/customers```, then you can copy any ```id``` and check it on ```http://127.0.0.1:8000/customers/<id>```
### With symfony cli
1. ```symfony project:create``` - provide ```title``` and ```plan```
2. ```symfony deploy```
3. Check provided link (with ```/customers``` and ```/customers/<id>``` of course)

