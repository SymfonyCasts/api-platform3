# Proveedor y procesador reutilizable Entidad->Dto

¡Nuestro `UserAPI` es ahora una clase de recursos API totalmente funcional! Tenemos nuestro`EntityToDtoStateProvider`, que llama al proveedor de estado central de Doctrine, y que nos proporciona todo lo bueno, como la consulta, el filtrado y la paginación. Luego, aquí abajo, aprovechamos el sistema MicroMapper para convertir los objetos `$entity` en objetos`UserApi`.

Y hacemos lo mismo en el procesador. Utilizamos MicroMapper para pasar de`UserApi` a nuestra entidad `User`... y luego llamamos al procesador de estado central de Doctrine para que se encargue de guardar o borrar. ¡Me encanta!

Nuestro sueño es crear un `DragonTreasureApi` y repetir toda esta magia. Y si podemos hacer que estas clases de procesador y proveedor sean completamente genéricas... eso va a ser superfácil. Así que ¡hagámoslo!

## Hacer genérico al proveedor

Empieza en el proveedor. Si buscas "usuario", sólo hay un lugar: donde le decimos a MicroMapper en qué clase convertir nuestro `$entity`. ¿Podemos... obtener esto dinámicamente? Aquí arriba, nuestro proveedor recibe el `$operation` y el `$context`. Volquemos ambos.

Como esto está en nuestro proveedor... podemos ir a actualizar la ruta Colección y... ¡boom! Se trata de una operación `GetCollection`... y compruébalo. El objeto de la operación almacena la clase ApiResource a la que está asociado

Así que por aquí, es sencillo: `$resourceClass = $operation->getClass()`. Ahora que tenemos eso, aquí abajo, conviértelo en un argumento - `string $resourceClass` - y pásalo en su lugar. Por último, tenemos que añadir `$resourceClass` como argumento cuando llamemos a `mapEntityToDto()` ahí... y justo ahí. Elimina la declaración `use` que ya no necesitamos y... así de fácil... ¡sigue funcionando!

## Hacer que el procesador sea genérico

¡Ya estamos en marcha! Dirígete al procesador y busca "usuario". Ah, tenemos el mismo problema excepto que, esta vez, necesitamos la clase de entidad `User`.

¡Vale! Arriba, `dd($operation)`. Y para ello, necesitamos ejecutar una de nuestras pruebas:

```terminal
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡ya está! Vemos la operación `Post`... y la clase es, por supuesto,`UserApi`. Pero esta vez necesitamos la clase `User`. Recuerda: en `UserApi`, utilizamos `stateOptions` para decir que `UserApi` está vinculada a la entidad `User`. Y ahora, podemos leer esta información de la operación. Si nos desplazamos un poco hacia abajo... ahí está: la propiedad `stateOptions` con el objeto `Options`, y `entityClass` dentro.

¡Genial! De vuelta al procesador, hacia arriba... quita el `dd()` y empieza por `$stateOptions = $operation->getStateOptions()`. Luego, para ayudar a mi editor (y también por si configuro algo mal), `assert($stateOptions instanceof Options)`(el del ORM Doctrine).

Puedes utilizar diferentes clases `Options` para `$stateOptions`... como si estuvieras obteniendo datos de ElasticSearch, pero sabemos que estamos utilizando esta de Doctrine. A continuación, digamos `$entityClass = $stateOptions->getEntityClass()`.

Y... no necesitamos este `assert()` de aquí abajo, entonces pasa `$entityClass` a`mapDtoToEntity()`. Por último, úsalo con `string $entityClass`... y pásalo también aquí.

Cuando ahora busquemos "usuario"... podemos deshacernos de las dos declaraciones `use`... y... ¡ya estamos limpios! ¡Es genérico! ¡Prueba el test!

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Ya está! ¡Estamos listos! ¡Tenemos un proveedor y un procesador reutilizables! A continuación, creemos una clase `DragonTreasureApi`, repitamos esta magia, ¡y veamos lo rápido que conseguimos que las cosas encajen!
