# Campos totalmente personalizados

Pongámonos salvajes. Quiero añadir un nuevo campo totalmente personalizado y loco a nuestra API `DragonTreasure` que no se corresponda con ninguna propiedad de nuestra clase. Bueno, en realidad, en la parte 1 de esta serie aprendimos que es posible añadir campos personalizados creando un método getter y añadiendo un grupo de serialización sobre él. Pero esa solución sólo funciona si podemos calcular el valor del campo únicamente a partir de los datos del objeto. Si, por ejemplo, necesitamos llamar a un servicio para obtener los datos, entonces no tendremos suerte.

Añadir un nuevo campo cuyos datos se calculen a partir de un servicio es otro as en la manga del normalizador personalizado. Y como ya tenemos uno configurado, he pensado que podríamos utilizarlo para ver cómo funciona.

## Prueba del campo IsMe

Ve a `DragonTreasureResourceTest` y busca`testOwnerCanSeeIsPublishedField()`. Cámbiale el nombre a`testOwnerCanSeeIsPublishedAndIsMineFields()`:

[[[ code('29adb7037b') ]]]

Esto es un poco tonto, pero si tenemos un `DragonTreasure`, vamos a añadir una nueva propiedad booleana llamada `$isMine` establecida en `true`. Así que, abajo del todo, diremos `isMine` y esperaremos que sea `true`:

[[[ code('034f0a0bc7') ]]]

Copia ese nombre de método, luego gira y ejecuta esta prueba:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

¡Tada! Es `null` porque el campo aún no existe.

## Devolver el campo personalizado

¿Cómo podemos añadirlo? Ahora que hemos pasado por el engorro de configurar el normalizador, ¡es fácil! El sistema normalizador hará lo suyo, devolverá los datos normalizados y luego, entre eso y la declaración `return`, podemos... ¡joder!

[[[ code('8a60aaf853') ]]]

Copia la sentencia if de aquí arriba. Podría ser más inteligente y reutilizar código, pero está bien. Si el objeto es un `DragonTreasure` y poseemos este`DragonTreasure`, diremos `$normalized['isMine'] = true`:

[[[ code('aaeeb43eb3') ]]]

¡Ya está! Cuando ejecutemos la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

¡Todo verde!

## Campos personalizados que faltan en los documentos

Pero estos campos personalizados tienen un inconveniente práctico: no estarán documentados en nuestra API. ¡Nuestros documentos de la API no tienen ni idea de que esto existe!

Si necesitas un supercampo personalizado que requiera lógica de servicio... y necesitas que esté documentado, tienes dos opciones. En primer lugar, podrías añadir una propiedad no persistente `isMe` a tu clase y luego rellenarla con un proveedor de estado. Aún no hemos hablado de los proveedores de estado, pero son la forma en que se cargan los datos. Por ejemplo, nuestras clases ya utilizan un proveedor de estado Doctrine entre bastidores para consultar la base de datos. Hablaremos de los proveedores de estado en la parte 3 de esta serie.

La segunda solución sería utilizar el normalizador personalizado como hicimos nosotros, y luego intentar añadir el campo a los documentos OpenAPI manualmente mediante el truco de la fábrica OpenAPI que mostramos antes.

A continuación: supongamos que un usuario tiene permiso para editar algo... pero hay ciertos cambios en los datos que no puede hacer -por ejemplo, podría establecer un campo en`foo` pero no puede cambiarlo a `bar` porque no tiene suficientes permisos. ¿Cómo debemos manejar esto? Es la seguridad unida a la validación.
