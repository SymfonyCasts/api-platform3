# 404 En Artículos No Publicados

Hemos dejado de devolver tesoros no publicados desde el punto final de la colección de tesoros, pero aún puedes recuperarlos desde el punto final GET one. Esto se debe a que estas clases `QueryCollectionExtensionInterface` sólo se invocan cuando obtenemos una colección de elementos, no cuando seleccionamos un único elemento.

Para comprobarlo, entra en nuestra prueba. Duplica la prueba de la colección, pégala y llámala `testGetOneUnpublishedTreasure404s()`. Dentro, crea sólo un `DragonTreasure`que no esté publicado... y haz una petición `->get()` a `/api/treasures/`... ¡oh! Necesito una variable `$dragonTreasure`. Eso está mejor. Ahora añade `$dragonTreasure->getId()`.

En la parte inferior, afirma que el estado es 404... y no necesitamos ninguna de estas afirmaciones, ni esta variable `$json`:

[[[ code('a31ba8979e') ]]]

¡Muy sencillo! Coge ese nombre de método y, ya sabes lo que hay que hacer. Ejecuta sólo esa prueba:

```terminal-silent
symfony php bin/phpunit --filter=testGetOneUnpublishedTreasure404s
```

Y... ¡sí! Actualmente devuelve un código de estado 200.

## Hola Extensiones de elementos de consulta

¿Cómo arreglamos esto? Bueno... al igual que hay un`QueryCollectionExtensionInterface` para el punto final de la colección, también hay un`QueryItemExtensionInterface` que se utiliza siempre que la API Platform consulta un único elemento.

Puedes crear una clase totalmente independiente para esto... pero también puedes combinarlas. Añade una segunda interfaz para `QueryItemExtensionInterface`. A continuación, desplázate hacia abajo y ve a "Código"->"Generar" -o `Command`+`N` en un Mac- para añadir el único método que nos falta: `applyToItem()`:

[[[ code('a9ee1338af') ]]]

Sí, es casi idéntico al método de la colección .... funciona de la misma manera... ¡e incluso necesitamos la misma lógica! Así que, copia el código que necesitamos, luego ve al menú Refactorizar y di "Refactorizar esto", que también es `Control`+`T` en un Mac. Selecciona extraer esto a un método... y llámalo `addIsPublishedWhere()`:

[[[ code('9397761819') ]]]

¡Genial! Limpiaré las cosas... y, ¿sabes qué? Debería haber añadido también esta declaración`if` ahí dentro. Así que vamos a mover eso:

[[[ code('5f93da2d6c') ]]]

Lo que significa que necesitamos un argumento `string $resourceClass`. Arriba, pasa`$resourceClass` al método:

[[[ code('3e2c1bbd9c') ]]]

¡Perfecto! Ahora, en `applyToItem()`, llama a ese mismo método:

[[[ code('6fafed8656') ]]]

Vale, ¡ya estamos listos! Prueba ahora el test:

```terminal-silent
symfony php bin/phpunit --filter=testGetOneUnpublishedTreasure404s
```

Y... ¡pasa!

## Arreglar nuestro conjunto de pruebas

Hemos estado retocando bastante nuestro código, así que ha llegado el momento de probarlo. Ejecuta todas las pruebas:

```terminal
symfony php bin/phpunit
```

Y... ¡ups! 3 fallos - todos procedentes de `DragonTreasureResourceTest`. El problema es que, cuando creamos tesoros en nuestras pruebas, no fuimos explícitos sobre si queríamos un tesoro publicado o no publicado... y ese valor se establece aleatoriamente en nuestra fábrica.

Para solucionarlo, podríamos ser explícitos controlando el campo `isPublished` cada vez que creamos un tesoro. O... podemos ser más perezosos y, en `DragonTreasureFactory`, establecer`isPublished` como verdadero por defecto:

[[[ code('0f5260ae7c') ]]]

Ahora, para que nuestros datos de fijación sigan siendo interesantes, cuando creemos los 40 tesoros de dragón, anulemos `isPublished` y añadamos manualmente algo de aleatoriedad: si un número aleatorio de 0 a 10 es mayor que 3, que se publique:

