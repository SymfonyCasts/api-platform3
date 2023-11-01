# Escribir en una relación de colección

Estamos tan cerca de reimplementar completamente nuestra API utilizando estas clases personalizadas... ¡Qué emoción!

Vamos a ejecutar todas las pruebas para ver en qué punto estamos.

```terminal
symfony php bin/phpunit
```

Y... todo pasa excepto una. Esta prueba problemática es`UserResourceTest::testTreasuresCannotBeStolen`. ¡Vamos a comprobarlo!

Abre `tests/Functional/UserResourceTest.php` y busca`testTreasuresCannotBeStolen()`. Aquí lo tienes.

[[[ code('74b89d1ebb') ]]]

Leamos la historia. Actualizamos un usuario e intentamos cambiar su propiedad `dragonTreasures`para que contenga un tesoro propiedad de otra persona. La prueba busca un código de estado 422 -porque queremos evitar el robo de tesoros-, pero la prueba falla con un 200.

Pero aparte de todo el tema del robo, ésta es la primera prueba que hemos visto que escribe en un campo de relación de colección. Y ése es un tema interesante por sí solo.

## ¿Evitar los campos de colección escribibles?

En primer lugar, si puedes, te recomiendo que no permitas que los campos de relación de colección como éste sean escribibles. Es decir, puedes hacerlo... pero añade complejidad. Por ejemplo, como muestra esta prueba, tenemos que preocuparnos de cómo establecer la propiedad `dragonTreasures`cambia el propietario de ese tesoro. Y ya existe otra forma de hacerlo: haz una petición `patch()` a este tesoro y... cambia la propiedad`owner`. ¡Sencillo!

Pero, si aún quieres permitir que tu relación de colección sea escribible en tu sistema DTO, bien, aquí tienes cómo hacerlo. Es broma, no está tan mal.

## Probar la escritura de la colección

Empieza por duplicar esta prueba. Cámbiale el nombre a `testTreasuresCanBeRemoved`. Lo he escrito mal - el mío dice `cannot`, que es lo contrario de lo que quiero probar - así que asegúrate de que lo pones bien en tu código.

[[[ code('0ff4066779') ]]]

Ahora podemos arreglarlo un poco. Haz que el primer `$dragonTreasure` pertenezca a`$user`. Luego crea un segundo `$dragonTreasure` también propiedad de `$user`, pero no necesitaremos una variable para él... ya lo verás. Por último, añade un tercer `$dragonTreasure`llamado `$dragonTreasure3` que sea propiedad de `$otherUser`.

[[[ code('3613cc7e51') ]]]

Así que tenemos tres `dragonTreasures`, dos propiedad de `$user`, y uno de`$otherUser`. Aquí abajo, parcheamos para modificar `$user`. Elimina `username` -no nos importa- y envía dos `dragonTreasures`: el primero y el tercero:`/api/treasures/` `$dragonTreasure3->getId()` .

[[[ code('f0cdc4f723') ]]]

Vamos a comprobar dos cosas. En primer lugar, que se elimine el segundo tesoro de este usuario. Piénsalo: `$user` empezó con estos dos tesoros... y el hecho de que no se envíe el IRI de este segundo tesoro significa que queremos que se elimine de `$user`.

En segundo lugar, he añadido `$dragonTreasure3` temporalmente para demostrar que los tesoros se pueden robar. Actualmente es propiedad de `$otherUser`, pero lo pasamos a `dragonTreasures`... y vamos a comprobar que el propietario de `$dragonTreasure3` cambia de`$otherUser` a `$user`. No es el comportamiento final que queremos, pero nos ayudará a que funcione toda la escritura de relaciones. Luego nos preocuparemos de evitarlo.

Aquí abajo, `->assertStatus(200)` y luego ampliaremos la prueba diciendo`->get('/api/users/' . $user->getId())` y `->dump()`.

[[[ code('9fca636c25') ]]]

Quiero ver qué aspecto tiene el usuario después de la actualización. Por último, afirma que el `length` del campo `dragonTreasures` -necesito comillas en esto- es 2, para los tesoros 1 y 3. Luego afirma que `dragonTreasures[0]` es igual a`'/api/treasures/'.`, seguido de `$dragonTreasure->getId()`. Cópialo, pégalo y afirma que la clave 1 es `$dragonTreasure3`.

[[[ code('f42ca60613') ]]]

¡Estupendo! Esa prueba ha costado trabajo, pero será superútil. Vamos... ¡a ejecutarla y a ver qué pasa! Copia el nombre del método y, en tu terminal, ejecútalo:

```terminal
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Y con "no se puede eliminar", quiero decir, por supuesto, que se puede eliminar. Vaya locura de copiar y pegar. Ya está. Y... falla, en la línea 81. Esto significa que la petición se ha realizado correctamente... pero los`dragonTreasures` siguen siendo los dos originales: `/api/treasures/2` en lugar de`/api/treasures/3`. No se han realizado cambios en los tesoros.

¿Por qué? Averigüémoslo a continuación y aprovechemos el componente accesor de propiedades para asegurarnos de que los cambios se guardan correctamente.
