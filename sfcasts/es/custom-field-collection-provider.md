# Decorar el CollectionProvider

Vamos a hacer con valentía algo que nos asusta a la mayoría de los desarrolladores: ejecutar todo el conjunto de pruebas:

```terminal
symfony php bin/phpunit
```

Éstas pasaban obedientemente cuando empecé el tutorial... ¡pero han decidido rebelarse! Abramos la respuesta fallida. Hmm:

> Se encontró más de un resultado para la consulta, aunque se esperaba una fila o ninguna.

Si ves el código fuente de la página, esto proviene de Doctrine... y, finalmente, del núcleo `ItemProvider` al que estamos llamando. Volviendo a los documentos, la operación `GetCollection`-que es la que se utiliza en esta prueba- tiene un proveedor diferente: `CollectionProvider`.

Por desgracia, cuando establezco `provider` dentro del atributo `#[ApiResource]`... eso establece el proveedor para cada operación. Es posible establecer el `provider`para una operación específica... así. Pero... Me gusta tener un único proveedor para todo mi recurso API: es más sencillo.

Para ello, sólo tenemos que darnos cuenta de que se llamará a este proveedor tanto cuando se obtenga un único elemento como cuando se obtenga una colección de elementos. En esta prueba, se llama a nuestro proveedor para obtener una colección... luego llamamos al proveedor de elementos... y ocurren cosas raras.

`dd()` el `$operation` de nuevo... 

[[[ code('f9708d25c1') ]]]

luego copia el nombre de la prueba que falla... y ejecuta sólo esa:

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

¡Excelente! Un objeto `GetCollection`. ¡Podemos utilizarlo para averiguar qué proveedor necesitamos!

Vamos a inyectar el núcleo `CollectionProvider`. Copia el primer argumento, duplícalo y configúralo para que utilice el servicio `CollectionProvider` de ORM. Ponle el nombre `$collectionProvider`.

[[[ code('9162c8ed10') ]]]

A continuación, comprueba si `$operation` es una instancia de `CollectionOperationInterface`. Vale, en realidad, sólo una operación - `GetCollection` - utiliza el proveedor de colecciones... pero en caso de que se añadiera una operación personalizada, cualquier cosa que necesite una colección implementará esta interfaz. En esta situación, devuelve `$this->collectionProvider->provide()`y pasa los args. Y... ¡no olvides el nombre del método!

[[[ code('c723018b0c') ]]]

¡Muy bien! Gira o ejecuta de nuevo la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Y... sigue explotando. Algo sobre que se espera que null sea igual que 5. 
Comprueba la respuesta. ¡Ah! ¡Otra vez nuestro error! Para la operación elemento, estamos estableciendo esa propiedad. Ahora tenemos que hacer lo mismo aquí: hacer un bucle sobre cada tesoro y establecerlo.

## El objeto paginador

Pero antes, ¿qué devuelve el proveedor de la colección: una matriz de tesoros? Copia toda la llamada, `dd()`... y vuelve a ejecutar la prueba:

[[[ code('e1d125a94f') ]]]

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Veamos... ¡es un objeto `Paginator`! Eso es importante: es lo que alimenta la paginación de nuestras rutas de recolección. Vale, en realidad no es tan importante ahora mismo -podemos hacer un bucle sobre este objeto para obtener cada `DragonTreasure` -, pero volveremos a ello más adelante, cuando creemos un recurso personalizado.

Elimina el `dd()` y, en lugar del retorno, di igual a `$paginator`. Ayudaré a mi editor diciendo que se trata de un `iterable` de `DragonTreasure`. Ahora,`foreach` `$paginator` como `$treasure`... y luego robaré el código de abajo... y lo pegaré.

Ahora que hemos modificado cada elemento, `return $paginator`.

[[[ code('837b8734ae') ]]]

Vamos a intentarlo de nuevo

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Vuelve a fallar... pero justo al final: `DragonTreasureResourceTest` línea 37. Vamos a comprobarlo. Así que hasta aquí, creamos algunos tesoros, hacemos una petición `->get()`a la ruta de la colección, verificamos algunas cosas, y luego, abajo, cogemos el primer elemento y comprobamos que tiene los campos correctos. Al parecer, la propiedad `isMine` está ahí... ¿pero no se esperaba?

Es culpa mía. En una aventura anterior, cuando añadimos la propiedad `isMine`, sólo la añadimos cuando era `true`. Si un `DragonTreasure` no me pertenecía, el campo no estaba ahí en absoluto... y probablemente debería haberlo estado. Así que vamos a actualizar la prueba. Y ahora... ¡está verde!

[[[ code('b36aeb94c9') ]]]

Vuelve a ejecutarlo todo

```terminal-silent
symfony php bin/phpunit
```

## POST: No State Provider

Uhhh. hasta un fallo: `testPostToCreateTreasure` - con un error 500. Abre eso en nuestro navegador. ¡Bah! Es nuestro:

> Debes llamar a `setIsOwnedByAuthenticatedUser()`.

Pero, ¿cómo es posible? No importa, ¡estamos estableciendo ese valor dentro de nuestro proveedor de estado! Sin embargo... la operación `POST` es única: es la única operación que no utiliza un proveedor. Vale, `Delete` no muestra un proveedor, pero utiliza `ItemProvider` para cargar el único elemento que va a eliminar.

Para `Post`, el JSON se deserializa directamente en un `TreasureEntity`.. y luego se guarda. El proveedor de estado nunca se necesita ni se utiliza ...., lo que significa que cuando se serializa a JSON, esa propiedad sigue sin establecerse.

La solución está en el procesador de estado para `DragonTreasure`: justo antes o después de guardar, tenemos que ejecutar esta misma lógica. Entendido. Ya tenemos un procesador de estado para `DragonTreasure`. Está pensado para establecer el propietario si no está establecido... pero vamos a secuestrarlo para esto. Justo después de guardar, pega esto. Pero la forma en que creamos esto en el episodio anterior significa que se llama para cada ApiResource. Así que necesitamos la misma sentencia if de aquí arriba: si `$data` es un `instanceof``DragonTreasure` , entonces establece esa propiedad. Voy a... actualizar un par de variables.

[[[ code('91cae81e5b') ]]]

Así, el objeto se guarda, establecemos la propiedad... y luego se serializa a JSON. Prueba de nuevo esas pruebas:

```terminal-silent
symfony php bin/phpunit
```

¡Todo verde! ¡Guau! Así que ya sabemos que podemos ejecutar código antes o después de que un elemento se guarde teniendo un procesador de estado personalizado. Pero, ¿y si necesitamos ejecutar código sólo cuando cambia algo concreto? Como cuando un `DragonTreasure` cambia de no publicado a publicado. Nos ocuparemos de ello a continuación, empezando por simplificar un poco nuestro procesador de estado.
