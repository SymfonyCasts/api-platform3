# Validador más sencillo para comprobar el cambio de estado

Sólo nos queda una prueba que falla. Al parecer, podemos robar tesoros parcheando a un usuario y enviando el conjunto `dragonTreasures` a un tesoro que pertenece a otra persona. Esto debería darnos un código de estado `422`, pero obtenemos 200.

Pero no pasa nada: ya lo arreglamos en el tutorial anterior. Ahora sólo tenemos que reactivar y adaptar ese validador.

## Volver a añadir la restricción

En `UserApi`, encima de la propiedad `$dragonTreasures`, podemos eliminar `#[ApiProperty]`y añadir `#[TreasuresAllowedOwnerChange]`.

[[[ code('36a6fd35e1') ]]]

En el último tutorial, pusimos esto encima de esa misma propiedad `$dragonTreasures`, pero dentro de la entidad `User`. El validador haría un bucle sobre cada `DragonTreasure`, utilizaría el `UnitOfWork` de Doctrine para obtener el `$originalOwnerId`, y luego comprobaría si el `$newOwnerId` es diferente del original. Si lo fuera, crearía una violación.

## Adaptar el validador

Lo primero es lo primero: la restricción ya no se utilizará en una propiedad que contenga un objeto `Collection`: la nueva propiedad contiene una matriz simple. También`dd($value)`.

[[[ code('4bc40065c7') ]]]

En la prueba, encima, pon un `dump()` que diga `Real owner is` con`$otherUser->getId()`. Eso nos ayudará a rastrear si está robado.

[[[ code('fa63cce8cf') ]]]

Ejecuta sólo esta prueba:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Y... ¡perfecto! Se supone que el "Propietario real" es `2`, y el volcado del validador muestra un único objeto `DragonTreasureApi`.

Recordatorio: este volcado es la propiedad `dragonTreasures` del `UserApi` que se está actualizando. Y, aunque no podamos verlo aquí, el id de ese usuario es 1. Pero, en el volcado, fíjate en el propietario: ¡sigue siendo `2`! ¡Sigue siendo el propietario correcto!

Cuando hacemos la petición PATCH, este tesoro se carga desde la base de datos, se transforma en un `DragonTreasureApi`, y luego se establece en la propiedad `dragonTreasures`del `UserApi`. Pero, nada ha cambiado -todavía- el`owner` del tesoro: sigue teniendo el `owner` original.

La parte problemática viene después, cuando nuestro procesador de estado, en realidad,`UserApiToEntityMapper`, mapea la propiedad `dragonTreasures` de `UserApi` a la entidad`User`. Eso hace que se llame a `User.addDragonTreasure()`... y eso hace que se llame a `DragonTreasure.setOwner()`... con el nuevo objeto `User`.

Así que, aunque las cosas parezcan estar bien ahora en el validador -el propietario sigue siendo el original-, el tesoro acabará siendo robado. Atención: añade un `return` al validador para que siempre pase. Y en `UserResourceTest`,`->get('/api/users/'.$otherUser->getId())` y `->dump()`.

[[[ code('b4f337b168') ]]]

Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Y... ¡sí! El campo `dragonTreasures` está vacío para `$otherUser` ¡porque les han robado el tesoro! ¡Están locos!

## Cambiar la Restricción para que esté por encima de la Clase

Para solucionar este lío en el validador, necesitamos saber dos cosas. En primer lugar, cuál es el propietario original de cada tesoro. Y lo tenemos: cada objeto `DragonTreasureApi`sigue teniendo su propietario original. En segundo lugar, necesitamos saber a qué usuario pertenecen ahora estos tesoros: a qué objeto de `UserApi` pertenece esta propiedad. Y eso no lo tenemos.

Para conseguirlo, podemos desplazar la restricción de esta propiedad concreta -a la que sólo tenemos acceso a los objetos `DragonTreasureApi` - hasta la clase. Eso nos dará acceso a todo el objeto `UserApi`.

[[[ code('d6d31bebc6') ]]]

El paso 1 es fácil... ¡mueve la restricción para que esté por encima de la clase! Para ello, abre la clase de la restricción. Deshazte de las anotaciones, ya que las anotaciones están muertas... y no las vamos a utilizar. Luego cambia esto de `TARGET_PROPERTY` y`TARGET_METHOD` a `TARGET_CLASS`.

[[[ code('a0955d06ce') ]]]

Por alguna razón, mi editor añade un `\` extra ahí, así que elimínalo. También tenemos que anular un método. No estoy seguro de por qué tenemos que especificar el objetivo en ambos sitios... este método es específico del sistema de validación, pero no es gran cosa:`return self::CLASS_CONSTRAINT`.

Añade también un tipo de retorno: `string|array`. Eso evitará un aviso de desaprobación.

[[[ code('5181896543') ]]]

Vuelve al validador, `dd($value)`... y vuelve a ejecutar la prueba:

[[[ code('a0f8148353') ]]]

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Veamos... ¡sí! Vuelca todo el objeto `UserApi` con ID `1`. Bien! La propiedad `dragonTreasures` contiene ese único tesoro... ¡y aquí abajo vemos a su propietario original! Ahora sólo tenemos que comprobar si el nuevo propietario es distinto del propietario original. ¡Así de fácil!

De vuelta en el validador, `assert()` que `$value` es un `instanceof UserApi`.

[[[ code('da6e77d0c1') ]]]

Luego, `foreach` sobre `$value->dragonTreasures as $dragonTreasureApi`.

[[[ code('5c75e393cb') ]]]

Lo positivamente encantador es que ya no necesitamos nada de esto de `$unitOfWork`. ¡Bórralo! Luego di `$originalOwnerId = $dragonTreasureApi->owner->id`. El `$newOwnerId` será `$value->id`. ¡Y ya está!

Para codificar a la defensiva, puedes añadir un `?` aquí... en caso de que no haya propietario... como si se tratara de un nuevo tesoro.

[[[ code('8867de088c') ]]]

La lógica aquí abajo no está rota, así que no hay nada que arreglar: si no tenemos el`$originalOwnerId` o el `$originalOwnerId` es igual a `$newOwnerId`, todo va bien. Si no, construye esta violación. Elimina también esta línea `$unitOfWork` de aquí, esas declaraciones `use`... y este constructor `EntityManagerInterface`. Gracias al nuevo sistema DTO, ahora tenemos un validador personalizado muy aburrido.

Vuelve a hacer la prueba... y cruza los dedos de las manos y los pies para tener suerte:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

¡Lo hemos conseguido! Choca los cinco con algo y, a continuación, elimina este `->dump()` de la parte superior. Respira hondo: ejecuta todo el conjunto de pruebas:

```terminal
symfony php bin/phpunit
```

¡Todo verde! ¡Hemos reconstruido completamente nuestro sistema utilizando DTOs! ¡Woohoo!

Y... ¡hemos terminado! Nos ha costado un poco de trabajo configurar todo esto, ¡pero ése es el objetivo de los DTOs! Hay más trabajo de base al principio a cambio de más flexibilidad y claridad más adelante, sobre todo si estás construyendo una API realmente robusta que quieres mantener estable.

Como siempre, si tienes preguntas, comentarios o quieres POSTULAR sobre las cosas chulas que estás construyendo, estamos a tu disposición en los comentarios. Muy bien amigos, ¡hasta la próxima!
