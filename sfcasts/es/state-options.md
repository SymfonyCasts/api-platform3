# stateOptions + entityClass Magia

Cuando creamos un recurso API que no es una entidad, somos responsables de cargar y guardar los datos. Lo frustrante es que, si creamos un proveedor de estado personalizado para`UserApi`, hará exactamente lo mismo que el proveedor de estado básico de Doctrine: consultar la base de datos. Históricamente, éste ha sido el talón de Aquiles de los DTO.

## Comprobando el Core CollectionProvider

Abre el núcleo `CollectionProvider` de Doctrine ORM. Si alguna vez has querido ver cómo es el `CollectionProvider`, ¡aquí lo tienes! Es más complejo de lo que imaginaba. Crea el `QueryBuilder`, llama a `handleLinks()` (que une inteligentemente a otras tablas en función de los datos que necesites), y alberga el sistema de extensión de consultas. En el último tutorial, creamos una extensión de consulta para `DragonTreasure` de modo que sólo devolviera elementos publicados. Y parte de ese sistema de extensión, aunque no podamos verlo aquí, es donde se añade la paginación y el filtrado.

Así que esta clase nos da mucho... y quiero reutilizarla. Así que, maldita sea, ¡vamos a yolo esta cosa y a intentarlo!

## Intentando utilizar el CollectionProvider

Dirígete a `UserApi`, di `provider`, y apunta a `CollectionProvider`(el de Doctrine ORM).

[[[ code('a16d4e789d') ]]]

¡A ver qué pasa! En el navegador, ve directamente a la ruta -`/api/users.jsonld`. Y... obtenemos un error

> Llamada a una función miembro `getRepository()` en null.

Procedente del núcleo `CollectionProvider`. Buf. Pero no es sorprendente. Nuestro `UserApi`no es una entidad... y por eso, cuando intenta averiguar cómo consultarlo, ¡explosiones!

## Hola stateOptions + entityClas

Pero psst... ¿quieres oír un secreto? Hay una forma de indicar al proveedor que los datos de esta clase deben proceder de la entidad `User`. Se parece a esto:`stateOptions` ponlo en un objeto `new Options` (asegurándote de coger el del ORM), y dentro, `entityClass: User::class`.

[[[ code('da8ec4d921') ]]]

¡Veamos qué ocurre ahora! Cuando nos dirigimos y actualizamos... ¡vaya! ¡Parece que ha funcionado! Vemos "totalItems: 11"... con los elementos 1-11 todos aquí. Sólo tenemos una propiedad `$id`, pero supongo que tiene sentido... ya que sólo tenemos una propiedad `$id`dentro de nuestra `UserApi`.

¡Añadamos algunas propiedades más! ¿Qué te parecen `public ?string $email = null`y `public ?string $username = null`. Estas dos propiedades también viven en nuestra entidad `User`.

[[[ code('ba9416335d') ]]]

Cuando actualizamos... ¡también aparecen! Esto funciona.... pero ¿cómo? ¿Qué demonios está pasando?

## Cómo funciona todo esto

Si pudiéramos echar un vistazo bajo el capó de API Platform, veríamos que los objetos de recursos API subyacentes son `UserApi`. Así que lo que vemos aquí es el JSON de una colección de objetos `UserApi`.

Pero hay varios lugares en el sistema que buscan `stateOptions` y, si está presente, utilizarán el `entityClass` de ahí. El `CollectionProvider` que hemos abierto hace un momento -el de Doctrine ORM- es uno de esos casos. Coge el `entityClass` de `stateOptions` si lo hay... y lo utiliza cuando hace la consulta.

De hecho, en cuanto tenemos esto de `stateOptions` + `entityClass`, API Platform establece el proveedor y el procesador automáticamente en los del núcleo de Doctrine. Así que ni siquiera necesitamos tener la clave `provider`: está establecida por nosotros.

[[[ code('f8615b323f') ]]]

Vale, pero si el proveedor está consultando objetos de entidad `User`, ¿cómo y cuándo se convierten en objetos `UserApi`... para poder serializarlos a JSON? La respuesta es durante la serialización... y es un poco raro. Gracias a`stateOptions`, API Platform está serializando en realidad el objeto entidad `User`. Pero para obtener la lista de las propiedades que debe serializar, lee los metadatos de `UserApi`. Luego, coge los valores de las propiedades de `User`... y los pone en una instancia de `UserApi`. Esencialmente, serializa la entidad `User` en un objeto `UserApi`... y luego a JSON.

Esto parece funcionar bien... pero con una limitación importante.

## Limitación: No hay propiedades personalizadas

Añade una propiedad que no esté en nuestra entidad, como`public int $flameThrowingDistance = 0`. No hay ninguna propiedad `$flameThrowingDistance`sobre `User`.

[[[ code('5e00b6d3ca') ]]]

