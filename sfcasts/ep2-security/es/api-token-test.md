# Probar la autenticación por token

¿Qué tal una prueba como ésta... pero en la que iniciamos sesión con una clave API? Creemos un nuevo método: función pública `testPostToCreateTreasureWithApiKey()`:

[[[ code('cc0da55af8') ]]]

Esto empezará más o menos igual que antes. Copiaré la parte superior de la prueba anterior, quitaré el `actingAs()`... y añadiré un `dump()` cerca de la parte inferior:

[[[ code('6c31b71407') ]]]

Así, como antes, estamos enviando datos no válidos y esperamos un código de estado 422.

Copia ese nombre de método, luego gira y ejecuta sólo esta prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

Y... ninguna sorpresa: obtenemos un código de estado 401 porque no estamos autenticados.

Enviemos una cabecera `Authorization`, pero una no válida para empezar. Pasa una clave`headers` configurada en una matriz con `Authorization` y luego la palabra `Bearer` y luego... `foo`.

Esto debería seguir fallando:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

Y... ¡lo hace! Pero con un mensaje de error diferente: `invalid_token`. ¡Qué bien!

## Utilizar un código real

Para pasar un token real, tenemos que introducir un token real en la base de datos. Hazlo con `$token = ApiTokenFactory::createOne()`:

[[[ code('c86b43cd3d') ]]]

¿Necesitamos controlar algún campo de esto? En realidad sí. Abre `DragonTreasure`. Si nos desplazamos hacia arriba, la operación `Post` requiere `ROLE_TREASURE_CREATE`:

[[[ code('7e7ffa2753') ]]]

Cuando nos autenticamos a través del formulario de acceso, gracias a `role_hierarchy`, siempre tenemos eso. Pero cuando utilizamos una clave API, para obtener ese rol, el token necesita el ámbito correspondiente.

Para asegurarnos de que lo tenemos, en la prueba, establece la propiedad `scopes` en`ApiToken::SCOPE_TREASURE_CREATE`:

[[[ code('5ca50ca12d') ]]]

Ahora pasa esto a la cabecera: `$token->getToken()`. Ah... y déjame arreglar`scopes`: que debería ser una matriz:

[[[ code('32ef7b9af9') ]]]

¡Creo que ya estamos listos! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

Y... ¡ya está! ¡Vemos los bonitos 422 errores de validación!

## Probar un token con un alcance incorrecto

Hagamos una prueba para asegurarnos de que no tenemos acceso si a nuestro token le falta este ámbito. Copia todo el método de prueba... y pégalo a continuación. Llámalo`testPostToCreateTreasureDeniedWithoutScope()`.

Esta vez, cambia `scopes` por otra cosa, como `SCOPE_TREASURE_EDIT`. A continuación, ahora esperamos un código de estado 403:

[[[ code('43280da30d') ]]]

Esta vez, vamos a ejecutar todas las pruebas:

```terminal
symfony php bin/phpunit
```

Y... ¡todo verde! Un 422 y luego un 403. Ve a eliminar los volcados de ambos puntos.

Por cierto, si utilizas mucho los tokens de la API en tus pruebas, pasar la cabecera `Authorization`puede resultar molesto. Browser tiene una forma en la que podemos crear un objeto Browser personalizado con métodos personalizados. Por ejemplo, podrías añadir un método `authWithToken()`, pasar un array de ámbitos, y entonces crearía ese token y lo pondría en la cabecera

```php
$this->browser()
    ->authWithToken([ApiToken::SCOPE_TREASURE_CREATE])
    // ...
;
```

Esto no funciona en absoluto ahora mismo, pero consulta la documentación de Browser para aprender cómo hacerlo.

Siguiente: en la API Platform 3.1, el comportamiento de la operación `PUT` está cambiando. Hablemos de cómo, y de lo que tenemos que hacer en nuestro código para prepararnos para ello.
