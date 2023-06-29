# Grupos Dinámicos: Creador de Contextos

En `DragonTreasure`, busca el campo `$isPublished`. Antes añadimos esto `ApiProperty``security` para que el campo sólo se devuelva a los usuarios administradores o propietarios de este tesoro. Esta es una forma sencilla y 100% válida de manejar esta situación.

Sin embargo, hay otra forma de manejar los campos que deben ser dinámicos en función del usuario actual... y puede tener o no dos ventajas dependiendo de tu situación.

## Las opciones de seguridad frente a los Grupos Dinámicos

En primer lugar, consulta la documentación. Abre la ruta GET para un único `DragonTreasure`. E, incluso sin probarlo, puedes ver que `isPublished` es un campo que se anuncia correctamente en nuestra documentación.

Entonces, eso es bueno, ¿no? Sí Bueno, probablemente. Si `isPublished` fuera realmente un campo interno, sólo para administradores, quizá no quisiéramos que se anunciara al mundo.

El segundo posible problema con `security` es que, si tienes esta opción en muchas propiedades, va a ejecutar esa comprobación de seguridad muchas veces al devolver una colección de objetos. Sinceramente, eso probablemente no cause problemas de rendimiento, pero es algo a tener en cuenta.

## Inventar nuevos grupos de serialización

Para resolver estos dos posibles problemas -y, sinceramente, sólo para aprender más sobre cómo funciona la API Platform bajo el capó- quiero mostrarte una solución alternativa. Elimina el atributo `ApiProperty`:

[[[ code('1af325ec1d') ]]]

Y sustitúyelo por dos nuevos grupos. No vamos a utilizar los normales`treasure:read` y `treasure:write`... porque entonces los campos siempre formarían parte de nuestra API. En su lugar, utiliza `admin:read` y `admin:write`:

[[[ code('341802f8ff') ]]]

Esto no funcionará todavía... porque estos grupos no se utilizan nunca. Pero ésta es la idea: si el usuario actual es un administrador, cuando serialicemos, añadiremos estos dos grupos.

La parte complicada es que, ahora mismo, ¡los grupos son estáticos! Los establecemos aquí arriba, en el atributo`ApiResource` -o en una operación específica- ¡y ya está! Pero podemos hacerlos dinámicos.

## Hola ContextBuilder

Internamente, la API Platform tiene un sistema llamado constructor de contextos, que se encarga de construir los contextos de normalización o desnormalización que luego se pasan al serializador. Y, podemos engancharnos a él para cambiar el contexto: por ejemplo, para añadir grupos adicionales.

Hagámoslo En `src/ApiPlatform/`, crea una nueva clase llamada`AdminGroupsContextBuilder`... y haz que implemente`SerializerContextBuilderInterface`:

[[[ code('549590dd51') ]]]

Luego, ve a "Código"->"Generar" -o `Command`+`N` en un Mac- y selecciona "Implementar métodos" para crear el que necesitamos: `createFromRequest()`:

[[[ code('b5da790e95') ]]]

Es bastante sencillo: API Platform lo llamará, nos pasará el `Request`, si estamos normalizando o desnormalizando... y luego nos devolverá el array `context` que debe pasarse al serializador.

## ¡Hagamos algo de Decoración!

Como ya hemos visto unas cuantas veces, nuestra intención no es sustituir al núcleo constructor de contextos. No, queremos que el constructor de contextos principal haga lo suyo... y luego añadiremos nuestras propias cosas.

Para ello, una vez más, utilizaremos la decoración de servicios. Sabemos cómo funciona: añade un método `__construct()` que acepte un`SerializerContextBuilderInterface` privado y lo llamaré `$decorated`:

[[[ code('4cc69fe354') ]]]

Luego, aquí abajo, digamos `$context = this->decorated->createFromRequest()`pasando `$request`, `$normalization` y `$extractedAttributes`. Añade un `dump()`para asegurarte de que funciona y devuelve `$context`:

[[[ code('fc65eabe4b') ]]]

Para decirle a Symfony que utilice nuestro constructor de contexto en lugar del real, añade nuestro `#[AsDecorator()]`.

