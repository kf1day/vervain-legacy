# Оглавление

1. Описание
2. Настройка
   - bootstrap.php
   - Необходимые переменные сервера
3. Карта сайта
   - Файл sitemap.php

# Описание
Vervain – мульти-сайтовый движок, который создавался в первую очередь для передачи управления классу (и методу), предопределенному картой сайта.

# Настройка

## bootstrap.php
Движок vervain автоматически осуществляет разбор URI, все запросы к динамическому контенту необходимо передавать на обработку в файл
`bootstrap.php` 

***Пример конфигурации сайта для nginx***
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
Карта сайта сопоставляет все возможные URI сайта с контороллерами. Сегменты URI описываются в файле `sitemap.php` с помощью древовидной структуры, начиная с одной корневой ноды

## Формат ноды
Каждая нода содержит `паттерн`, `обработчик` и необязательный массив дочерних нод

```php
array( 'паттерн', 'обработчик' [, array( node1, node2, ... ) ] );
```

### Паттерн
Паттерн определяет одну или несколько секций URI, разделенных прямым слешем.
Символ `*` (звездочка) трактуется как любое значение секции.
Паттерн для корневой ноды задает базовый URI для всего сайта.

***Примеры паттернов***
```
'/test/'
'elements/*'
'shop/*/detail'
```

### Обработчик
Обработчик задает способ обработки подходящего паттерна в виде
```
[класс][@метод][/аргумент1][/аргумент2][/аргументN]
```
Любая из частей (включая все одновременно) может быть опущена.
Специальное значение `null` означает, что нода будет отмечена, как транзитная – при обращении к такой ноде парсер сформирует переход к первой не-транзитной дочерней ноде.
* `класс` определяет класс контроллера для передачи управления.
В случае пропуска наследуется от вышестоящей ноды.
При невозможности наследования, дальнейшая обработка будет остановлена с ошибкой 
* `метод` явно указывает используемый метод класса. В случае отсутствия метод определяется дальнейшим разбором URI
* `аргумент1..N` если заданы, будут переданы в метод перед аргументами, полученными при дальнейшем разборе URI


## Файл sitemap.php
Файл `sitemap.php` должен находится в корневой директории сайта и содержать возвращаемый массив, состоящий из дерева нод.

***Пример sitemap.php***
```php
return [ '', null, [				// 1
    [ 'mercury', 'class_mercury' ],		// 2
    [ 'venus', 'class_venus@overview' ],	// 3
    [ 'earth', 'class_earth', [			// 4
        [ 'countries', 'class_countries', [	// 5
	    [ '*/flag', '@flag/small' ],	// 6
	    [ '*/leader', '' ],			// 7
	    [ 'list', '@list' ],		// 8
	]],
    ]],
    [ 'mars', 'class_mars/missions' ],		// 9
]];
```

***Примеры запросов***

|URI|№ ноды|Обработчик
|---|---|---
|/mercury/|2|\action\class_mercury::index()
|/mercury/size/metric|2|\action\class_mercury::size('metric')
|/venus/size/metric|3|\action\class_venus::overview('size', 'metric')
|/earth/countries/list/|8|\action\class_countries::list()
|/earth/countries/tongo/flag|6|\action\class_countries::flag('small')
|/earth/countries/cuba/leader/che_guevara|7|\action\class_countries::leader('che_guevara')
|/mars/current/curiosity|9|\action\class_mars::current('missions', 'curiosity')


|URI|Перенаправление
|---|---
|/|/mercury/
|/earth/countries/tongo|/earth/countries/tongo/flag/
