# Entidad -> DTO Proveedor de estado del elemento

¿Qué pasa con la ruta del elemento? Si vamos a `/api/users/6.jsonld`... parece que funciona... ¡pero es una trampa! Es sólo el formato de colección... ¡con un único elemento!

Sabemos que hay dos proveedores principales: `CollectionProvider` y un proveedor de ítems, cuyo trabajo es devolver un ítem o null. Como hemos puesto `provider` en `EntityToDtoStateProvider`, está utilizando este `provider`para cada operación. Y eso está bien... siempre que lo hagamos lo suficientemente inteligente como para manejar ambos casos.

Ya vimos antes cómo hacerlo: `$operation` es la clave. Añade`if ($operation instanceof CollectionOperationInterface)`. Ahora podemos deformar todo este código aquí arriba. ¡Precioso!

[[[ code('bdbdef05a3') ]]]

A continuación, éste será nuestro proveedor de artículos. `dd($uriVariables)`.

[[[ code('bfeae4c31f') ]]]

## Llamada al proveedor de elementos del núcleo

Cuando probamos la operación elemento... ¡bien! Esto es lo que esperamos ver: el valor `id`, que es la parte dinámica de la ruta.

Al igual que con el proveedor de colecciones, no queremos hacer el trabajo de consulta manualmente. En su lugar, vamos a... "delegaremos" en el proveedor de elementos del núcleo de Doctrine. Añadiremos un segundo argumento... podemos simplemente copiar el primero... de tipo `ItemProvider`(el de Doctrine ORM), y lo llamaremos `$itemProvider`.

[[[ code('fc94d77867') ]]]

¡Me gusta! De vuelta abajo, deja que haga el trabajo con`$entity = $this->itemProvider->provide()` pasando `$operation`, `$uriVariables`y `$context`.

[[[ code('39921ce749') ]]]

Esto nos dará un objeto `$entity` o null. Si no tenemos un objeto `$entity`,`return null`. Esto provocará un 404. Pero si tenemos un objeto `$entity`, no queremos devolverlo directamente. Recuerda que el objetivo de esta clase es tomar el objeto `$entity` y transformarlo en un DTO `UserApi`.

Así que, en su lugar, `return $this->mapEntityToDto($entity)`.

[[[ code('901c9e27e4') ]]]

Así queda bien. Y... la ruta final funciona de maravilla. Si intentamos un identificador no válido, nuestro proveedor devuelve null y API Platform se encarga del 404.

## Mostrar sólo los Tesoros del Dragón publicados

Nota al margen: si sigues algunos de estos tesoros relacionados, también pueden 404. Veamos... tenemos 21 y 27. el 21 me funciona... y para el 27... también funciona... por supuesto. De todos modos, la razón por la que algunos podrían 404 es que, ahora mismo, si vuelvo atrás, la propiedad `dragonTreasures` incluye todos los tesoros relacionados con este usuario: incluso los no publicados. Pero en un tutorial anterior, creamos una extensión de consulta que impedía que se cargaran los tesoros no publicados.

Cuando la entidad `User` era nuestro recurso API, evitábamos devolver tesoros no publicados desde esta propiedad. Creamos `getPublishedDragonTreasures()` y la convertimos en la propiedad `dragonTreasures`.

Pero en nuestro proveedor de estado, los estamos estableciendo todos. Esto tiene fácil arreglo: cambia a `getPublishedDragonTreasures()`. 

[[[ code('84586ed7d2') ]]]

En realidad, deshazlo... y luego actualiza la ruta de la colección. Vale, aquí abajo vemos los tesoros 16 y 40... después de utilizar el nuevo método... ¡sólo 16! "40" está sin publicar.

¡Ha sido fácil! Y pone de relieve algo genial. Para tener un campo`dragonTreasures` que devolviera algo especial cuando nuestra entidad `User` fuera un ApiResource, necesitábamos un método dedicado y un atributo `SerializedName`. Pero con una clase personalizada, no necesitamos ninguna rareza. Podemos hacer lo que queramos en el proveedor de estado. ¡Nuestras clases se mantienen brillantes y limpias!

A continuación: Hagamos que nuestros usuarios se guarden con un procesador de estado: un delicado baile que implica manejar usuarios nuevos y existentes.
