# Validador personalizado

Si necesitas controlar cómo se establece un campo como `isPublished` en función de quién está conectado, tienes dos situaciones diferentes.

## Proteger un campo frente a proteger sus datos

En primer lugar, si necesitas evitar por completo que determinados usuarios escriban en este campo, para eso está la seguridad. La opción más sencilla es utilizar la opción `#[ApiProperty(security: ...)]`que hemos utilizado antes sobre la propiedad. O podrías ponerte más elegante y añadir un grupo dinámico `admin:write` mediante un constructor de contexto. De cualquier forma, impediremos que este campo se escriba por completo.

La segunda situación es cuando un usuario puede escribir en un campo... pero los datos válidos que puede establecer dependen de quién sea. Por ejemplo, un usuario puede poner `isPublished` en `false`... pero no puede ponerlo en `true` a menos que sea un administrador.

Te daré un ejemplo diferente. Ahora mismo, cuando creas un `DragonTreasure`, obligamos al cliente a pasar un `owner`. Podemos verlo en`testPostToCreateTreasure()`. Vamos a arreglar esto en unos minutos para que podamos dejar este campo desactivado... y entonces se establecerá automáticamente para quien esté autentificado.

Pero ahora mismo, el campo `owner` está permitido y es obligatorio. Pero a quién se permite asignar como `owner` depende de quién esté conectado. Para los usuarios normales, sólo se les debería permitir asignarse a sí mismos como usuario. Pero para los administradores, deberían poder asignar a cualquiera como `owner`. Heck, quizá en el futuro nos volvamos más locos y haya clanes de dragones... y puedas crear tesoros y asignarlos a cualquiera de tu clan La cuestión es: la pregunta no es si podemos establecer este campo, sino a qué datos se nos permite establecerlo. Y eso depende de quiénes seamos.

## ¿Solución con seguridad o validación?

Vale, en realidad, este problema ya lo hemos resuelto antes para la operación `Patch()`. Déjame que te lo muestre. Busca `testPatchToUpdateTreasure()`. Entonces... ejecutemos sólo esa prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

Y... pasa. Esta prueba comprueba 3 cosas. En primer lugar, nos conectamos como el usuario propietario de `DragonTreasure` y realizamos una actualización. ¡Ese es el caso feliz!

A continuación, entramos como un usuario diferente e intentamos editar el`DragonTreasure` del primer usuario. Eso no está permitido. Y ése es un uso correcto de `security`: no somos propietarios de este `DragonTreasure`, por lo que no se nos permite en absoluto editarlo. Eso es lo que protege la línea `security`.

Para la última parte, nos registramos de nuevo como propietarios de este `DragonTreasure`. Pero luego intentamos cambiar el propietario por otra persona. Eso tampoco está permitido y ésta es la situación de la que estamos hablando. Actualmente se gestiona con`securityPostDenormalize()`. Pero en su lugar quiero gestionarlo con la validación ¿Por qué? Porque la pregunta a la que estamos respondiendo es la siguiente:

> ¿Son válidos los datos de `owner` que se envían?

Y... validar los datos es... ¡el trabajo de la validación!

Elimina el `securityPostDenormalize()`:

[[[ code('bd19ffd202') ]]]

Y para demostrar que esto era importante, vuelve a ejecutar la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Sí! Falló en la línea 132... que es ésta de aquí abajo. Vamos a reescribir esto con un validador personalizado, que en realidad es mucho más bonito.

## Crear la validación personalizada

Ah, pero como esto fallará por validación cuando acabemos, cambia a`assertStatus(422)`:

[[[ code('91c485445c') ]]]

La idea es que se nos permite PATCH este usuario, pero enviamos datos no válidos: no podemos establecer este propietario a alguien que no seamos nosotros mismos.

Vale, dirígete a la línea de comandos y ejecuta:

```terminal
php ./bin/console make:validator
```

Dale un nombre chulo como `IsValidOwnerValidator`. En Symfony, los validadores son dos clases diferentes. Abre primero `src/Validator/IsValidOwner.php`:

[[[ code('2abcf2b11a') ]]]