Aquí, necesitamos el ID de servicio de lo que sea el constructor de contexto principal. Es algo que puedes encontrar en la documentación: es `api_platform.serializer.context_builder`:

[[[ code('2b4a37c6b8') ]]]

Ah, pero ten cuidado al utilizar `SerializerContextBuilderInterface`: hay dos. Uno de ellos es de GraphQL: asegúrate de seleccionar el de `ApiPlatform\Serializer`, a menos que estés utilizando GraphQL.

De acuerdo ¡Veamos si funciona nuestro volcado! Ejecuta todas nuestras pruebas: También quiero ver cuáles fallan:

```terminal
symfony php bin/phpunit
```

Y... ¡bien! Vemos el volcado un montón de veces, seguido de dos fallos. El primero es `testAdminCanPatchToEditTreasure`. Es el caso en el que estamos trabajando ahora. Nos preocuparemos de `testOwnerCanSeeIsPublishedFieldI` dentro de un momento.

Copia el nombre del método de prueba y vuelve a ejecutarlo con `--filter=`:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

## Cuando se llama al constructor de contexto

¡Perfecto! Vemos el volcado: en realidad tres veces, lo cual es interesante. Abre esa prueba para que podamos ver qué está pasando. ¡Sí! Estamos haciendo una única petición de`PATCH` a `/api/treasure/1`. Entonces, ¿se llama al constructor de contextos 3 veces durante una sola petición?

¡Pues sí! Se llama una vez cuando la API Platform está consultando y cargando el`DragonTreasure` desde la base de datos. Es... una situación un poco extraña, porque se supone que el contexto se utiliza para el serializador... pero nosotros simplemente estamos consultando el objeto. Pero en fin, ésa es la primera vez.

Las dos siguientes tienen sentido: se llama cuando el JSON que estamos enviando se desnormaliza en el objeto... y una tercera vez cuando el `DragonTreasure`final se normaliza de nuevo en JSON.

De todos modos, vamos a añadir los grupos dinámicos. Para determinar si el usuario es un administrador, añade un segundo argumento constructor - `private Security` de `SecurityBundle`llamado `$security`:

[[[ code('db6e21b050') ]]]

Luego aquí abajo, si `isset($context['groups'])` y`$this->security->isGranted('ROLE_ADMIN')`, entonces añadiremos los grupos:`$context['groups'][] =`. Si estamos normalizando, añade `admin:read` si no, añade `admin:write`:

[[[ code('83f68aad31') ]]]

Ahora te preguntarás por qué comprobamos si `isset($context['groups'])`. Bueno, no es aplicable a nuestra aplicación, pero imagina que serializáramos un objeto que no tuviera ningún `groups` -como si nunca hubiéramos establecido el `normalizationContext`en ese `ApiResource`. En ese caso, ¡añadir estos `groups` haría que devolviera menos campos! Recuerda que si no hay grupos de serialización, el serializador devuelve todos los campos accesibles. Pero en cuanto añades un solo grupo, sólo serializa las cosas de ese grupo. Así que si no hay ningún `groups`, no hagas nada y deja que todo se serialice o deserialice normalmente.

¡De acuerdo! ¡Probemos ahora la prueba!

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

¡Pasa! El campo `isPublished` se devuelve si somos un usuario administrador. Pero... ve a actualizar los documentos... y abre la ruta GET one treasure endpoint. Ahora no vemos `isPublished` anunciado como campo en nuestros documentos... aunque se devuelva si somos un usuario administrador. Eso puede ser bueno o malo. Es posible hacer que los documentos se carguen dinámicamente en función de quién haya iniciado sesión, pero no es algo que vayamos a abordar en este tutorial. Ya hablamos de ello en nuestro tutorial [API Platform 2](https://symfonycasts.com/screencast/api-platform2-security)... pero el sistema de configuración ha cambiado.

Analicemos el siguiente método, que comprueba que un propietario puede ver el campo`isPublished`. Esto falla actualmente... y es aún más complicado que la situación del administrador, porque tenemos que incluir o no el campo `isPublished` objeto por objeto.
