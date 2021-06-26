<p align="center"><img src="https://files.bookacorner.io/all/logo-stycky2.png" width="400" alt="Book a Corner"></p>


# Book a Corner Service API

Bookacorner.com service API developed with Laravel 8

## Start

Environment Ubuntu 18.4

_This instructions will allow you to get a local working copy of this project to develop and testing._

### Requirements

-   Docker - [How to install](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-18-04)
-   Docker-compose - [How to install](https://www.digitalocean.com/community/tutorials/how-to-install-docker-compose-on-ubuntu-18-04)

**Docker & Compose versions used**

-   Docker Docker version 20.10.6, build 370c289
-   Docker-compose version 1.21.2, build a133471
-   Docker-compose YML V3


**Docker image base**
-  php:7.4.19-apache-buster


**Docker Servicios**

-   php 7.4.19
-   Apache/2.4.38
-   supervisord 3.3.5


**Docker Ports**

-   80 (Expose)

**Laravel Framework & Paquetes**

```sh
./composer.json
```

### Setup

At first, we should build docker images. Ejecution might take some minutes,
if  the image is not in the host, it will automatically download and then the packets installation
will take place from the docker file. `dockerstack/docker/php7.4.19-apache-buster-dev/Dockerfile`

TCP Port 8081 is being used

Clone this repo

```sh
git clone git@github.com:Bookacorner/bac-services-api.git .
```

Crea red externa

Docker-compose utiliza una red externa llamada `bac-network` con una subnet particular:

```bash
# Motrar las redes actuales
docker network ls 

# Output
NETWORK ID     NAME                       DRIVER    SCOPE
8fd7f46956c8   bac-network                bridge    local
4ba9c476a52d   bookacornerlocal_default   bridge    local
72987c128f04   bridge                     bridge    local

# Crear la red bac-network
docker network create --driver=bridge --subnet=192.168.200.0/24  bac-network
```

Agregar host

Se debe agregar `bacservice.local` a `/etc/hosts` (Linux OS)
```bash
127.0.1.1       bacservice.local
```

Start docker stack

```bash
cd dockerstack
# First time
docker-compose up -d --build

# Then
docker-compose up -d

# To stop services
docker-compose down
```

The app wil mount inside the container at /var/www

```bash
#  docker ps: shows active containers
docker ps
CONTAINER ID   IMAGE        COMMAND                 CREATED       STATUS        PORTS                               NAMES
4f82bedc5820   bac:service  "/usr/bin/supervisor…"  3 hours ago   Up 3 hours    0.0.0.0:80->80/tcp, :::80->80/tcp   service
```

### Configuration

To install laravel packets we need to get into the container and follow these steps

Create a database on your MySQL server

```sql
CREATE DATABASE `bacservice` COLLATE 'utf8mb4_unicode_ci';
```

```bash
# enter the container with user www
docker exec -it -u www service /bin/bash

composer install

# Configure laravel with env.example (sobreescribir el que crea laravel)
cp env.example .env

# Configure environment
vi .env 


# if everything is correct
php artisan

# execute to create database tables
php artisan devtool:freshmigrate

```

### Debug y logs

If there are PHP errors, they will show in stdout container or logs,
alernatively and depending on the config, we can enter the container to check logs

```bash
# Check container logs
docker-compose logs -f

# Logs con docker
docker logs -f [nombre-del-contenedor]

# Ingreso al contenedor
docker exec -it [nombre-del-contenedor] /bin/bash

```

### Testing

We implement Unit tests with PHP Unit

[PHPUnit Manual](https://phpunit.readthedocs.io/en/9.3/index.html)

[Laravel Mocking](https://laravel.com/docs/8.x/mocking)

```bash
# Testing into container
docker exec -it  -u www service /bin/bash

# Run all suit
php artisan test 
php artisan test --parallel

# Run Partial test
t  # Alias para ./vendor/bin/phpunit 
t --filter=PdfWorkTest

# Testing from host
docker exec -it  -u www service php artisan test

docker exec -it -u www service vendor/bin/phpunit

```

### Coding standard

We implement php-cs-fixer, this tool to be executed before unit test, to ensure a proper fixing, 
to explore a deployed rule set and configuration, see `site/.php_cs`

- [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [All Rules](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/rules/index.rst)
- [PRS12](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/ruleSets/PSR12.rst)

```bash
# Run into container
docker exec -it  -u www service /bin/bash

# Show the list of files and their modifications, without updating any files
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix all files 
vendor/bin/php-cs-fixer fix
```

Additionally, the plugin for .editorconfig must be activated in the IDE of our preference

- [editorconfig](https://editorconfig.org/)


## PDF Service

This service main purpose is to render Blade templates and convert them to PDF. It can also concatenate 
other PDF files to result. Once the process is completed, the result is uplaoded to S3 and it can do a remote call.

### Endpoints

**POST: /pdf/creator** admits 4 params as json:

* `content` (required): json object with key: value to populate blade template
  
* `templates` (required): array with URLs templates or any URL that response is a HTML. They will be converted in the correct order.

* `attachments` (nullable): array URLs of PDFs file, concatenated in proper order

* `callback` (nullable): URL to POST informing about the process success.

example POST

```json
{    
    "content": {
        "name":"Charlie Langworth III",
        "address":"14514 Hayley DamNorth Robbie, MD 55442"
    },
    "templates": [
      "http://sample.com/template-two.blade.php"
    ],
    "attachments": [
      "http://sample.com/mercadopago.pdf"
    ],
    "callback":  "https://sample.com/nPVREqXakbFaBBGL8YRD"
}
```

Response: 

* code : process unique code. It's being used to consult process state

* link : URL to get final file
  
* status : process state in_progress | fail | done
  
* message : In case of failure, a message
  
* callback : URL to POST informing about the process success.


```json
{
    "data": {
        "code": "ac9szznrzm",
        "link": "https://files.bookacorner.io\/ac9szznrzm-2021may28-011649.pdf",
        "status": "in_progress",
        "message": null,
        "callback": "https://sample.com/nPVREqXakbFaBBGL8YRD"
    },
    "success": true
}
```

**GET: /pdf/status/[code]** Consult about a process 

```
GET /pdf/status/ac9szznrzm
```
Response: 

```json
{
    "data": {
        "code": "ac9szznrzm",
        "link": "https://files.bookacorner.io\/ac9szznrzm-2021may28-011649.pdf",
        "status": "in_progress",
        "message": null,
        "callback": "https://sample.com/nPVREqXakbFaBBGL8YRD"
    },
    "success": true
}
```

### Internal function

The whole process is an event chain with jobs that complete the work.

When creating a post, this is validated by `PdfCreatorRequest`, if it is valid, it will then generate an insert int the table `pdf_works` 
y returns the response generated by `PdfWorkResource`.

`PdfWork` model has an event associated to the `created` table event from eloquent called `PdfWorkCreated`

`PdfWorkCreated` calls `PdfWorkGetSources` listener, this determines the amount of files to download: templates or PDF files to concatenate. 
Then each download will trigger a job called `DownloadFilesToLocal`

`DownloadFilesToLocal` executes for each file to download, it saves them into `site/storage/app/pdf/[code]` and when finished it calls `DownloadedFinishedFile` event.

`DownloadedFinishedFile` will call `PdfMaker` listener for each downloaded file. The latter will know when all the files have been downloaded and it will trigger the Job `CreatePdfFromTemplate`.

When `CreatePdfFromTemplate` finishes the file generation, it will call `FinishedPdfFile`.

`FinishedPdfFile` calls the `PdfMergeable` listener and will know if all the files have been generated from templates and will trigger Job `PdfFileDelivery`.

`PdfFileDelivery` concatenates all PDF files and uploads the final file with the proper name to S3, then calls Job `CallBackResponse`.

`CallBackResponse` determines if there is a URL to call on success provided by initial call

```php
// EventServiceProvider
protected $listen = [
    // Executes on each DB insert, it's associated to the model
    PdfWorkCreated::class => [
        // Determines what to downlaod and for each file triggers DownloadFilesToLocal   
        PdfWorkGetSources::class,
    ],
    // DownloadFilesToLocal calls this event for each finished file
    DownloadedFinishedFile::class => [
        // Called by DownloadedFinishedFile and evaluates if all files have been downloaded. If they are, it triggers CreatePdfFromTemplate
        PdfMaker::class,
    ],
    // Is called by CreatePdfFromTemplate  when finished
    FinishedPdfFile::class => [
        //Called by FinishedPdfFile, determines if all templates were already converted to PDF and triggers job PdfFileDelivery when they are
        PdfMergeable::class,
    ],
];
```

### Artisan Commands

`pdf-service:clean-folder` Removes temp files in `site/storage/app/pdf/`

`pdf-service:delete-oldworks` Removes finished jobs on DB