Esta clase ligera se utilizará como atributo... y sólo contiene opciones que podemos configurar, como `$message`, que es suficiente. Cambiemos el mensaje por defecto por algo un poco más útil:

[[[ code('e632cf89e2') ]]]

La segunda clase es la que se ejecutará para manejar la lógica:

[[[ code('20258f5bbc') ]]]

Lo veremos en un momento... pero antes utilicemos la nueva restricción. Sobre `DragonTreasure`, abajo en la propiedad `owner`... ahí vamos... añade el nuevo atributo: `IsValidOwner`:

[[[ code('e8549102c9') ]]]

## Rellenar la lógica del validador

Ahora que tenemos esto, cuando se valide nuestro objeto, Symfony llamará a`IsValidOwnerValidator` y nos pasará el `$value` -que será el objeto `User`- y la restricción, que será `IsValidOwner`.

Hagamos un poco de limpieza. Elimina el `var` y sustitúyelo por`assert($constraint instanceof IsValidOwner)`:

[[[ code('22fbf32f58') ]]]

Eso es sólo para ayudar a mi editor: sabemos que Symfony siempre nos pasará eso. A continuación, fíjate en que está comprobando si el `$value` es nulo o está vacío. Y si lo es, no hace nada. Si la propiedad `$owner` está vacía, eso sí que debería gestionarlo una restricción diferente.

De vuelta en `DragonTreasure`, añade `#[Assert\NotNull]`:

[[[ code('6b92e8ef08') ]]]

Así, si se olvidan de enviar `owner`, esto se encargará de ese error de validación. De vuelta dentro de nuestro validador, si nos encontramos en esa situación, podemos simplemente devolver:

[[[ code('7115db94a3') ]]]

Debajo de esto, añade un `assert()` más que `$value` es un `instanceof User`.

Realmente, Symfony nos pasará cualquier valor que se adjunte a esta propiedad... pero sabemos que siempre será un `User`:

[[[ code('90eeece145') ]]]

Por último, elimina `setParameter()` -que no es necesario en nuestro caso- y`$constraint->message` lee la propiedad `$message`:

[[[ code('bfbe36d59b') ]]]

Llegados a este punto, ¡tenemos un validador funcional! Excepto que... va a fallar en todas las situaciones. Ah, al menos asegurémonos de que está siendo llamado. Ejecuta nuestra prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Bonito fallo! Un 422 procedente de la línea 110 de `DragonTreasureResourceTest`... porque nuestra restricción nunca se satisface.

## Comprobación de la propiedad en el validador

Por último, podemos añadir nuestra lógica de negocio. Para hacer la comprobación de propietario, necesitamos saber quién está conectado. Añade un método `__construct()`, autocablea nuestra clase favorita `Security`... y pondré `private` delante, para que se convierta en una propiedad:

[[[ code('3cb4f4a890') ]]]

Abajo, pon `$user = $this->security->getUser()`. Y si no hay ningún usuario por alguna razón, lanza un `LogicException` para que las cosas exploten:

[[[ code('55f5589469') ]]]

¿Por qué no lanzar un error de validación? Podríamos... pero en nuestra aplicación, si un usuario anónimo está cambiando de alguna manera con éxito un `DragonTreasure`... tenemos algún tipo de error de configuración.

Por último, si `$value` no es igual a `$user` -por tanto, si `owner` no es`User` -, añade ese fallo de validación:

[[[ code('bfc68bf310') ]]]

¡Ya está! ¡Vamos a probar esto!

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

Y... ¡bingo! Tanto si estamos creando como editando un `DragonTreasure`, no se nos permite establecer como propietario a alguien que no seamos nosotros.

Y podemos añadir cualquier otra fantasía que queramos. Por ejemplo, si el usuario es un administrador, volver para que los usuarios administradores puedan asignar el `owner` a cualquiera:

[[[ code('7eb8843df1') ]]]

Esto me encanta. Pero... sigue habiendo un gran agujero de seguridad: ¡un agujero que permitirá a un usuario robar los tesoros de otra persona! ¡No mola! Averigüemos cuál es a continuación y aplastémoslo.
