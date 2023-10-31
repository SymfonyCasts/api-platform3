# Seguridad en el campo con Parche

En un giro heroico de valentía, hemos decidido ejecutar todas las pruebas del tesoro del dragón:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Y... tenemos tres fallos, incluido uno de`testAdminCanPatchToEditTreasure` en la línea 200... que dice`->assertJsonMatched('isPublished', true)`. Eso falla porque... ¡no tenemos en absoluto un campo `isPublished` en nuestro `DragonTreasureApi`!

## Añadir el campo isPublished

Esto se debe a que se trata de un campo interesante. Antes, este campo sólo lo podían leer los usuarios administradores o el propietario. Volvamos a añadir este campo y mantengamos ese comportamiento. Digamos `public bool $isPublished = false`.

[[[ code('379ba758c6') ]]]

Entonces... entra en el mapeador para rellenar esto. Aquí abajo, deshazte de `TODO`y di `$entity->setIsPublished($dto->isPublished)`.

[[[ code('e65a12f5c3') ]]]

Así, si cambiamos `isPublished` en la llamada a la API, el nuevo valor se sincronizará con la entidad.

En el otro lado... no importa dónde... di`$dto->isPublished = $entity->getIsPublished()`.

[[[ code('d5ad5aa104') ]]]

¡Genial! Aún no tenemos seguridad... así que cuando ejecutamos las pruebas:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Algunas pasan, pero la original sigue fallando - `testGetCollectionOfTreasures` - porque no espera que `isPublished` esté ahí.

## Mostrar condicionalmente isPublished mediante seguridad

Fíjate: ésta es la primera prueba, y en la parte inferior hemos afirmado que éstas son las propiedades exactas que deberíamos tener si obtenemos tesoros como usuario anónimo. Por tanto, como no somos el propietario ni un administrador, no deberíamos ver `isPublished`

¿Cómo podemos hacerlo? Antes hemos trabajado con `DragonTreasureApiVoter`. Cuando lo llamamos con el atributo `EDIT`, comprueba si somos administradores y, si lo somos, nos da acceso. También comprueba si somos el propietario. Ésta es exactamente la lógica que queremos utilizar para determinar si el campo `isPublished` debe serializarse.

Así que... ¡vamos a utilizarla! Sobre esta propiedad, digamos`#[ApiProperty(security: 'is_granted("EDIT", object)')]`.

[[[ code('1ed624f9b1') ]]]

Si quieres, puedes cambiar este atributo por otra cosa -como `OWNER` -, si te resulta más claro. `EDIT` suena un poco raro aquí... ya que sólo estamos decidiendo si debemos incluir este campo en la respuesta... no "editarlo"... pero tú decides.

Y lo que es más importante, veamos si esto funciona. Ejecuta las pruebas:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

¡Se ha solucionado nuestra primera prueba! Ya no se muestra el campo `isPublished`. Pero, curiosamente, hemos hecho fallar otra prueba. ¡A la porra! Ahora es`testPublishTreasure` - falla en la línea 244.

Vamos a buscarlo. Vale, como su nombre indica, estamos probando si podemos publicar este tesoro. Creamos un tesoro que es`'isPublished' => false`, iniciamos sesión como su propietario y, a continuación, enviamos una petición a `patch()` para establecer `isPublished` en `true`. Por último, afirmamos que el JSON de la respuesta tiene `isPublished` verdadero.  Y eso es lo que falla.

## La opción de seguridad ApiProperty en las operaciones de parcheo

¿Por qué? Me llevó un poco de depuración desentrañar este misterio. El problema es que, cuando se deserializa el JSON, `isPublished` no es escribible.

La expresión `security` se llama tanto al serializar como al deserializar: al tomar el JSON de la petición y al actualizar el objeto. Por alguna razón, durante la deserialización, ¡nuestra expresión `security` devuelve false!

La razón es... posiblemente un error:  Tengo una incidencia abierta en API Platform. Cuando realizas una petición a `patch()`, nuestro proveedor de datos carga primero el objeto desde la base de datos. A pesar de ello, cuando se llama a la expresión durante la deserialización,`object` siempre es nulo. Y como nuestro votante sólo admite si `object` es un `DragonTreasureApi`, éste devuelve `false`. En última instancia, ningún votante admite esto, y cuando ocurre, se deniega el acceso. El resultado final es que `isPublished`no es escribible.

La solución es un poco extraña, pero quédate conmigo. Básicamente, vamos a permitir el acceso a este campo si `object === null` o`is_granted("EDIT", object)`.

[[[ code('c4fec1cc8f') ]]]

Piensa en esto. Si estamos leyendo un `DragonTreasure`, entonces `object` nunca es `null`. Siempre tendremos un objeto, por lo que siempre se llamará al votante. Este `object === null` sólo ocurrirá durante la deserialización: cuando estemos comprobando si podemos escribir este campo. Esto hace que el campo sea siempre escribible. Esto parece un problema, pero no lo es, porque ya tenemos `security` aquí arriba en `Post()` y `Patch()`. En `Patch`, sólo el propietario puede editar este objeto. Así que una vez superada la seguridad de `Patch`, ya sabemos que puede editar este objeto. Así que, aquí abajo, está bien que siempre podamos editar este campo.

Si esto te parece demasiado raro, otra estrategia es dejar la seguridad API fuera del campo por completo. Entonces, utilizaríamos el mapeador para manejar la configuración condicional del campo `isPublished`. Podríamos poner aquí una lógica de seguridad que dijera básicamente

> Sólo establece el campo `isPublished` en el DTO si eres el propietario. En caso contrario
> deja `isPublished` nulo por defecto.

Es bueno recordar que tenemos el control total de los datos a través de nuestros mapeadores.

Bien, volvamos atrás y añadamos de nuevo nuestra expresión de seguridad. Y vuelve también al mapeador: Acabo de darme cuenta de que también queremos mantener ese código `isPublished`... sólo que no en la declaración `if`.

Muy bien, ahora vuelve a ejecutar todas las pruebas.

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Y... ¡ooh! ¡Tan cerca! Sólo nos queda un fallo en `testPublishTreasure`. Esto prueba que, cuando se publica un tesoro, enviamos una notificación. Veamos cómo podemos resolverlo en nuestro nuevo sistema
