# Proveedores de estado, procesadores y un campo personalizado

La API Platform 3 introdujo nuevos y atractivos conceptos llamados Proveedores de Estado y Procesadores de Estado. Hablamos de ellos en el último tutorial y vamos a profundizar aún más en este tutorial.

## Conceptos básicos sobre proveedores y procesadores

En la "Guía de actualización" de la documentación de API Platform hay una de mis secciones favoritas sobre este tema. Cada clase de recurso API -ya sea una entidad o una clase normal- tendrá un Proveedor de Estado. Su trabajo consiste en cargar los datos, por ejemplo, de la base de datos... o de donde sea. Cada clase de recurso API tendrá también un Procesador de Estado, cuyo trabajo es guardar los datos, como en una petición POST o PATCH. También se encarga de borrarlos.

La gran ventaja es que si tu recurso API es una entidad, obtendrás automáticamente un conjunto de Proveedores de Estado y Procesadores de Estado. Por ejemplo, la operación `GetCollection` utiliza un núcleo `CollectionProvider`, que consulta la base de datos por ti. Y hay un `ItemProvider` similar para obtener un elemento de la base de datos.

Las entidades también tienen un complemento `PersistProcessor`, que, sin sorpresa, persiste tus datos en la base de datos.

En el Episodio 2, decoramos el `PersistProcessor` para la entidad `User`. Esto nos permitió hacer un hash de la contraseña simple aquí arriba... antes de llamar al núcleo`PersistProcessor` para que se encargue de guardarla.

[[[ code('7c2bfb64af') ]]]

## Formas buenas y mejores de añadir un campo personalizado

Hablamos de esto porque podemos utilizar un truco similar con el proveedor de estado para añadir un campo personalizado: un campo que quieres en tu API, pero que no vive en la base de datos.

En el último episodio, aprendimos que una forma de añadir un campo personalizado es ampliando el normalizador. Lo hicimos en `AddOwnerGroupsNormalizer`. Bien, esto hace unas cuantas cosas, pero lo más importante para nosotros: si el objeto es un `DragonTreasure` -por tanto, si un `DragonTreasure` se está convirtiendo en JSON- y el usuario autenticado en ese momento es el propietario de ese tesoro, entonces añade un campo `isMine` totalmente personalizado.

[[[ code('100447ac31') ]]]

Podemos verlo en nuestras pruebas:`tests/Functional/DragonTreasureResourceTest.php`Busca `isMine`. Sí: `testOwnerCanSeeIsPublishedAndIsMineFields`. La parte importante es la de abajo: cuando se serializa el tesoro, `isMine` debe estar en la respuesta.

[[[ code('ecd7a98abf') ]]]

Esto funciona de maravilla... excepto por un contratiempo: en la documentación... ¡no se menciona el campo `isMine`! Se devolverá, pero no está documentado.

Si esto te importa, hay dos formas mejores de manejarlo: añade un campo no persistente a tu entidad -eso es lo que haremos dentro de un momento- o crea una clase de recurso API totalmente personalizada. Ese será nuestro gran tema más adelante.

## Añadir el campo no persistente

Paso 1: elimina el código del normalizador... y sólo vuelve. Copia el nombre del método de prueba... para asegurarte de que esto falla:

[[[ code('fdcf1cec0c') ]]]

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

Y... ¡yay fallo! Esperaba que `null` fuera el mismo que `true` de la línea 215... ¡porque ya no existe el campo `isMine`!

Paso 2: añade este campo como una propiedad real en nuestra clase: ¿qué te parece`private bool $isOwnedByAuthenticatedUser`. Fíjate en que se trata de una propiedad no persistente: sólo existe para ayudar a nuestra API. Hacer esto no es supercomún, pero está permitido. Ve hasta el final para añadir un getter y un setter.

[[[ code('582f4535f5') ]]]

Ah, y como la propiedad no tiene un valor por defecto, si la propiedad no se ha inicializado, gritemos para saberlo.

[[[ code('1adaa2ee20') ]]]

Por último, pero no menos importante, tenemos que exponer esta propiedad a nuestra API. Hazlo poniéndola en el grupo llamado `treasure:read`... y luego utiliza `SerializedName` para llamarla `isMine` en la API.

[[[ code('81fc26d0f8') ]]]

Si ahora vamos a ejecutar la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

¡Nos recibe un delicioso error 500! Gracias a la biblioteca `zenstruck/browser`, guardó esa respuesta fallida en un archivo... que podemos abrir en nuestro navegador. Y... ¡sí!

> Debes llamar a setIsOwnedByAuthenticatedUser()

Así que está intentando exponer el campo a nuestra API... pero nada está estableciendo esa propiedad. ¿Cómo la estableceremos? ¡Con una actitud positiva! Y... sobre todo con un proveedor de estado personalizado. Eso a continuación.
