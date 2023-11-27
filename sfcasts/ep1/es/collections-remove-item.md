# Eliminar elementos de una colección

Nuestro flamante usuario es el orgulloso propietario de dos tesoros con los identificadores `7` y `44`. Actualicemos este usuario para ver si podemos hacer algunos cambios en `$dragonTreasures`. Utiliza la ruta `PUT`, haz clic en "Probar" y... veamos... el `id` que necesitamos es `14`... así que lo introduciré. También eliminaré todos los campos excepto`dragonTreasures` para que podamos centrarnos.

Sabemos que actualmente tiene dos tesoros deslumbrantes: `/api/treasures/7` y`/api/treasures/44`. Así que si enviamos esta petición, en teoría, eso no debería hacer... ¡nada! Y si miramos aquí abajo... sí: no ha hecho ningún cambio.

Supongamos que queremos añadir un nuevo `DragonTreasure` a este recurso. Para ello, listamos los dos que ya tiene, junto con `/api/treasures/8`. Estoy totalmente seguro de que es un `id` válido. Cuando pulsamos "Ejecutar"... funciona de maravilla. El sistema serializador se dio cuenta de que ya tenía estos dos primeros, así que no hizo nada con ellos. Sólo añadió el nuevo con id `8`.

## Eliminar un elemento de una colección

Eso está muy bien, pero de lo que realmente quiero hablar es de eliminar un tesoro. Supongamos que nuestro dragón se dejó uno de estos tesoros en el bolsillo del pantalón y lo lavó accidentalmente en la lavandería. No puedo culparles. Yo siempre pierdo mi bálsamo labial ahí. Como ahora el tesoro está empapado y no sirve para nada, tenemos que eliminarlo de la lista de tesoros. No hay problema Mencionaremos los dos que aún tiene nuestro dragón y eliminaremos el otro. Cuando pulsamos "Ejecutar"... ¡estalla!

> Se ha producido una excepción al ejecutar una consulta: [...] Violación no nula: 7.
> valor nulo en la columna "owner_id"

¿Qué ha ocurrido? Bueno, nuestra aplicación estableció la propiedad `$owner` para el `DragonTreasure` que acabamos de eliminar en `null`... y ahora está intentando guardarlo. Pero como la tenemos establecida en`nullable: false`, está fallando.

[[[ code('15cff49a62') ]]]

Pero... demos un paso atrás y veamos el cuadro completo. Primero, el serializador se dio cuenta de que los tesoros `7` y `8` ya pertenecían a `User`... así que no hizo nada con ellos. Pero entonces se dio cuenta de que el tesoro con id 44 -que pertenecía a este `User` - ¡ha desaparecido!

Por eso, en nuestra clase `User`, el serializador llamó a`removeDragonTreasure()`. Lo realmente importante es que toma ese`DragonTreasure` y establece el `owner` en `null` para romper la relación. Dependiendo de tu aplicación, puede que eso sea exactamente lo que quieres. Tal vez permitas que`dragonTreasures` no tenga `owner`... como si... estuvieran aún sin descubrir y esperando a que un dragón los encuentre. Si ése es el caso, sólo querrás asegurarte de que tu relación permite `null`... y todo se salvará sin problemas.

Pero en nuestro caso, si un `DragonTreasure` ya no tiene un `owner`, queremos borrarlo por completo. Podemos hacerlo en `User`... muy arriba en la propiedad `dragonTreasures`. Después de `cascade`, añade una opción más aquí: `orphanRemoval: true`.

[[[ code('aa791b1eab') ]]]

Esto le dice a Doctrine que si alguno de estos `dragonTreasures` queda "huérfano" -lo que significa que ya no tiene propietario- debe ser eliminado.

Vamos a probarlo. Cuando volvamos a pulsar "Ejecutar"... ¡ya está! Se guarda sin problemas.

Siguiente paso: Volvamos a los filtros y veamos cómo podemos utilizarlos para buscar en recursos relacionados.