[[[ code('62a3502630') ]]]

Eso debería arreglar la mayoría de nuestras pruebas. Aunque busca `unpublished`. Ah sí, estamos probando que un admin puede `PATCH` para editar un tesoro. Creamos un`DragonTreasure` no publicado... sólo para poder afirmar que estaba en la respuesta. Cambiémoslo a `true` en ambos sitios:

[[[ code('f4fcf30cce') ]]]

Hay otra prueba similar: cambia aquí también `isPublished` por `true`:

[[[ code('f844fbec05') ]]]

Ahora prueba las pruebas:

```terminal-silent
symfony php bin/phpunit
```

## Permitir la actualización de un elemento no publicado

¡Están contentos! ¡Yo estoy contento! Bueno, sobre todo. Aún tenemos un problemilla. Busca la primera prueba de `PATCH`. Estamos creando un `DragonTreasure` publicado, actualizándolo... y funciona perfectamente. Copia este test entero... pégalo... pero borra la parte de abajo: sólo necesitamos la parte de arriba. Llama a este método `testPatchUnpublishedWorks()`... y asegúrate de que el `DragonTreasure` no está publicado:

[[[ code('697545bb53') ]]]

Piénsalo: si tengo un `DragonTreasure` con `isPublished` `false` , debería poder actualizarlo, ¿no? Este es mi tesoro... Yo lo creé y sigo trabajando en él. Queremos que se permita.

¿Lo estará? Probablemente puedes adivinarlo:

```terminal-silent
symfony php bin/phpunit --filter=testPatchUnpublishedWorks
```

¡No! ¡Obtendremos un 404! Esto es a la vez una característica... ¡y un "gotcha"! Cuando creamos un `QueryCollectionExtensionInterface`, sólo se utiliza para esta única ruta de recogida. Pero cuando creamos un `ItemExtensionInterface`, se utiliza siempre que obtenemos un único tesoro: incluso para las operaciones `Delete`, `Patch` y`Put`. Así que, cuando un propietario intenta `Patch` su propio `DragonTreasure`, gracias a nuestra extensión de consulta, no puede encontrarlo.

Esto tiene dos soluciones. En primer lugar, en `applyToItem()`, API Platform nos pasa el `$operation`. Así que podríamos utilizarlo para determinar si se trata de una operación `Get`,`Patch` o `Delete` y sólo aplicar la lógica para algunas de ellas.

Y... esto podría tener sentido. Al fin y al cabo, si se te permite editar o borrar un tesoro... eso significa que ya has pasado una comprobación de seguridad... así que no necesitamos necesariamente bloquear las cosas mediante esta extensión de consulta.

La otra solución es cambiar la consulta para permitir que los propietarios vean sus propios tesoros. Una cosa interesante de esta solución es que también permitirá que se devuelvan tesoros no publicados desde la ruta de recogida si el usuario actual es el propietario de ese tesoro.

Vamos a intentarlo. Añade el `public function __construct()`... y autocablea el increíble servicio `Security`:

[[[ code('81501ca583') ]]]

A continuación... la vida se complica un poco. Empieza con `$user = $this->security->getUser()`. Si tenemos un usuario, vamos a modificar el `QueryBuilder` de forma similar... pero ligeramente diferente. En realidad, déjame subir el `$rootAlias` por encima de mi sentencia if. Ahora, si el usuario está conectado, añade `OR %s.owner = :owner`... luego pasa otro `rootAlias`... seguido de `->setParameter('owner', $user)`.

En caso contrario, si no hay usuario, utiliza la consulta original. Y necesitamos el parámetro `isPublished`en ambos casos... así que mantenlo al final:

[[[ code('6dd8b1fc43') ]]]

¡Creo que me gusta! Veamos qué opina el test:

```terminal-silent
symfony php bin/phpunit --filter=testPatchUnpublishedWorks
```

¡También le gusta! De hecho, todas nuestras pruebas parecen contentas.

Ok equipo: tema final. Cuando obtenemos un recurso de `User`, devolvemos sus tesoros de dragón. ¿Esa colección incluye también tesoros inéditos? Ah... ¡sí! Hablemos de por qué y de cómo solucionarlo a continuación.
