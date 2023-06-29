# Validar cómo cambian los valores

Seguimos teniendo un enorme problema para asegurarnos de que los tesoros no acaban siendo robados! Acabamos de cubrir el caso principal: si haces una petición POST o PUT a una ruta de tesoro, gracias a nuestra nueva validación, nos aseguramos de que te asignas el propietario a ti mismo, a menos que seas un administrador. ¡Sí!

Pero en nuestra API, al hacer POST o PUT a una ruta de usuario, se te permite enviar un campo `dragonTreasures`. Esto, desgraciadamente, permite que se roben tesoros. Simplemente envía una petición `PATCH` para modificar tu propio registro `User`... y luego establece el campo`dragonTreasures` en una matriz que contenga las cadenas IRI de algunos tesoros que no te pertenezcan. ¡Vaya!

La solución más sencilla sería... hacer que el campo no sea escribible. Así, dentro de`User`, para `dragonTreasures`, lo mantendríamos legible, pero eliminaríamos el grupo de escritura. Eso obligaría a todo el mundo a utilizar las rutas `/api/treasures` para gestionar sus tesoros.

## El truco de este problema

Si quieres mantener el campo `dragonTreasures` escribible... puedes hacerlo, pero este problema tiene truco.

Pensemos: si envías un campo `dragonTreasures` que contiene el IRI de un tesoro que no posees, eso debería provocar un error de validación. Vale... ¿podríamos añadir una restricción de validación sobre esta propiedad? El problema es que, para cuando se ejecuta esa validación, los tesoros enviados en el JSON ya se han establecido en esta propiedad `dragonTreasures`. Y lo que es más importante, ¡el `owner` de esos tesoros ya se ha actualizado a este `User`!

Recuerda: cuando el serializador vea un `DragonTreasure` que no sea ya propiedad de este usuario, llamará a `addDragonTreasure()`... que a su vez llamará a `setOwner($this)`. Así que, cuando se ejecute la validación, parecerá que somos los propietarios del tesoro... ¡aunque originalmente no lo fuéramos!

## ¿Usar datos anteriores?

¿Qué podemos hacer? Bueno, la API Platform tiene un concepto de "datos anteriores". La API Platform clona los datos antes de deserializar el nuevo JSON sobre ellos, lo que significa que es posible obtener el aspecto original del objeto `User`.

Desgraciadamente, ese clon es superficial, lo que significa que clona campos escalares -como`username` -, pero no clona ningún objeto -como los objetos `DragonTreasure`. No hay forma, a través de la API Platform, de ver qué aspecto tenían originalmente.

## Prueba del error

Así que vamos a solucionarlo con la validación... pero con la ayuda de una clase especial de Doctrine llamada `UnitOfWork`.

Muy bien, vamos a preparar una prueba para aclarar este molesto error. Dentro de`tests/Functional/`, abre `UserResourceTest`. Copia la prueba anterior, pégala y llámala `testTreasuresCannotBeStolen()`. Crea un segundo usuario con`UserFactory::createOne()`... y necesitamos un `DragonTreasure` que vamos a intentar robar. Asigna su `owner` a `$otherUser`:

[[[ code('ebf845a5f4') ]]]

¡Hagámoslo! Nos registramos como `$user`, nos actualizamos -lo que está permitido- y luego, para el JSON, claro, quizá sigamos enviando `username`... pero también enviamos`dragonTreasures` configurado en un array con `/api/treasures/` y`$dragonTreasure->getId()`.

Al final, afirma que esto devuelve un 422:

[[[ code('c8465cbf77') ]]]

¡Vale! Copia el nombre del método. Esperamos que falle:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Y... ¡falla! Código de estado 200, ¡lo que significa que estamos permitiendo que nos roben el tesoro! ¡Qué susto!

## Crear el Validador

Bien, vamos a crear una nueva clase validadora:

```terminal
php ./bin/console make:validator
```

Llámala `TreasuresAllowedOwnerChange`.

