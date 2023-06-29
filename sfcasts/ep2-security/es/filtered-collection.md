# Filtrar la colección de relaciones

Oye, ¡hemos creado una API bastante elegante! Tenemos unos cuantos sub-recursos y datos de relación incrustados, que se pueden leer y escribir. Todo esto es estupendo... pero seguro que aumenta la complejidad de nuestra API, sobre todo en lo que se refiere a la seguridad.

Por ejemplo, ya no podemos ver tesoros no publicados desde las rutas GET colección o GET individual. Pero aún podemos ver tesoros no publicados si obtienes un usuario y lees su campo `dragonTreasures`.

## Escribir la prueba

Preparemos rápidamente una prueba para exponer este problema. Abre nuestro `UserResourceTest`. En la parte inferior, añade una función pública `testUnpublishedTreasuresNotReturned()`. Dentro de ella, crea un usuario con `UserFactory::createOne()`. A continuación, utiliza `DragonTreasureFactory`para crear un tesoro que sea `isPublished` falso y tenga su `owner` establecido en`$user`... sólo para que sepamos quién es el propietario.

Para la acción, digamos `$this->browser()`... y necesitamos iniciar sesión para utilizar la ruta... pero no nos importa con quién iniciamos sesión... así que digamos `actingAs()``UserFactory::createOne()` para iniciar sesión como otra persona.

Luego `->get()` `/api/users/` `$user->getId()` . Termina con `assertJsonMatches()`que el `length()` de `dragonTreasures` es cero -utilizando una función genial `length()` de esa sintaxis JMESPath:

[[[ code('1c405c7deb') ]]]

¡Vamos a probarlo! Copia el método... y ejecútalo con `--filter=` ese nombre:

```terminal-silent
symfony php bin/phpunit --filter=testUnpublishedTreasuresNotReturned
```

¡Vale! Esperaba que 1 fuera igual a 0 porque estamos devolviendo el tesoro no publicado... ¡pero no queremos!

## Cómo se cargan las relaciones

Primero... ¿por qué se devuelve este `DragonTreasure` inédito? ¿No creamos clases de extensión de consultas para evitar exactamente esto?

Bueno .... algo importante que hay que entender es que estas clases de extensión de consulta se utilizan sólo para la consulta principal en una ruta. Por ejemplo, si utilizamos el endpoint GET colección para tesoros, la consulta "principal" es para esos tesoros y se llama a la extensión de consulta colección.

Pero cuando hacemos una llamada a un punto final de usuario -como GET un único `User` - API Platform no está haciendo una consulta para cualquier tesoro: está haciendo una consulta para ese único `User`. Una vez que tiene ese `User`, para obtener ese campo `dragonTreasures`, no hace otra consulta para esos, al menos no directamente. En cambio, si abre la entidad `User`, la API Platform -a través del serializador- simplemente llama a`getDragonTreasures()`.

Así que consulta el `User`, llama a `->getDragonTreasures()`... y lo que devuelva se fija en el campo `dragonTreasures`. Y como esto devuelve todos los tesoros relacionados, eso es lo que obtenemos: incluidos los no publicados.

## Añadir un método Getter filtrado

¿Cómo podemos solucionar esto? Añadiendo un nuevo método que sólo devuelva los tesoros publicados. Digamos `public function getPublishedDragonTreasures()`, que devuelve un`Collection`. Dentro, podemos ponernos elegantes: devuelve `$this->dragonTreasures->filter()`pasándole una llamada de retorno con un argumento `DragonTreasure $treasure`. Luego, devuelve`$treasure->getIsPublished()`:

[[[ code('5a89ba2e38') ]]]

Es un truco ingenioso para recorrer todos los tesoros y obtener una nueva y brillante colección sólo con los publicados.

Nota al margen: una desventaja de este enfoque es que si un usuario tiene 100 tesoros... pero sólo 10 de ellos están publicados, internamente, Doctrine consultará primero los 100... aunque sólo devolvamos 10. Si tienes colecciones grandes, esto puede ser un problema de rendimiento. En nuestro tutorial de Doctrine, hablamos de solucionar esto con algo llamado [Sistema de criterios](https://symfonycasts.com/screencast/doctrine-relations/collection-criteria). Pero con ambos enfoques, el resultado es el mismo: un método que devuelve un subconjunto de la colección.

## Intercambiar el Getter en nuestra API

En este punto, el nuevo método funcionará, pero aún no forma parte de nuestra API. Desplázate hasta la propiedad `dragonTreasures`. Actualmente es legible y escribible en nuestra API. Haz que la propiedad sólo sea escribible:

[[[ code('e01049a325') ]]]

Luego, abajo en el nuevo método, añade `#[Groups('user:read')]` para que forme parte de nuestra API y `#[SerializedName('dragonTreasures')]` para darle el nombre original:

[[[ code('1f1b8dce19') ]]]

¡Redoble de tambores! Haz la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testUnpublishedTreasuresNotReturned
```

¡Explota! Porque... Tengo un error de sintaxis. Prueba de nuevo. ¡Todo verde!

Y... ¡hemos terminado! ¡Lo has conseguido! Muchas gracias por acompañarme en este gigantesco, genial y desafiante viaje por la API Platform y la seguridad. Algunas partes de este tutorial han sido bastante complejas... porque quiero que seas capaz de resolver problemas de seguridad reales y difíciles.

En el próximo tutorial, vamos a ver cosas aún más personalizadas y potentes que puedes hacer con la API Platform, incluyendo cómo utilizar clases para recursos API que no son entidades.

Mientras tanto, cuéntanos qué estás construyendo y, como siempre, estamos a tu disposición en la sección de comentarios. Muy bien amigos, ¡hasta la próxima!
