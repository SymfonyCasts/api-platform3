# Poner en marcha un sistema de pruebas asesino

Nuestra API es cada vez más compleja. Y hacer pruebas manualmente no es un buen plan a largo plazo. Así que vamos a instalar algunas herramientas para conseguir una configuración de pruebas asesina.

## Instalar el paquete de pruebas

Primer paso: en tu terminal ejecuta:

```terminal
composer require test
```

Este es un alias de flex para un paquete llamado `symfony/test-pack`. Recuerda: los paquetes son paquetes de acceso directo que en realidad instalan un montón de otros paquetes. Por ejemplo, cuando esto termine... y echemos un vistazo a `composer.json`, podrás ver abajo en`require-dev` que esto añadió el propio PHPUnit, así como algunas otras herramientas de Symfony para ayudar en las pruebas:

[[[ code('58168dd412') ]]]

También ejecutó una receta que añadió varios archivos. Tenemos `phpunit.xml.dist`, un directorio `tests/`, `.env.test` para variables de entorno específicas de las pruebas e incluso un pequeño acceso directo ejecutable `bin/phpunit` que utilizaremos para ejecutar nuestras pruebas.

## Biblioteca Hello browser

No es ninguna sorpresa, Symfony tiene herramientas para realizar pruebas y éstas pueden utilizarse para probar una API. Es más, API Platform incluso tiene sus propias herramientas construidas sobre ellas para que probar una API sea aún más fácil. Sin embargo, voy a ser testarudo y utilizar una herramienta totalmente diferente de la que me he enamorado.

