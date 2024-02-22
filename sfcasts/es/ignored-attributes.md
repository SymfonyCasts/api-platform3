# Otras estrategias de campo condicional

Sigamos jugando con cómo podemos ocultar o mostrar campos. Elimina el atributo `#[ApiProperty]`. A continuación, en la parte superior, establece la opción `normalizationContext`. Esto ya lo utilizamos en tutoriales anteriores... pero esta vez, en lugar de `groups`, establece una clave llamada`AbstractNormalizer::IGNORED_ATTRIBUTES` y, a continuación, ponla en una matriz. Dentro, pon `flameThrowingDistance`.

Que un campo sea legible o escribible depende del serializador. Esto le dice al serializador:

> ¡Eh! Cuando normalices, es decir, cuando pases a JSON, ignora esta propiedad.

Esto hará que sea escribible, pero no legible. Cuando lo probamos...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Eso es exactamente lo que ocurre! Para envolverlo en un signo de "no escribir", duplica este movimiento con `denormalizationContext`. Cópialo, ponle un "de" delante, y ahora cuando lo probemos:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Sí! `flameThrowingDistance` es "1", por lo que no se puede escribir, y aquí abajo... tampoco se puede leer. Genial.

Así que ésta es sólo una opción diferente que debería funcionar igual que `ApiProperty`... aunque he visto casos complejos en los que esta opción contextual funcionaba cuando la solución de`ApiProperty` no lo hacía. De todos modos, elimínalos.

## El atributo #[Ignorar

La última forma de ignorar un campo -si quieres ignorarlo por completo- es añadir un atributo llamado... `#[Ignore]`! Esto viene del sistema serializador de Symfony. Cuando probamos el test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Perfecto: No se puede escribir ni leer. ¡Genial!

Muy bien, vamos a darle al botón de reinicio de todo ese código ficticio. Deshazte del`#[Ignore]`... y veamos si tenemos alguna sentencia `use` extra aquí arriba. Luego, en nuestro procesador, elimina el `->dump()`... y en nuestra prueba, deshazte de ese campo extra y del otro `->dump()`. ¡Todo limpio!

## Evitar la escritura en el identificador

En este tema de lo legible y lo escribible, ahora mismo, podemos cambiar el campo`id` en una petición `PATCH`. Observa: pon esto en `47`... que me acabo de inventar, y... ¡falla con un error 500!

Abre el error:

> Entidad 47 no encontrada.

Eso viene de nuestro procesador de estado. Viene de aquí abajo... lee el `id` de aquí arriba e intenta encontrarlo en la base de datos... pero no está. Si hubiéramos utilizado un `id` válido, habría buscado esa otra entidad `User`... ¡y habríamos actualizado sus propiedades! Eso es un gran no-no. Al menos, tal y como está escrito nuestro código, al hacer que `id` sea escribible, estamos permitiendo al usuario cambiar qué usuario se está modificando.

Veamos el flujo completo. En primer lugar, nuestro proveedor encontró la entidad `User` original con la `id` de la URL... y la mapeó a un objeto `UserApi`. Bien hasta aquí. Después, durante la deserialización, el `id` del objeto `UserApi` se cambió a `47`. Por último, en el procesador de estado, intentamos consultar una entidad con `id=47`... que es, en definitiva, lo que habríamos guardado en la base de datos.

En `UserApi`, para arreglar esto, encima de `id`, añade `writable: false`. O podríamos utilizar el atributo `#[Ignore]` que vimos hace un segundo... ya que no queremos que sea legible ni escribible. La propiedad `id` ayuda a generar el IRI... pero en realidad no forma parte de nuestra API.

Si ejecutamos esa prueba ahora... pasa porque ignora el nuevo campo `id` en el JSON. La vida es buena.

Mientras estamos aquí, en `UserApi`, hay otras dos propiedades que, por ahora, quiero que sean de sólo lectura. Encima de `$dragonTreasures`, haz esto `writable: false`... aunque lo haremos escribible más adelante.

Abajo, haz lo mismo para `$flameThrowingDistance`... porque ésta es una propiedad falsa que estamos generando como un número aleatorio.

## Utilizar la "seguridad" para ocultar/mostrar un campo

Ah, y otra forma de controlar si un campo es legible o escribible es el atributo`security`. Por ejemplo, si `$flameThrowingDistance` sólo fuera legible o escribible si tienes un determinado rol, podrías utilizar el atributo `security` para comprobarlo. Veremos esto un poco más adelante.

## ¿Diferentes clases de entrada/salida?

Por último, quiero mencionar una última estrategia para los campos condicionales... aunque no lo haremos. Si el JSON de entrada y el JSON de salida de tu recurso API empiezan a parecer realmente diferentes, es posible tener clases distintas para tu entrada y tu salida. Podrías tener algo así como un `UserApiRead` y otro`UserApiWrite`. El `UserApiRead` se utilizaría para las operaciones de lectura como la recogida de`GET` y `GET`. Y `UserApiWrite` se utilizaría para las operaciones `PUT`, `PATCH` y `POST`.

Aunque, para ser sincero, aún no he jugado con esto. Debería funcionar, pero probablemente haya algunos baches y detalles por el camino. Otra cosa a tener en cuenta es que, en `UserApiWrite`, podrías, en teoría, establecer el `output`en `UserApiRead`. Eso permitiría al usuario enviar datos en el formato de`UserApiWrite`, pero ser devueltos JSON desde `UserApiRead`. Pero, para que esto funcione, después de guardar el `UserApiWrite` en tu procesador de estado, tendrías que convertirlo en un `UserApiRead` y devolverlo.

En fin, esto es definitivamente más avanzado, pero si te parece interesante y lo pruebas, ¡házmelo saber!

A continuación: Pulamos nuestro nuevo recurso API volviendo a añadir validación y seguridad.
