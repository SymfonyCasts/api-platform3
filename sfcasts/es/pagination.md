# Paginación en un Recurso Personalizado

Cuando obtenemos la colección de búsquedas, ¡vemos las 50! No hay paginación... un hecho que puedo probar porque, en la parte inferior no vemos ningún dato extra sobre la paginación.

Normalmente... si echamos un vistazo a la colección de tesoros... en la parte inferior de la respuesta, API Platform añade un campo `hydra:view` que describe cómo puedes paginar a través de estos recursos. Pero aquí para las búsquedas... ¡nada!

## La paginación viene del proveedor

Pero, ¿de dónde viene la paginación en API Platform? Resulta que la paginación es completamente responsabilidad de tu proveedor de estado. Es... bastante sencillo en realidad. Lo que devuelva tu proveedor de colecciones -ya sea una matriz de búsquedas... o algún tipo de iterable de búsquedas- es lo que se serializa en JSON. Pero si devuelve un objeto iterable que implementa un `PaginatorInterface` especial, la API Platform lo verá y mostrará los detalles de paginación de `hydra:view`.

## Utilizar el paginador TraversablePaginator

Así que, si queremos que nuestra colección admita la paginación, el primer paso es, en lugar de devolver esta matriz, devolver un objeto que implemente esa interfaz. Y, afortunadamente, API Platform ya tiene una clase que puede ayudarnos

Establece la matriz en una variable `$quests`. A continuación, devuelve new `TraversablePaginator`desde la API Platform. Esto toma unos cuantos argumentos. En primer lugar, un traversable -básicamente los resultados que deberían mostrarse para la página actual. De momento, seguiremos utilizando las 50 búsquedas. Oh, excepto que esto tiene que ser un iterable... así que envuélvelo en un nuevo `ArrayIterator`.

Lo siguiente es la página actual, que por ahora se codifica en 1, luego los objetos por página, que se codifican en 10, y finalmente el número total de objetos, que por ahora contaré en `$quests`.

[[[ code('da08790a13') ]]]

Aún no es un paginador muy inteligente: siempre estará en la página 1 y mostrará todos los resultados. Pero cuando pasamos, actualizamos... y nos desplazamos hasta el final, ¡sí que vemos la información de la paginación! Según esto, hay 5 páginas de resultados... lo que tiene sentido: 10 elementos por página y 50 elementos en total. Pero también verás que seguimos devolviendo 50 elementos. ¡No hay paginación real!

¿Por qué? Porque depende de nosotros averiguar en qué página estamos y pasar sólo los resultados correctos al paginador. Si le pasamos 50 elementos, nos devolverá 50 elementos, independientemente de lo que le digamos que es el máximo por página.

## Organizar nuestras variables

Para ayudarnos a hacerlo, vamos a establecer unas cuantas variables: `$currentPage` codificada a 1,`$itemsPerPage` codificada a 10 y `$totalItems`. Para ello, llama a un nuevo método privado`countTotalQuests()`. 

[[[ code('facde9f673') ]]]

Pulsaré Alt+Enter y añadiré ese método al final. Esto devolverá un `int`... y yo sólo voy a devolver 50...

[[[ code('19cfced077') ]]]

porque ese es el total de búsquedas posibles que tenemos en nuestra base de datos "falsa". Si estuvieras utilizando una base de datos, contarías todas las filas disponibles. Cambia el código en `createQuests()`para utilizar esto.

Probablemente esto parezca un poco tonto: ¿por qué estoy creando un método privado para devolver algo tan simple? Bueno, lo que realmente quiero destacar son las dos "tareas" distintas de la paginación. En primer lugar, devolver el subconjunto correcto de los 50 resultados, lo que haremos dentro de un momento. En segundo lugar, devolver el recuento del número total de elementos. Cuando utilizas Doctrine, ejecuta 2 consultas distintas para esto: una para obtener los resultados de la página actual con un LÍMITE y un DESFASE, y una segunda consulta CONTAR para contar cada fila.

## Página actual, Límite, Desplazamiento: El Servicio de Paginación

