# Proveedor: Transformar Entidades en DTOs

No perdamos de vista el objetivo. Cuando utilizamos por primera vez `stateOptions`, provocó que se utilizara el proveedor central de la colección Doctrine. Eso está muy bien... salvo que devuelve entidades `User`, lo que significa que éstas se convirtieron en los objetos centrales de las rutas `UserApi`. Eso provoca una grave limitación a la hora de serializar: nuestras propiedades `UserApi` tienen que coincidir con nuestras propiedades `User`... de lo contrario, el serializador explota.

Para solucionarlo y darnos el control total, hemos creado nuestro propio proveedor de estado que llama al proveedor de colección central. Pero en lugar de devolver estos objetos de entidad `User`, vamos a devolver objetos `UserApi` para que se conviertan en los objetos centrales y se serialicen normalmente.

## Mapeo al DTO

Crea un array `$dtos` y `foreach` sobre `$entities as $entity`. A continuación, añade al array `$dtos` llamando a un nuevo método: `mapEntityToDto($entity)`.

[[[ code('610b7a07dc') ]]]

Pulsa "alt" + "enter" para añadir ese método al final. Esto devolverá un `object`. Bueno... será un objeto `UserApi`... pero estamos intentando mantener esta clase genérica. Voy a pegar algo de lógica -puedes copiarla del bloque de código de esta página- y luego pulsar "alt" + "enter" para añadir la declaración `use` que falta. Este código es específico del usuario... pero lo haremos más genérico más adelante, para que podamos reutilizar esta clase para los tesoros del dragón.

[[[ code('b5a06d6bc9') ]]]

Pero, ¿no es este código refrescantemente aburrido y comprensible? Sólo transferir propiedades del `User` `$entity` ... al DTO. Lo único un poco extravagante es donde cambiamos esta colección por una matriz... porque esta propiedad es una `array` en `UserApi`.

Por último, en la parte inferior de `provide()`, `return $dtos`.

[[[ code('9a93af1586') ]]]

Gracias a esto, los objetos centrales serán objetos `UserApi`... y éstos se serializarán normalmente: nada de fantasías donde el serializador intenta pasar de una entidad`User` a una `UserApi`.

¡Drumoll, por favor! ¡Tada! Funciona... ¡con el mismo resultado que antes! Pero ahora tenemos la posibilidad de añadir propiedades personalizadas.

## Añadir propiedades personalizadas

Vuelve a añadir `public int $flameThrowingDistance`. 

[[[ code('046822b3f9') ]]]

Luego, en el proveedor, es donde tenemos la oportunidad de establecer esas propiedades personalizadas, como`$dto->flameThrowingDistance = rand(1, 10)`.

[[[ code('87506a00cb') ]]]

Y... ¡voilà! ¡Ahora sí que somos jodidamente peligrosos! Estamos reutilizando el núcleo de Doctrine `CollectionProvider`, pero con la posibilidad de añadir campos personalizados. Ah, y me olvidé de mencionarlo: los campos JSON-LD `@id` y `@type` están de vuelta. ¡Lo hemos conseguido!

## Arreglar la paginación

Aunque, parece que ahora nos falta la paginación. El filtro está documentado... ¡pero el campo `hydra:view` que documenta la paginación ha desaparecido! Vale, en realidad, la paginación sigue funcionando. Observa: si voy a `?page=2`, el primer usuario "usuario 1"... se convierte en "usuario 6". Sí, internamente, el núcleo `CollectionProvider` de Doctrine sigue leyendo la página actual y buscando el conjunto correcto de objetos para esa página. Nos falta el campo `hdra:view` de la parte inferior que describe la paginación simplemente porque ya no devolvemos un objeto que implemente`PaginationInterface`.

Recuerda que esta variable `$entities` es en realidad un objeto `Pagination`. Ahora que sólo devolvemos una matriz, la API Platform piensa que no admitimos la paginación.

La solución es muy sencilla. En lugar de devolver `$dtos`,`return new TraversablePaginator()` con un nuevo `\ArrayIterator()` de `$dtos`. Para los demás argumentos, podemos coger los del paginador original. Como ayuda,`assert($entities instanceof Paginator)` (el de Doctrine ORM). Luego, aquí abajo, utiliza `$entities->getCurrentPage()`, `$entities->getItemsPerPage()`, y`$entities->getTotalItems()`.

[[[ code('bdbdef05a3') ]]]

El proveedor de la colección principal ya ha hecho todo ese trabajo duro por nosotros. Qué amigo, actualiza ahora. Los resultados no cambian... pero aquí abajo, ¡ha vuelto `hydra:view`!

Siguiente: Hagamos que esto funcione para nuestras operaciones de artículos, como `GET` uno o `PATCH`. También aprovecharemos nuestro nuevo sistema para añadir algo a `UserApi` que antes teníamos.... pero esta vez, lo haremos de una forma mucho más chula.
