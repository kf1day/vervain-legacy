# Оглавление

* Описание
* Настройка
* Карта сайта
  * Файл sitemap.php

# Описание
Vervain – мульти-сайтовый движок, который создавался в первую очередь для передачи управления классу (и методу), предопределенному картой сайта.

# Настройка

## bootstrap.php
Движок vervain автоматически осуществляет разбор URI, все запросы к динамическому контенту необходимо передавать на обработку в файл
`bootstrap.php` 

#### *Пример конфигурации сайта для nginx*
```nginx
server {

	listen 80;
	server_name vervain-demo.domain.tld;

	root /var/www/vervain/sites/demo;

	location ~* \.php$ {
		return 404; 
	}

	location /static/ {
		try_files $uri =404;
		gzip_types *;
		expires 7d;
	}

	location / {
		try_files $uri @baduri;
	}
 
	location @baduri {
		fastcgi_param SCRIPT_FILENAME /var/www/vervain/v0.2/bootstrap.php;
		fastcgi_pass unix:/run/php/php7.0-fpm.sock;
		include fastcgi_params;
	}
}
```

Включаемый файл `fastcgi_params` из поставки `nginx`, как правило, уже содержит необходимые переменные для передачи PHP-процессору, но некоторые из них напрямую используются движком vervain, и на них следует обратить особое внимание

## Необходимые переменные сервера
### DOCUMENT_ROOT
Корневая директория сайта, определенная сервером, служит для формирования путей для файлов конфигурации и пользовательских классов

### DOCUMENT_URI
Путь для разбора парсером

### HTTPS [on|off] и REQUEST_SCHEME
Служит для определения факта работы сайта по https (например, при построении redirect'ов). `REQUEST_SCHEME` при конфликтах имеет более высокий приоритет

# Карта сайта
## sitemap.php
Файл `sitemap.php` должен находится в корневой директории сайта.
Файл содержит возвращаемый массив, состоящий из корневой ноды и вложенных в нее (при необходимости) одной или нескольких дочерних. Глубина вложенности нод не ограничена.
Формат каждой ноды следующий:

`[ паттерн, обработчик, [ вложенная_нода_1, вложенная_нода_2, ... ] ]`.

* `паттерн` определяет одну или несколько секций URI, разделенных слешем.
Символ `*` (звездочка) трактуется как любое значение секции.
Паттерн для корневой ноды задает базовый URI для всего сайта.
* `обработчик` задает способ обработки подходящего паттерна в виде

  `[класс][@метод][/аргумент1][/аргумент2][/аргументN]`

  Любая из частей (включая все одновременно) может быть опущена.
  Специальное значение `null` означает, что нода будет отмечена, как транзитная.
  При обращении к такой ноде парсер сформирует переход к первой не-транзитной дочерней ноде.
  * `класс` определяет название класса для передачи управления.
  В случае пропуска наследуется от вышестоящей ноды.
  При невозможности наследования, дальнейшая обработка будет остановлена с ошибкой 
  * `метод` указывает явто используемый метод класса. В противном случае метод определяется дальнейшей обработкой URI
  * `аргумент1..N` если заданы, будут переданы в метод до аргументов, определяемых дальнейшей обработкой URI


#### *Пример sitemap.php*
```php
return [ '', null, [
    [ 'mercury', 'class_mercury' ],
    [ 'venus', 'class_venus@overview' ],
    [ 'earth', 'class_earth', [
        [ 'countries', 'class_countries', [
	    [ '*', null, [
	        [ 'flag', '@flag/small' ],
	        [ 'leader', '' ],
	    ]],
	    [ 'list', '@list' ],
	]],
    ]],
    [ 'mars', 'class_mars/missions' ],
]];
```

При таком сетапе:

|URI|Обработчик
|---|---
|/|`/mercury/` (перенаправление)
|/mercury/|`\action\class_mercury::index()`
|/mercury/size/metric|`\action\class_mercury::size('metric')`
|/venus/size/metric|`\action\class_venus::overview('size', 'metric')`
|/earth/countries/list/|`\action\class_countries::list()`
|/earth/countries/tongo|`/earth/countries/tongo/flag/` (перенаправление)
|/earth/countries/tongo/flag|`\action\class_countries::flag('small')`
|/earth/countries/cuba/leader/che_guevara|`\action\class_countries::leader('che_guevara')`
|/mars/current/curiosity|`\action\class_mars::current('missions', 'curiosity')`