Bien, volviendo al principio, utilicemos estas variables `$currentPage`, `$itemsPerPage` y`$totalItems`.

[[[ code('87acc17371') ]]]

Vale, genial... pero lo que realmente necesitamos es determinar la página actual y utilizarla para devolver sólo un subconjunto de los resultados. Por ejemplo, si mostramos 10 por página... y estamos en la página 2, deberíamos devolver las búsquedas 11 a 20.

La paginación funciona mediante un parámetro de consulta `?page`: `?page=2` debería significar que estamos en la página 2. Pero nuestro código aún no está leyendo esto. Mira: sigue pensando que estamos en la página 1... porque así lo hemos codificado. Para obtener la página correcta, podríamos intentar leer directamente el parámetro de consulta... ¡pero no hace falta! API Platform nos proporciona un servicio que ya contiene toda la información sobre la paginación.

Encima, añade un segundo argumento constructor llamado `private Pagination` - de la plataforma API `$pagination`. 

[[[ code('67280de16b') ]]]

A continuación, establece `$currentPage` en `$this->pagination->getPage()`, que necesita el `$context` que tenemos como argumento en este método. Luego establece `$itemsPerPage` en `$this->pagination->getLimit()` pasando `$operation`y `$context`. También podemos obtener un `$offset` de forma similar, lo que es súper práctico. Si estamos en la página 2 y el límite es 10, el servicio `Pagination` calculará que el desplazamiento debe ser 11. Vuelca las cuatro variables a continuación.

[[[ code('048a668faa') ]]]

¡Vamos a comprobarlo! Vuelve a la página 1, actualiza y ¡mira esto! Página 1, 30 elementos por página, el límite y el desplazamiento 0. Si vamos a `page=2`, entonces es la página 2, el número por página sigue siendo 30 y el desplazamiento es 30.

¿De dónde saca 30 como número de elementos por página? Ese es el valor por defecto en API Platform para cualquier recurso. Pero esto es algo que puedes configurar en tu atributo`#[ApiResource]`: cambia `paginationItemsPerPage` a, qué tal, 10.

[[[ code('afed7cfd4e') ]]]

Ahora pruébalo. Eso cambia a 10 y el desplazamiento es 10. Si vamos a la página 3, nuestro por página sigue siendo 10. Y ahora dice

> Oye, como estamos en la página 3, deberías empezar en el resultado 20.

## Obtener los resultados correctos para la página actual

Ahora estamos en buena forma. Nuestro trabajo final es utilizar esta información para devolver el subconjunto correcto de resultados, en lugar de todas las búsquedas. Para ello, pasa `$offset` y `$itemsPerPage` a `createQuests()`.

[[[ code('6d06f0224f') ]]]

Aquí abajo, añade `int $offset` y `int $limit` con un valor predeterminado de 50. Y utiliza estos:`$i = $offset` y luego `$i <=` `$offset` más `$limit`.

[[[ code('2dc502dcb6') ]]]

Ok equipo ¡comprobadlo! Estamos en la página 3 y... ¡estos son los elementos de la página 3! Es más obvio si vamos a la página 1. Mira las descripciones: descripción 1, 2, 3 y así sucesivamente. Así pues, ¡la paginación funciona en nuestra colección!

Aunque, en este sencillo ejemplo, tengo que asegurarme de que no rompo el proveedor de objetos. Como estamos buscando la cadena del día como clave de un array, tenemos que devolver todas las búsquedas. Para asegurarnos de que eso ocurre, pasa 0 y 50.

[[[ code('01cea93220') ]]]

En una aplicación real, harías esto más inteligente, por ejemplo, consultando por el elemento que necesitas... en lugar de cargarlos todos.

Eso es la paginación de un recurso personalizado. ¿Y el filtrado? Hablaremos de la creación de filtros personalizados en un próximo tutorial. Pero alerta de spoiler: la lógica de filtrado también es algo que ocurre aquí mismo, dentro del proveedor de la colección.

A continuación: vamos a eliminar todo el material del recurso API de nuestra entidad `User` y a añadirlo a una nueva clase que se dedicará a nuestra API. Woh.
