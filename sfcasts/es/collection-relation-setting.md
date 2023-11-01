# Colección escribible mediante el PropertyAccessor

Para ver lo que ocurre aquí, dirígete al mapeador: `UserApiToEntityMapper`. La petición`patch()` tomará estos datos, los rellenará en `UserApi`... y luego los volveremos a mapear en la entidad de este mapeador.

Y... la razón por la que falla la prueba es bastante obvia: ¡no estamos mapeando la propiedad`dragonTreasures` del DTO a la entidad!

Vamos a `dump($dto)` para ver qué aspecto tiene después de deserializar los datos.

[[[ code('d6a8035083') ]]]

Ejecuta de nuevo la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Y... ¡guau! Los `dragonTreasures` del DTO siguen siendo los dos originales. Esto me dice que este campo se está ignorando por completo: no se está deserializando. Y apuesto a que sabes la razón. ¡Dentro de `UserApi`, la propiedad `$dragonTreasures`no es `writable`! Pero está muy bien ver que `writable: false` hace su trabajo.

[[[ code('8f80d5391c') ]]]

Cuando volvamos a ejecutar la prueba, verás la diferencia.

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

¡Sí! Sigue habiendo dos tesoros, pero los ID son "1" y "3". Así que `UserApi` parece correcto.

## Pasar de DragonTreasureApi -> DragonTreasure

Ahora, tenemos que tomar esta matriz de objetos `DragonTreasureApi` y mapearlos a objetos de entidad`DragonTreasure` para que podamos colocarlos en la entidad `User`. Una vez más, ¡necesitamos un micro mapeador!

Ya sabes lo que hay que hacer: añade `private MicroMapperInterface $microMapper`... y vuelve aquí abajo... empieza con `$dragonTreasureEntities = []`. Yo voy a simplificar las cosas y utilizaré un buen y anticuado `foreach`. Haz un bucle sobre `$dto->dragonTreasures` como`$dragonTreasureApi`. Entonces diremos que `$dragonTreasureEntities[]` es igual a`$this->microMapper->map()`, pasando `$dragonTreasureApi` y `DragonTreasure::class`. Y como ya habrás adivinado, vamos a pasar`MicroMapperInterface::MAX_DEPTH` ajustado a `0`.

[[[ code('73bb632870') ]]]

`0` está bien aquí porque sólo tenemos que asegurarnos de que el mapeador del tesoro dragón consulta la entidad `DragonTreasure` correcta. Si tiene una relación, como `owner`, no nos importa si ese objeto está totalmente mapeado y poblado. Aquí abajo, `dd($dragonTreasureEntities)`.

[[[ code('5bdaca14c5') ]]]

¡Pruébalo!

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Y... ¡tiene buena pinta! Tenemos 2 tesoros con `id: 1`... y aquí abajo`id: 3`.

## Llamar a los métodos Sumador/Recuperador

Así que todo lo que tenemos que hacer ahora es colocarlo en el objeto `User`. Digamos `$entity->set`... pero... uh oh. ¡No tenemos un método `setDragonTreasures()`! Y eso es a propósito! Mira dentro de la entidad `User`. Tiene un método `getDragonTreasures()`, pero no `setDragonTreasures()`. En su lugar, tiene `addDragonTreasure()`y `removeDragonTreasure()`.

No voy a profundizar demasiado en por qué no podemos tener un definidor, pero está relacionado con el hecho de que necesitamos definir el lado propietario de la relación Doctrine. Hablamos de ello en nuestro tutorial sobre las relaciones Doctrine.

La cuestión es que si pudiéramos llamar simplemente a `->setDragonTreasures()`, no se guardaría correctamente. Tenemos que llamar a los métodos sumador y eliminador.

¡Y esto es complicado! Tenemos que mirar `$dragonTreasureEntities`, compararlo con la propiedad actual `dragonTreasures`, y luego llamar a los sumadores y eliminadores correctos para los tesoros que sean nuevos o eliminados. En nuestro caso, tenemos que llamar a`removeDragonTreasure()` para el del medio y a `addDragonTreasure()` para este tercero.

Escribir este código suena... molesto... y complicado. Afortunadamente, ¡Symfony tiene algo que hace esto! Es un servicio llamado "Property Accessor".

Dirígete aquí... y añade `private PropertyAccessorInterface $propertyAccessor`.

[[[ code('7da1af2757') ]]]

Property Accessor es bueno para establecer propiedades. Puede detectar si una propiedad es pública... o si tiene un método establecedor... o incluso métodos sumadores o eliminadores. Para utilizarlo, digamos `$this->propertyAccessor->setValue()` pasando el objeto al que estamos estableciendo datos - el `User` `$entity` , la propiedad que estamos estableciendo -`dragonTreasures` - y, por último, el valor: `$dragonTreasureEntities`.

Aquí abajo, vamos a `dd($entity)` para que veamos cómo queda.

[[[ code('b74268014b') ]]]

Respira hondo. Inténtalo:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Desplázate hacia arriba... hasta el objeto `User`. ¡Mira `dragonTreasures`! ¡Tiene dos elementos con `id: 1` y `id: 3`! Ha actualizado correctamente la propiedad `dragonTreasures` ¿Cómo demonios lo ha hecho? Llamando a `addDragonTreasure()` para el id 3 y a`removeDragonTreasure()` para el id 2.

Puedo demostrarlo. Aquí abajo, añade `dump('Removing treasure'.$treasure->getId())`.

Cuando volvamos a ejecutar la prueba...

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

¡Ahí está! ¡Eliminando el tesoro 2! La vida es buena. Elimina este `dump()`... así como el otro de aquí.

Veamos algo de verde. Ejecuta la prueba una última vez... con suerte:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

¡Pasa! La respuesta final contiene los tesoros `1` y `3`. ¿Qué ha pasado con el tesoro `2`? En realidad, se eliminó por completo de la base de datos. Entre bastidores, su propietario se estableció en `null`. Luego, gracias a `orphanRemoval`, cada vez que el propietario de uno de estos `dragonTreasures` se establece en `null`, se borra. Es algo de lo que ya hablamos en un tutorial anterior.

Antes de seguir adelante, tenemos que limpiar la prueba. Elimina la parte en la que estamos robando `$dragonTreasure3`. Nos desharemos de ese objeto de ahí, de la parte en la que lo colocamos aquí abajo, cambiaremos la longitud a `1`, y sólo probaremos ese. Así que esto ahora sí que es una prueba para eliminar un tesoro.

Celébralo eliminando este `->dump()`.

[[[ code('0bc8575f72') ]]]

Pero... los tesoros aún se pueden robar, lo cual es lamentable. Arreglemos el validador para esto... pero también hagámoslo mucho más sencillo, gracias al sistema DTO, a continuación.
