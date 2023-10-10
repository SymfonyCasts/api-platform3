# Extensión de consulta: Autofiltrar una colección

Cuando obtenemos una colección de tesoros, actualmente devolvemos todos los tesoros, incluso los inéditos. Probablemente algunos de ellos sean inéditos. Añadimos un filtro para controlar esto... pero seamos sinceros, no es la mejor solución. En realidad, necesitamos no devolver tesoros inéditos en absoluto.

Busca la [Guía de actualización de la API Platform](https://api-platform.com/docs/core/upgrade-guide/#api-platform-2730)... y busca la palabra "estado" para encontrar una sección que habla de "proveedores" y "procesadores". Antes hemos hablado de los procesadores de estado, como el `PersistProcessor`de las operaciones `Put` y `Post`, que se encarga de guardar el artículo en la base de datos.

## Proveedores de estado

Pero cada operación también tiene algo llamado proveedor de estado. Éste es el responsable de cargar el objeto o colección de objetos. Por ejemplo, cuando hacemos una petición GET para un único elemento, el `ItemProvider` es el responsable de tomar el ID y consultar la base de datos. También hay un `CollectionProvider`para cargar una colección de elementos.

Así que si queremos ocultar automáticamente los tesoros no publicados, una opción sería decorar este `CollectionProvider`, de forma muy parecida a como hicimos con el `PersistProcessor`. Excepto... que eso no funcionará del todo. ¿Por qué? El `CollectionProvider` de Doctrine ejecuta la consulta y devuelve los resultados. Así que lo único que podríamos hacer es coger esos resultados... y luego ocultar los que no queramos. Eso... no es lo ideal para el rendimiento -imagínate cargar 50 tesoros y luego mostrar sólo 10- y confundiría la paginación. Lo que realmente queremos hacer es modificar la propia consulta: añadir un`WHERE isPublished = true`.

## Probar el comportamiento

Por suerte para nosotros, este `CollectionProvider` "proporciona" su propio punto de extensión que nos permite hacer exactamente eso.

Antes de meternos de lleno, actualicemos una prueba para mostrar el comportamiento que queremos. Busca`testGetCollectionOfTreasures()`. Toma el control de estos 5 tesoros y conviértelos todos en `isPublished => true`:

[[[ code('59f527158c') ]]]

porque ahora mismo, en `DragonTreasureFactory`, `isPublished` está configurado con un valor aleatorio:

[[[ code('abb96ff04e') ]]]

Luego añade uno más con `createOne()` y `isPublished` falsos:

[[[ code('02d071cd43') ]]]

¡Impresionante! Y aún queremos afirmar que esto devuelve sólo 5 elementos. Así que... asegurémonos de que falla:

```terminal-silent
symfony php bin/console phpunit --filter=testGetCollectionOfTreasures
```

Y... ¡sí! Devuelve 6 elementos.

## Extensiones de la consulta de colección

Bien, para modificar la consulta de una ruta de colección, vamos a crear algo llamado extensión de consulta. En cualquier lugar de `src/` - yo lo haré en el directorio `ApiPlatform/`- crea una nueva clase llamada `DragonTreasureIsPublishedExtension`. Haz que implemente `QueryCollectionExtensionInterface`, luego ve a "Código"->"Generar" o`Command`+`N` en un Mac - y genera el único método que necesitamos: `applyToCollection()`:

[[[ code('a1406d2b7a') ]]]

Esto está muy bien: nos pasa el `$queryBuilder` y algunos datos más. Luego, podemos modificar ese `QueryBuilder`. ¿Lo mejor? El `QueryBuilder`ya tiene en cuenta cosas como la paginación y cualquier filtro que se haya aplicado. Así que no tenemos que preocuparnos de esas cosas.

Además, gracias al sistema de autoconfiguración de Symfony, sólo con crear esta clase y hacer que implemente esta interfaz, ¡ya será llamada cada vez que se utilice una ruta de colección!

## Lógica de extensión de consulta

De hecho, se llamará para cualquier recurso. Así que lo primero que necesitamos es`if (DragonTreasure::class !== $resourceClass)` -afortunadamente nos pasa el nombre de la clase- y luego return:

[[[ code('077038260a') ]]]

A continuación, aquí es donde nos ponemos manos a la obra. Ahora, cada objeto `QueryBuilder` tiene un alias raíz que hace referencia a la clase o tabla que estás consultando. Normalmente, creamos el `QueryBuilder`... como desde dentro de un repositorio decimos algo como `$this->createQueryBuilder('d')` y `d` se convierte en ese "alias raíz". Luego lo utilizamos en otras partes de la consulta.

Sin embargo, en esta situación, no creamos el `QueryBuilder`, así que nunca elegimos ese alias raíz. Lo eligieron por nosotros. ¿Qué es? Es "plátano". En realidad, ¡no tengo ni idea de lo que es! Pero podemos conseguirlo con `$queryBuilder->getRootAliases()[0]`:

[[[ code('7071d70c4e') ]]]

Ahora es sólo lógica de consulta normal: `$queryBuilder->andWhere()` pasando `sprintf()`. Esto parece un poco raro: `%s.isPublished = :isPublished`, luego pasa `$rootAlias`seguido de `->setParameter('isPublished', true)`:

[[[ code('b31000a557') ]]]

¡Genial! ¡Gira para probar esto!

```terminal-silent
symfony php bin/console phpunit --filter=testGetCollectionOfTreasures
```

¡Misión cumplida! Así de fácil.

## ¿Extensiones de consulta en los Subrecursos?

Por cierto, ¿funcionará también con los subrecursos? Por ejemplo, en nuestros documentos, también podemos obtener una colección de tesoros visitando`/api/users/{user_id}/treasures`. ¿Esto también ocultará los tesoros no publicados? La respuesta es... ¡sí! Así que no es algo de lo que debas preocuparte. No lo mostraré, pero esto también utiliza la extensión de consulta.

Ah, y si quieres que los usuarios administradores puedan ver los tesoros no publicados, puedes añadir una lógica que sólo modifique esta consulta si el usuario actual no es administrador.

A continuación: ¡esta extensión de consulta arregla la ruta de recogida! Pero... alguien aún podría obtener un único tesoro no publicado directamente por su id. ¡Vamos a arreglarlo!
