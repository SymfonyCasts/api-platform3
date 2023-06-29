# Procesadores de Estado: Hashing de la contraseña de usuario

Cuando un cliente de la API crea un usuario, envía un campo `password`, que se establece en la propiedad `plainPassword`. Ahora tenemos que aplicar el hash a esa contraseña antes de que `User`se guarde en la base de datos. Como demostramos al trabajar con Foundry, hashear una contraseña es sencillo: coge el servicio `UserPasswordHasherInterface` y llama a un método sobre él:

[[[ code('1aec54425f') ]]]

Pero para conseguirlo, necesitamos un "gancho" en la API Platform: necesitamos alguna forma de ejecutar código después de que nuestros datos se deserialicen en el objeto `User`, pero antes de que se guarden.

En nuestro tutorial sobre la API Platform 2, utilizamos para ello una escucha Doctrine, que seguiría funcionando. Sin embargo, tiene algunos aspectos negativos, como ser supermágico -es difícil depurar si no funciona- y tienes que hacer algunas cosas raras para asegurarte de que se ejecuta al editar la contraseña de un usuario.

## Hola Procesadores de Estado

Afortunadamente, en API Platform 3 tenemos una nueva y brillante herramienta que podemos aprovechar: se llama procesador de estado. Y de hecho, ¡nuestra clase `User` ya utiliza un procesador de estado!

Encuentra la [Guía de actualización de la API Platform 2 a la 3](https://api-platform.com/docs/core/upgrade-guide/)... y busca procesador. Veamos... aquí está. Tiene una sección llamada proveedores y procesadores. Hablaremos de proveedores más adelante.

Según esto, si tienes una clase `ApiResource` que es una entidad -como en nuestra aplicación-, entonces, por ejemplo, tu operación `Put` ya utiliza un procesador de estado llamado `PersistProcessor` La operación `Post` también lo utiliza, y `Delete` tiene uno llamado `RemoveProcessor`.

Los procesadores de estado son geniales. Después de que los datos enviados se deserialicen en el objeto, nosotros... ¡necesitamos hacer algo! La mayoría de las veces, ese "algo" es: guardar el objeto en la base de datos. ¡Y eso es precisamente lo que hace `PersistProcessor`! ¡Sí, nuestros cambios de entidad se guardan en la base de datos por completo gracias a ese procesador de estado incorporado!

## Creación del procesador de estado personalizado

Así que éste es el plan: vamos a engancharnos al sistema de procesadores de estado y añadir el nuestro propio. Primer paso: ejecuta un nuevo comando desde la API Platform:

```terminal
php ./bin/console make:state-processor
```

Llamémoslo `UserHashPasswordProcessor`. Perfecto.

Gira, entra en `src/`, abre el nuevo directorio `State/` y echa un vistazo a`UserHashPasswordStateProcessor`:

[[[ code('36f009d47d') ]]]

Es deliciosamente sencillo: API Platform llamará a este método, nos pasará datos, nos dirá qué operación está ocurriendo... y algunas cosas más. Luego... hacemos lo que queramos. Enviar correos electrónicos, guardar cosas en la base de datos, ¡o RickRollar a alguien viendo un screencast!

Activar este procesador es sencillo en teoría. Podríamos ir a la operación `Post`, añadir una opción `processor` y configurarla con nuestro id de servicio: `UserHashPasswordStateProcessor::class`.

Por desgracia... si hiciéramos eso, sustituiría al `PersistProcessor` que está utilizando ahora. Y... no queremos eso: queremos que se ejecute nuestro nuevo procesador... y también el existente `PersistProcessor`. Pero... cada operación sólo puede tener un procesador.

## Configurar la decoración

¡No te preocupes! Podemos hacerlo decorando `PersistProcessor`. La decoración sigue siempre el mismo patrón. Primero, añade un constructor que acepte un argumento con la misma interfaz que nuestra clase: `private ProcessorInterface` y lo llamaré `$innerProcessor`:

[[[ code('e1a66c86ba') ]]]

Después de añadir un `dump()` para ver si funciona, haremos el paso 2: llamar al método de servicio decorado: `$this->innerProcessor->process()` pasando `$data`, `$operation`,`$uriVariables` y... sí, `$context`:

[[[ code('3345ce9992') ]]]

Me encanta: nuestra clase está preparada para la decoración. Ahora tenemos que decirle a Symfony que la utilice. Internamente, `PersistProcessor` de API Platform es un servicio. Vamos a decirle a Symfony que siempre que algo necesite ese servicio `PersistProcessor`, le pase nuestro servicio en su lugar... pero también que Symfony nos pase el `PersistProcessor` original.

Para ello, añade `#[AsDecorator()]` y pásale el id del servicio. Normalmente puedes encontrarlo en la documentación, o puedes utilizar el comando `debug:container` para buscarlo. La documentación dice que es `api_platform.doctrine.orm.state.persist_processor`:

[[[ code('c7e6df9fc6') ]]]

¡Decoración realizada! Todavía no estamos haciendo nada, ¡pero vamos a ver si llega a nuestro volcado! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Y... ¡ahí está! Sigue siendo un 500, ¡pero está utilizando nuestro procesador!

## Añadir la lógica Hashing

Ahora podemos ponernos manos a la obra. Debido a cómo hicimos la decoración del servicio, nuestro nuevo procesador será llamado siempre que se procese cualquier entidad... ya sea un `User`, un `DragonTreasure` o cualquier otra cosa. Así que, empieza por comprobar si `$data` es un `instanceof User`... y si `$data->getPlainPassword()`... porque si estamos editando un usuario, y no se envía ningún `password`, no hace falta que hagamos nada:

[[[ code('fced58ad22') ]]]

Por cierto, la documentación oficial de los procesadores de estados de decoración es ligeramente diferente. A mí me parece más complejo, pero el resultado final es un procesador que sólo se llama para una entidad, no para todas.

Para hacer hash de la contraseña, añade un segundo argumento al constructor:`private UserPasswordHasherInterface` llamado `$userPasswordHasher`:

[[[ code('b912b016f3') ]]]

A continuación, digamos que `$data->setPassword()` se establece en `$this->userPasswordHasher->hashPassword()`pasándole el `User`, que es `$data` y la contraseña simple: `$data->getPlainPassword()`:

[[[ code('a2724928e9') ]]]

Y todo esto ocurre antes de que llamemos al procesador interno que guarda realmente el objeto.

¡Vamos a probar esto! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

¡Victoria! Después de crear un usuario en nuestra API, podemos iniciar sesión como ese usuario.

## Usuario.borrarCredenciales()

Ah, y es algo sin importancia, pero una vez que tienes una propiedad `plainPassword`, dentro de `User`, hay un método llamado `eraseCredentials()`. Descomenta `$this->plainPassword = null`:

[[[ code('7f537e47fd') ]]]

Esto asegura que si el objeto se serializa en la sesión, se borre primero el `plainPassword` sensible.

A continuación: arreglemos algunos problemas de validación mediante `validationGroups` y descubramos algo especial sobre la operación `Patch`.