Se llama [Browser](https://github.com/zenstruck/browser), y también está construida sobre las herramientas de prueba de Symfony: casi como una interfaz más bonita sobre esa sólida base. Es... superdivertido de usar. Browser nos proporciona una interfaz fluida que se puede utilizar para probar aplicaciones web, como la que ves aquí, o para probar APIs. También se puede utilizar para probar páginas que utilicen JavaScript.

Vamos a instalarlo. Copia la línea `composer require`, gira hacia atrás y ejecútalo:

```terminal-silent
composer require zenstruck/browser --dev
```

Mientras eso hace lo suyo, es opcional, pero hay una "extensión" que puedes añadir a `phpunit.xml.dist`. Añádela aquí abajo:

[[[ code('21ca5680f1') ]]]

En el futuro, si utilizas PHPUnit 10, es probable que esto se sustituya por alguna configuración de `listener`.

Esto añade algunas funciones extra al navegador. Por ejemplo, cuando falle una prueba, guardará automáticamente la última respuesta en un archivo. Pronto veremos esto. Y si utilizas pruebas con JavaScript, ¡hará capturas de pantalla de los fallos!

## Crear nuestra primera prueba

Bien, ya estamos listos para nuestra primera prueba. En el directorio `tests/`, no importa cómo organices las cosas, pero yo voy a crear un directorio `Functional/`porque vamos a hacer pruebas funcionales a nuestra API. Sí, crearemos literalmente un cliente API, haremos peticiones GET o POST y luego afirmaremos que obtenemos de vuelta la salida correcta.

Crea una nueva clase llamada `DragonTreasureResourceTest`. Una prueba normal extiende`TestCase` de PHPUnit. Pero haz que extienda `KernelTestCase`: una clase de Symfony que extiende `TestCase`... pero nos da acceso al motor de Symfony:

[[[ code('a88e674d33') ]]]

Empecemos probando la ruta de recolección GET para asegurarnos de que obtenemos los datos que esperamos. Para activar la biblioteca del navegador, en la parte superior, añade un trait con `use HasBrowser`:

[[[ code('c8e8a8248b') ]]]

A continuación, añade un nuevo método de prueba: `public function``testGetCollectionOfTreasures()` ... que devolverá `void`:

[[[ code('8939576a8f') ]]]

Utilizar el navegador es sencillísimo gracias a ese trait: `$this->browser()`. Ahora podemos hacer peticiones GET, POST, PATCH o lo que queramos. Haz una petición GET a `/api/treasures` y luego, para ver qué aspecto tiene, utiliza esta ingeniosa función`->dump()`:

[[[ code('e2226bdf2a') ]]]

## Ejecutando nuestras Pruebas a través del Binario symfony

¿A que mola? Veamos qué aspecto tiene. Para ejecutar nuestra prueba, podríamos ejecutar:

```terminal
php ./vendor/bin/phpunit
```

Eso funciona perfectamente. Pero una de las recetas también añadió un archivo de acceso directo:

```terminal
php bin/phpunit
```

Cuando lo ejecutamos, veamos. El `dump()` sí que funcionó: volcó la respuesta... que era una especie de error. Dice

> SQLSTATE: falló la conexión al puerto 5432 del servidor.

No puede conectarse a nuestra base de datos. Nuestra base de datos se ejecuta a través de un contenedor Docker... y luego, como estamos utilizando el servidor web `symfony`, cuando utilizamos el sitio a través de un navegador, el servidor web `symfony` detecta el contenedor Docker y establece la variable de entorno `DATABASE_URL` por nosotros. Así es como nuestra API ha podido hablar con la base de datos Docker.

Cuando hemos ejecutado comandos que necesitan hablar con la base de datos, los hemos ejecutado como `symfony console make:migration`... porque cuando ejecutamos cosas a través de`symfony`, añade la variable de entorno `DATABASE_URL`... y luego ejecuta el comando.

Así que, cuando simplemente ejecutamos `php bin/phpunit`... falta el verdadero `DATABASE_URL`. Para solucionarlo, ejecuta:

```terminal
symfony php bin/phpunit
```

Es lo mismo... excepto que deja que `symfony` añada la variable de entorno `DATABASE_URL`. Y ahora... ¡volvemos a ver el volcado! Desplázate hasta arriba. Mejor! Ahora el error dice

> La base de datos `app_test` no existe.

## Base de datos específica de la prueba

Interesante. Para entender lo que está pasando, abre `config/packages/doctrine.yaml`. Desplázate hasta la sección `when@test`. Esto es genial: cuando estamos en el entorno `test`, hay un trozo de configuración llamado `dbname_suffix`. Gracias a esto, Doctrine tomará el nombre normal de nuestra base de datos y le añadirá `_test`:

[[[ code('0e0c04410b') ]]]

La siguiente parte es específica de una biblioteca llamada ParaTest en la que puedes ejecutar pruebas en paralelo. Como no vamos a utilizar eso, es sólo una cadena vacía y no es algo de lo que debamos preocuparnos.

De todos modos, así es como acabamos con un `_test` al final del nombre de nuestra base de datos. Y eso es lo que queremos No queremos que nuestros entornos `dev` y `test` utilicen la misma base de datos, porque resulta molesto cuando se sobreescriben mutuamente.

Por cierto, si no estás utilizando la configuración binaria y Docker de `symfony`... y estás configurando tu base de datos manualmente, ten en cuenta que en el entorno `test`no se lee el archivo `.env.local`:

[[[ code('70831f3973') ]]]

El entorno `test` es especial: se salta la lectura de `.env.local` y sólo lee `.env.test`. También puedes crear un `.env.test.local` para las variables de entorno que se leen en el entorno `test` pero que no se consignarán en tu repositorio.

## El rasgo ResetDatabase

Vale, en el entorno `test`, nos falta la base de datos. Podríamos arreglarlo fácilmente ejecutando:

```terminal
symfony console doctrine:database:create --env=test
```

Pero eso es demasiado trabajo. En lugar de eso, añade un rasgo más a nuestra clase de prueba:`use ResetDatabase`:

[[[ code('0ff7a75753') ]]]

Esto viene de Foundry: la biblioteca que hemos estado utilizando para crear fijaciones ficticias mediante las clases de fábrica. `ResetDatabase` es increíble. Se asegura automáticamente de que la base de datos se vacía antes de cada prueba. Así, si tienes dos pruebas, la segunda no se estropeará por culpa de algún dato que haya añadido la primera.

También va a crear la base de datos automáticamente por nosotros. Compruébalo. Ejecuta

```terminal
symfony php bin/phpunit
```

de nuevo y comprueba el volcado. ¡Esa es nuestra respuesta! ¡Es nuestro hermoso JSON-LD! Todavía no tenemos ningún elemento en la colección, pero está funcionando.

Y fíjate en que, cuando hacemos esta petición, no estamos enviando una cabecera `Accept`en la petición. Recuerda que, cuando utilizamos la interfaz Swagger UI... en realidad sí envía una cabecera `Accept` que anuncia que queremos `application/ld+json`.

Podemos añadirlo a nuestra prueba si queremos. Pero si no pasamos nada, obtendremos JSON-LD de vuelta porque ése es el formato por defecto de nuestra API.

A continuación: vamos a terminar correctamente esta prueba, incluyendo la alimentación de la base de datos con datos y el aprendizaje de las aserciones de la API de Browser.