Utilízala inmediatamente. Sobre la propiedad `dragonTreasures`, añade`#[TreasuresAllowedOwnerChange]`:

[[[ code('a64b9af672') ]]]

A continuación, en `src/Validator/`, abre la clase validadora. Haremos una limpieza básica: utiliza la función `assert()` para afirmar que `$constraint` es una instancia de `TreasuresAllowedOwnerChange`. Y también afirma que `value` es una instancia de`Collection` de Doctrine:

[[[ code('5aded32ea9') ]]]

Sabemos que se utilizará sobre esta propiedad... así que será una especie de colección de `DragonTreasures`.

## Introduce UnitOfWork

Pero... ésta será la colección de objetos `DragonTreasure` después de haber sido modificados. Tenemos que preguntar a Doctrine qué aspecto tenía cada `DragonTreasure` cuando se consultó originalmente en la base de datos. Para ello, necesitamos coger un objeto interno de Doctrine llamado `UnitOfWork`.

Encima, añadir un constructor, autoconectar `EntityManagerInterface $entityManager`... y hacer que sea una propiedad privada:

[[[ code('efa324fbbe') ]]]

Abajo, coge la unidad de trabajo con`$unitOfWork = $this->entityManager->getUnitOfWork()`:

[[[ code('a23a860f57') ]]]

Se trata de un potente objeto que realiza un seguimiento de cómo cambian los objetos de entidad y se encarga de saber qué objetos deben insertarse, actualizarse o eliminarse de la base de datos cuando el gestor de entidades se vacía.

A continuación, `foreach` sobre `$value` -que será una colección- `as $dragonTreasure`. Para ayudar a mi editor, afirmaré que `$dragonTreasure` es una instancia de`DragonTreasure`. Y ahora, obtén los datos originales:`$originalData = $unitOfWork->getOriginalEntityData($dragonTreasure)`.

Muy bonito, ¿verdad? Veamos `dd($dragonTreasure)` y `$originalData` para ver qué aspecto tienen:

[[[ code('64058ab5d3') ]]]

Go test go:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

¡Sí! ¡Ha llegado al vertedero! ¡Y esto es genial! La primera parte es el objeto`DragonTreasure` actualizado y su propietario tiene el id 1. No es super obvio, pero `$user`será id 1 y `$otherUser` será id 2. Así que el propietario era originalmente id 2, pero sí: ¡el usuario id 1 lo ha robado! Debajo, vemos los datos originales como una matriz. ¡Y su propietario era el ID 2!

Esta información nos pone en peligro. De vuelta dentro de nuestro validador, di`$originalOwnerId` = `originalData['owner_id']`. Y para que quede súper claro, pon`$newOwnerId` a `$dragonTreasure->getOwner()->getId()`.

Si no coinciden, tenemos un problema. Bueno, en realidad, si no tenemos un`$originalOwnerId`, estamos creando un nuevo `DragonTreasure` y no pasa nada. Así que si no hay `$originalOwnerId` o el `$originalOwnerId` es igual al `$newOwnerId`, ¡estamos bien!

Si no... ¡está ocurriendo un saqueo! Mueve el `$violationBuilder` hacia arriba, pero elimina el `setParameter()`:

[[[ code('523944607a') ]]]

¡Ya está!

Pero nunca he personalizado el mensaje de error. En la clase `Constraint`, dale a la propiedad `$message` un mensaje por defecto mejor:

[[[ code('90869e58e8') ]]]

Muy bien equipo, ¡hora de la verdad! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

¡Lo he clavado! El robo de tesoros queda oficialmente descartado. Ah, y aunque no lo he hecho, también podríamos inyectar el servicio `Security` para permitir que los usuarios administradores hagan lo que quieran.

Siguiente paso: cuando creamos un `DragonTreasure`, debemos enviar el campo `owner`. Hagamos que por fin sea opcional. Si no pasamos el `owner`, lo estableceremos en el usuario autenticado actualmente. Para ello, tenemos que engancharnos al proceso de "guardado" de la API Platform una vez más.
