# Validación

Los usuarios de nuestra API pueden estropear las cosas de muchas maneras, por ejemplo con un JSON incorrecto o haciendo tonterías como introducir un número negativo en el campo `value`. ¡Esto es oro de dragón, no deuda de dragón!

## JSON no válido

Este capítulo trata sobre cómo manejar estas cosas malas con elegancia. Prueba con la ruta POST. Enviemos algo de JSON no válido. Pulsa Ejecutar. ¡Impresionante! ¡Un error `400`! Eso es lo que queremos. 400 -o cualquier código de estado que empiece por 4- significa que el cliente -el usuario de la API- ha cometido un error. en concreto, 400 significa "petición errónea".

En la respuesta, el tipo es `hydra:error` y dice: `An error occurred`
y `Syntax Error`. Ah, y este `trace` sólo se muestra en el entorno de depuración: no se mostrará en producción.

¡Así que esto es muy bonito! El JSON no válido se gestiona de forma automática.

## Restricciones de validación de reglas de negocio

Probemos algo diferente, como enviar JSON vacío. Esto nos da el temido error 500. Boo. Internamente, la API Platform crea un objeto `DragonTreasure`... pero no establece ningún dato en él. Y luego explota cuando llega a la base de datos porque algunas de las columnas son `null`.

Y, ¡nos lo esperábamos! Nos falta la validación. Añadir validación a nuestra API es exactamente igual que añadir validación en cualquier parte de Symfony. Por ejemplo, busca la propiedad`name`. Necesitamos que `name` sea obligatoria. Así que añade la restricción `NotBlank`, y pulsa tabulador. Oh, pero voy a buscar la declaración `NotBlank` `use` ... y cambiarla por `Assert`. Eso es opcional... pero es la forma en que los chicos guays suelen hacerlo en Symfony. Ahora di `Assert\NotBlank`:

[[[ code('b05489e9e8') ]]]

A continuación, añade una más: `Length`. Digamos que el nombre debe tener al menos dos caracteres, `max` 50 caracteres... y añade un `maxMessage`:`Describe your loot in 50 chars or less`:

[[[ code('5f18649857') ]]]

## Cómo se ven los errores en la respuesta

¡Buen comienzo! Inténtalo de nuevo. Coge ese mismo JSON vacío, pulsa Ejecutar, y ¡sí! ¡Una respuesta 422! Se trata de un código de respuesta muy común que suele significar que se ha producido un error de validación. Y ¡he aquí! El `@type` es `ConstraintViolationList`. Se trata de un tipo especial de JSON-LD añadido por API Platform. Anteriormente, lo vimos documentado en la documentación de `JSON-LD`.

Observa: ve a `/api/docs.jsonld` y busca un `ConstraintViolation`. ¡Ahí está! API Platform añade dos clases: `ConstraintViolation` y`ConstraintViolationList` para describir el aspecto que tendrán los errores de validación. Un`ConstraintViolationList` es básicamente una colección de `ConstraintViolations`... y luego describe cuáles son las propiedades de `ConstraintViolation`.

Podemos verlas aquí: tenemos una propiedad `violations` con `propertyPath`y luego la `message` debajo.

## Añadir más restricciones

¡Vale! Vamos a añadir unas cuantas restricciones más. Añade `NotBlank` por encima de `description`... y `GreaterThanOrEqual` a `0` por encima de `value` para evitar los negativos. Por último, para`coolFactor` utiliza `GreaterThanOrEqual` a 0 y también `LessThanOrEqual` a 10. Así que algo entre 0 y 10:

[[[ code('edb7d7cf25') ]]]

Y ya que estamos aquí, no necesitamos hacer esto, pero voy a inicializar`$value` a 0 y `$coolFactor` a 0. Esto hace que ambos no sean necesarios en la API: si el usuario no los envía, serán 0 por defecto:

[[[ code('343088fb55') ]]]

Vale, vuelve a probar esa misma ruta. ¡Mira qué validación más bonita! Prueba también a poner `coolFactor` en `11`. ¡Sí! Ningún tesoro mola tanto... bueno, a menos que sea un plato gigante de nachos.

## Pasar tipos malos

Vale, hay una última forma de que un usuario envíe cosas malas: pasando un tipo incorrecto. Así que `coolFactor: 11` fallará nuestras reglas de validación. Pero, ¿y si en su lugar pasamos un `string`? ¡Qué asco! Pulsa Ejecutar. Vale: un código de estado `400`, eso es bueno. Aunque, no es un error de validación, tiene un tipo diferente. Pero indica al usuario lo que ha ocurrido:

> el tipo del atributo `coolFactor` debe ser `int`, `string` dado.

¡Suficientemente bueno! Esto es gracias al método `setCoolFactor()`. El sistema ve el tipo `int` y por eso rechaza la cadena con este error.

Así que de lo único que tenemos que preocuparnos en nuestra aplicación es de escribir un buen código que utilice correctamente `type` y de añadir restricciones de validación: la red de seguridad que atrapa las violaciones de las reglas de negocio... como que `value` debe ser mayor que 0 o que `description`es obligatorio. API Platform se encarga del resto.

A continuación: nuestra API sólo tiene un recurso: `DragonTreasure`. Añadimos un segundo recurso -un recurso `User` - para que podamos vincular qué usuario posee qué tesoro en la API.