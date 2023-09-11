# Ejecutar Código "Al Publicar"

Un pequeño detalle sobre los procesadores de estado. El comando `make:state-processor` creó el método `process()` con un retorno `void`. Y... eso tiene sentido. API Platform nos pasa los datos y nuestro trabajo es sólo guardarlos... no devolver nada.

Sin embargo, técnicamente el método `process()` puede devolver algo. Y, por coherencia, devolveré algo. Elimina el tipo `void` y, al final, devuelve `$data`.

[[[ code('fae1d3f405') ]]]

Repetiré esto en `UserHashPasswordStateProcessor` por coherencia.

[[[ code('6844996198') ]]]

Éste es el trato: si devuelves algo, ésa será la "cosa" que finalmente se serialice y se devuelva como JSON. Si no devuelves nada, se serializará `$data`. Así que, al devolver `$data`... no estamos cambiando ningún comportamiento. Pero es interesante saber que podrías devolver algo diferente.

## Detectar cambios: datos_anteriores vs UnidadDeTrabajo

Bien, volvamos a nuestro objetivo. Después de guardar, necesitamos detectar si el campo `isPublished` cambió de falso a verdadero, para poder ejecutar algún código personalizado. Pero cuando se llama al procesador de estado, el JSON del usuario ya se ha utilizado para actualizar el objeto. Así que `$data` ya tendrá `isPublished` verdadero.

En el último tutorial, tuvimos una situación similar con un validador en el que necesitábamos comprobar si el propietario de un `DragonTreasure` había cambiado. Esta lógica vive en`TreasureAllowToChangeValidator`. Empezamos con `$value`, que es una colección de objetos `DragonTreasure`, hacemos un bucle sobre ellos y luego utilizamos `UnitOfWork` de Doctrine para ver qué aspecto tenía cada `DragonTreasure` cuando se cargó originalmente de la base de datos.

[[[ code('66546bf2f1') ]]]

¿Deberíamos utilizar ese mismo truco aquí para ver qué aspecto tenía originalmente la propiedad `isPublished`? Podríamos... ¡pero hay una forma más fácil!

API Platform tiene un concepto de "datos anteriores". Cuando se inicia la petición, la API Platform clona el objeto de nivel superior. Así, si estamos editando un `DragonTreasure`, lo coge de la base de datos utilizando nuestro proveedor de estado, lo clona y, a continuación, guarda ese clon "original" por si nos resulta útil. Podemos utilizarlo para ver si el valor de `isPublished` ha cambiado.

Pero espera, ¿por qué no hicimos esto de los "datos anteriores" en el último tutorial para el validador? La razón es sutil. Para el validador, el objeto de nivel superior era un objeto `User`. Cuando PHP clona un objeto, es un clon "superficial": cualquier propiedad string, int o booleana se copia en el clon. Pero cualquier propiedad del objeto -como los objetos`DragonTreasure` - no se copia: tanto el clon como los objetos `User`originales apuntan a los mismos objetos `DragonTreasure` en memoria. Así que cuando se actualiza el`owner` de esos tesoros... eso afectaba tanto al objeto principal como al "objeto anterior" clonado. Por eso tuvimos que profundizar y utilizar `UnitOfWork`.

Pero en este caso, la propiedad `isPublished` es una aburrida propiedad booleana escalar. Así que si podemos obtener los datos anteriores, eso tendrá el valor correcto, original, de `isPublished`.

¡Estupendo! Entonces... ¿cómo obtenemos los datos anteriores? Fíjate en que se nos pasa un argumento llamado`$context`... que está lleno de información útil. Vamos a `dd()`. 

[[[ code('f876c16baa') ]]]

A continuación, copia el nombre de la prueba en la que estamos trabajando y... ejecútala:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Oooo: un montón de cosas buenas aquí. Tenemos el objeto de operación actual... y aquí está: `previous_data`. Fíjate en esa preciosa propiedad `isPublished`: ¡es falsa!

Deshazte de `dd()`. En la parte inferior, pon `$previousData = $context['previous_data']`. Y, si no está ahí -lo que ocurrirá para una petición de `POST` - pon `null`. Voy a pegar el resto del código que detecta si `isPublished` cambió de falso a verdadero. En realidad... este no es el mejor código que he escrito nunca - es un poco confuso y no te permitirá publicar inmediatamente a través de un `POST`... pero funcionará para nuestros propósitos. Dentro, añade un volcado.

[[[ code('a39c200601') ]]]

¡Vamos a hacerlo! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Y... ¡le damos al volcado!

## Prueba y creación de notificaciones

Nuestro proyecto tiene una entidad `Notification` sin usar que creé antes de grabar sólo para esta función: se refiere a un tesoro y tiene un mensaje. Nada del otro mundo. Vamos a crear una de estas cuando publiquemos... haciendo primero una prueba para ello. ¡TDD!

Al final de la prueba, di `NotificationFactory` -que es una fábrica de Foundry que he creado-, `::repository()` -para obtener un ayudante de repositorio- y luego`->assert()->count(1)`.

[[[ code('fe14151120') ]]]

Con Foundry, nuestra base de datos siempre está vacía al inicio de una prueba: así que comprobar 1 fila es perfecto.

Volvemos al procesador, eliminamos el `dd()`... y comprobamos que la prueba falla nuestra nueva aserción:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

¡Excelente! De vuelta, empieza por autocablear un `EntityManagerInterface`privado`$entityManager`. A continuación, pegaré un aburrido código que crea un`Notification` y lo persiste.

[[[ code('5f5a2dbcb7') ]]]

Genial. Y la prueba dice...

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

... ¡que molamos! Lo siguiente: es hora de volvernos locos creando una clase ApiResource totalmente personalizada que no sea una entidad.