Cuando probamos esto... ¡explosión! Si nos desplazamos un poco hacia abajo, vemos que esto procede del sistema normalizador... que forma parte del serializador. Mira `UserApi`, piensa "Oh, necesito un campo `$flameThrowingDistance` ", intenta obtenerlo de `User`, y, como no está ahí, ¡boom!

Así que la colosal, monstruosa, titánica limitación de la estrategia `entityClass` es... que no podemos tener campos adicionales en nuestra clase `UserApi`. Pero no te preocupes: encontraremos una forma de evitarlo en el próximo capítulo. Por ahora, elimina la propiedad extra.

Ah, y otra limitación que habrás notado es que no tenemos los campos JSON-LD `@id` o `@type`. Nos ocuparemos de ello mientras solucionamos el problema de los campos personalizados... como magos multitarea que somos.

## Añadir una propiedad de relación

Añadamos otra propiedad: `public array $dragonTreasures = []`? Tenemos una propiedad `$dragonTreasures` en `User` que contiene una colección de objetos`DragonTreasure`.

[[[ code('e24167298c') ]]]

Así que si vamos y probamos esto... ¡funciona bien! Aunque, sorprendentemente, está incrustando los `dragonTreasures` en lugar de devolverlos como IRI. Este es el mismo problema que vimos antes, y la solución es la misma.

Sin embargo, quiero señalar algo interesante. Cuando incrusta el `dragonTreasures`, una de las propiedades es `owner`. Ahora mismo, ese propietario es en realidad la entidad `User`. Como la entidad `User` ya no es un recurso de la API, añade esta cosa aleatoria `genid`.

Hablaré más de esto dentro de un rato, pero una vez que empecemos a crear DTOs y a utilizarlos en lugar de entidades, probablemente querremos utilizar DTOs para todos nuestros recursos API... en lugar de mezclar entidades y DTOs... porque crea problemas como éste.

De todos modos, arregla esto anunciando que se trata de un `array` de `DragonTreasure`. Estoy utilizando una sintaxis de matriz ligeramente diferente, pero en realidad no importa.

[[[ code('3b79478941') ]]]

Si volvemos a intentarlo... ¡volveremos a las IRI! ¡Guau!

## Paginación incorporada

Hasta ahora, sabemos que `stateOptions` hace tres cosas. Una: configura automáticamente el proveedor y el procesador para que utilicen el proveedor y el procesador del núcleo de Doctrine. Dos: el proveedor es lo suficientemente inteligente como para realizar consultas a partir de esta entidad. Esto también funciona para elementos individuales, como `/users/1.jsonld`. Y tres: El serializador serializa la entidad `User` en un objeto `UserApi`.

El hecho de que `stateOptions` haga que se utilice el proveedor de estado básico de Doctrine tiene otros efectos secundarios muy importantes. En primer lugar, obtenemos paginación gratis. Añade`paginationItemsPerPage: 5`, repasa y actualiza. Vemos que el número total de elementos es "11"... pero sólo muestra cinco... y las páginas están aquí abajo.

[[[ code('ed5ccf46b5') ]]]

En segundo lugar, el proveedor de colecciones también hace funcionar el sistema de extensión de consultas. No tenemos ninguna extensión de consulta para `User`, pero sí para `DragonTreasure`. Más adelante, cuando convirtamos `DragonTreasure` en su propia clase DTO, esta extensión seguirá funcionando.

La tercera y última golosina es que ¡el sistema de filtros sigue funcionando! Observa: encima de `UserApi`, añade `#[ApiFilter()]` con `SearchFilter::class` y `properties:`con `username` ajustado a `partial`.

[[[ code('e58d562495') ]]]

Vuelve a mirar la documentación... ups. Autocompleté el`SearchFilter` desde ODM. Bórralo y pulsa Alt+Enter para coger el de `ORM`.

Vuelve a actualizar la documentación... y mira la ruta `/api/users`. Está anunciando que hay un filtro `username`, ¡y va a funcionar! En la otra pestaña, añade `?username=Clumsy`.

Y... ¡sí! ¡Sólo devuelve esos 5 resultados! Así que el sistema de filtros funciona! Aunque, una cosa a tener en cuenta es que, cuando decimos `username`, nos estamos refiriendo a la propiedad`$username` de la entidad `User`. En lo que respecta al filtro, ni siquiera necesitamos un `username` en `UserApi`.

Así que: estamos reutilizando toda esta lógica central del proveedor Doctrine, tenemos paginación, filtros y.... es lo mejor desde los sándwiches de helado. Excepto... por esa gran y aterradora limitación: que nuestro DTO no puede tener campos personalizados. Y... ése es realmente el objetivo de un DTO: obtener la flexibilidad de tener campos diferentes a los de tu entidad. Así que vamos a ver cómo solucionar esa limitación a continuación.
