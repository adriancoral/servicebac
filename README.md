<p align="center"><img src="https://files.bookacorner.io/all/logo-stycky2.png" width="400" alt="Book a Corner"></p>


# Book a Corner Service API

Service API de bookacorner.com desarrollada en Laravel 8

## Inicio

Leer todo este archivo primero. Entorno utilizado en esta descripción Ubuntu 18.4

_Estas instrucciones te permitirán obtener una copia del proyecto en funcionamiento en tu máquina local para propósitos de desarrollo y testing._

### Requisitos

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

### Instalación

La primera vez se deben crear las imágenes docker, la ejecución puede tomar unos minutos,
si la imagen base no se encuentra en el host, baja automáticamente y luego comienza la instalación de
todos los paquetes indicados en el docker file. `dockerstack/docker/php7.4.19-apache-buster-dev/Dockerfile`

El puerto 80 TCP deben estar libre siempre que se arranque el contenedor, de lo contrario fallara

Clonar este repositorio

```sh
git clone git@github.com:Bookacorner/bac-services-api.git .

```

Iniciar el stack de docker

```bash
cd dockerstack
# Primera ves
docker-compose up -d --build

# Luego
docker-compose up -d

# Detener los servicios
docker-compose down
```

La aplicación se montara dentro del contenedor en /var/www/

```bash
#  docker ps: muestra los contenedores activos
docker ps
CONTAINER ID   IMAGE        COMMAND                 CREATED       STATUS        PORTS                               NAMES
4f82bedc5820   bac:service  "/usr/bin/supervisor…"  3 hours ago   Up 3 hours    0.0.0.0:80->80/tcp, :::80->80/tcp   service
```

### Configuración

Para instalar los paquetes de laravel hay que entrar al contendor y seguir los siguientes pasos

```bash
# ingresar al contenedor con el usuario www
docker exec -it -u www service /bin/bash

#developer@zoho:
composer install

# Configurar laravel, la configuración para entorno local esta en env.example (sobreescribir el que crea laravel)
cp env.example .env

# Crear el archivo de base de datos
touch storage/database/service.sqlite 


# Si todo esta bien, probamos el comando artisan
php artisan

# Ejecutamos el comando para crear las tablas y
php artisan devtool:freshmigrate

```

### Debug y logs

Si hay errores de PHP se mostraran por el stdout del contenedor o en los logs,
alternativamente y dependiendo de la configuración, se puede entrar al contenedor para ver otros logs

```bash
# Ver logs con docker-composer, estando dentro de la carpeta dockerstack
docker-compose logs -f

# Logs con docker
docker logs -f [nombre-del-contenedor]

# Ingreso al contenedor
docker exec -it [nombre-del-contenedor] /bin/bash

```

### Testing

La app implementa test unitarios y features con PHPUnit.

[PHPUnit Manual](https://phpunit.readthedocs.io/en/9.3/index.html)

[Laravel Mocking](https://laravel.com/docs/8.x/mocking)

```bash
# Ingresar al contenedor PHP-FMP llamado app
docker exec -it app /bin/bash

# Ejecutar la suit
php artisan test --parallel # via laravel
t                           # Alias para ./vendor/bin/phpunit 

# Desde el host (fuera del contenedor)
docker exec -it app vendor/bin/phpunit

# Ejecutar tests parciales
t --filter=ExampleTest
./vendor/bin/phpunit --filter=ExampleTest
```

## Servicio de PDF

Este servicio tiene como objetivo renderizar templates de Blade y convertirlos a PDF, adicionalmente puede concatenar otros archivos PDF 
enviados al resultado, una vez terminado el proceso sube el archivo a S3 y puede devolver una respuesta si le es proporcionada

### Endpoints

**POST: /pdf/creator** admite 4 parámetros enviados en el cuerpo en formato json:

* `content` (requiere): objeto con la lista de pares clave/valor que se utilizan para popular los template blade
  
* `templates` (requiere): array de URLs con templates blade o cualquier URL que entrega como respuesta un HTML, en caso de ser mas de uno, 
  seran convertidos a PDF en el orden proporcionado. 

* `attachments` (nullable): array de URLs con archivos PDFs, que seran concatenados en el orden proporcionado

* `callback` (nullable): URL donde se envia un POST informando el estado y la finalizacion de proceso. 

POST de ejemplo

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

* code : codigo unico de proceso, se utiliza para consultar el estado 

* link : URL para obtener el archivo final
  
* status : estado de proceso in_progress | fail | done
  
* message : En caso de fallar se informan detalles del fallo
  
* callback : URL a la que se enviara por POST esta misma respuesta cuando finalice el proceso 


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

**GET: /pdf/status/[code]** Consultar el estado de un proceso  

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

### Funcionamiento Interno

Todo el proceso es una cadena de eventos con jobs que hace el trabajo.

Al realizar un post este es validado por `PdfCreatorRequest` si pasa las validaciones se genera un insert en la tabla `pdf_works` 
y se retorna la respuesta generada por `PdfWorkResource`.

El modelo `PdfWork` correspondiente a la tabla, tiene un evento asociado al evento `created` de eloquent llamado `PdfWorkCreated`

`PdfWorkCreated` llama al listener `PdfWorkGetSources`, este determina la cantidad de archivos a bajar, ya sean templates o 
PDF para adjuntar al resultado, por cada archivo que encuentra se dispara un job llamado `DownloadFilesToLocal`

`DownloadFilesToLocal` se ejecuta por cada archivo a bajar, almacena los mismos en `site/storage/app/pdf/[code]` al finalizar llama 
el evento `DownloadedFinishedFile` 

`DownloadedFinishedFile` llamara el listener `PdfMaker` por cada archivo bajado, este último determinara si han terminado de bajar 
todos los archivos y dispara el Job  `CreatePdfFromTemplate`

Cuando `CreatePdfFromTemplate` termina de generar el archivo llama al evento  `FinishedPdfFile`

`FinishedPdfFile` llama al listener `PdfMergeable` este determina si ya están generados todos los PDF desde los templates 
y dispara el job `PdfFileDelivery` 

`PdfFileDelivery` Concatena (merge) todos los archivos PDFs y sube el archivo final con el nombre dado a S3, 
luego dispara el job `CallBackResponse`

`CallBackResponse` determina si hay que enviar una respuesta en caso de ser propocionada la URL

```php
// EventServiceProvider
protected $listen = [
    // Se ejecuta con cada insert en la DB, esta asociado desde el modelo
    PdfWorkCreated::class => [
        // Determina que bajar y por cada archivo dispara DownloadFilesToLocal   
        PdfWorkGetSources::class,
    ],
    // DownloadFilesToLocal llama a este evento por cada archivo terminado 
    DownloadedFinishedFile::class => [
        // Es llamado por DownloadedFinishedFile y evalúa si bajaron todos los archivo, en caso de que si dispara  CreatePdfFromTemplate
        PdfMaker::class,
    ],
    // Es llamado por CreatePdfFromTemplate  al terminar
    FinishedPdfFile::class => [
        //Es llamado por FinishedPdfFile, determina si todos los templates fueron convertidos a PDF, en caso de que si dispara el job PdfFileDelivery 
        PdfMergeable::class,
    ],
];
```

### Artisan Commands

`pdf-service:clean-folder` Elimina los archivos temporales en `site/storage/app/pdf/`

`pdf-service:delete-oldworks` Elimina los trabajos terminados de la DB 
