# Personalizar el navegador globalmente

Nuestra prueba funciona... pero la API nos devuelve JSON, no JSON-LD. ¿Por qué?

Cuando hicimos antes la petición a `GET`, no incluimos una cabecera `Accept` para indicar qué formato queríamos de vuelta. Pero... JSON-LD es el formato por defecto de nuestra API, así que nos lo devolvió.

Sin embargo, cuando hacemos una petición a `->post()` con la clave `json`, se añade una cabecera`Content-Type` establecida en `application/json` -lo que está bien-, pero también se añade una cabecera `Accept` establecida en `application/json`. Sí, le estamos diciendo al servidor que queremos que nos devuelva JSON plano, no JSON-LD.

Quiero utilizar JSON-LD en todas partes. ¿Cómo podemos hacerlo? El segundo argumento de`->post()` puede ser una matriz o un objeto llamado `HttpOptions`. Digamos`HttpOptions::json()`... y luego pasar directamente el array. A ver... si entiendo bien la sintaxis:

[[[ code('cf8cc7f669') ]]]

Hasta aquí, esto es equivalente a lo que teníamos antes. Pero ahora podemos cambiar algunas opciones diciendo `->withHeader()` pasando `Accept` y `application/ld+json`:

[[[ code('3a9ebfc976') ]]]

También podríamos haberlo hecho con la matriz de opciones: tiene una clave llamada`headers`. Pero el objeto está muy bien.

Asegurémonos de que esto arregla las cosas. Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

## Envío global de la cabecera

Y... ¡volvemos a JSON-LD! Tiene los campos correctos y la respuesta `application/ld+json`Encabezado `Content-Type`.

Así que .... mola... pero hacer esto cada vez que hacemos una petición a nuestra API en las pruebas es... mega cutre. Necesitamos que esto ocurra automáticamente.

Una buena forma de hacerlo es aprovechar una clase base de prueba. Dentro de `tests/`, en realidad dentro de `tests/Functional/`, crea una nueva clase PHP llamada `ApiTestCase`. Voy a llamarla `abstract` y extender `KernelTestCase`:

[[[ code('96aa441725') ]]]

Dentro, añade el rasgo `HasBrowser`.  Pero vamos a hacer algo astuto: vamos a importar el método `browser()` pero lo llamaremos `baseKernelBrowser`:

[[[ code('5db39e1696') ]]]

¿Por qué demonios lo hacemos? Reimplementa el método `browser()`... luego llama a `$this->baseKernelBrowser()` pasándole `$options` y `$server`.  Pero ahora llama a otro método: `->setDefaultHttpOptions()`. Pásale`HttpOptions::create()` y luego `->withHeader()`, `Accept`, `application/ld+json`:

[[[ code('a45b3d0438') ]]]

¡Listo! De vuelta en nuestra clase de prueba real, extiende `ApiTestCase`: coge el que es de nuestra app:

[[[ code('d0abc5cd64') ]]]

¡Ya está! Cuando decimos `$this->browser()`, ahora llama a nuestro método`browser()`, que cambia esa opción por defecto. Celébralo eliminando`withHeader()`... y podrías volver a la matriz de opciones con una clave`json` si quieres.

Vamos a probarlo.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Y... uh oh. Es un error extraño:

> No se puede anular el método final `_resetBrowserClients()`

Esto... es porque estamos importando el trait de la clase padre y de nuestra clase... lo que hace que el trait se vuelva loco. Elimina el que está dentro de nuestra clase de prueba:

[[[ code('f4b285053a') ]]]

ya no lo necesitamos. También haré una pequeña limpieza en mis sentencias `use`.

Y ya está:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Lo tengo! Volvemos a tener JSON-LD con cero trabajo extra. Elimina ese `dump()`:

[[[ code('17978acdfa') ]]]

A continuación: vamos a escribir otra prueba que utilice nuestro token de autenticación de la API.
