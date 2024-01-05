# Filtros: Buscar resultados

Algunos de nuestros tesoros de dragón están publicados y otros no. Eso es gracias a `DragonTreasureFactory`, donde publicamos aleatoriamente algunos pero no otros.

Ahora mismo, la API devuelve hasta el último tesoro dragón. En el futuro, haremos que nuestra API devuelva automáticamente sólo los tesoros publicados. Pero para empezar, al menos hagamos posible que nuestros clientes de la API puedan filtrar los resultados no publicados si lo desean.

## Hola ApiFiltro

¿Cómo? Aprovechando los filtros. API Platform viene con un montón de filtros incorporados que te permiten filtrar las colecciones de resultados por texto, booleanos, fechas y mucho más.

Funciona así: sobre tu clase, añade un atributo llamado `ApiFilter`.

Normalmente hay dos ingredientes que debes pasarle. El primero es qué clase de filtro quieres utilizar. Y si miras la documentación, hay un montón de ellas, como una llamada `BooleanFilter` que utilizaremos ahora y otra llamada`SearchFilter` que utilizaremos dentro de unos minutos.

Pasa este `BooleanFilter` -el de `ORM`, ya que estamos utilizando el ORM Doctrine- porque queremos permitir al usuario filtrar en un campo booleano.

Lo segundo que tienes que pasar es `properties` a una matriz de los campos o propiedades en los que quieres utilizar este filtro. Establécelo en `isPublished`:

[[[ code('75f2466630') ]]]

## Utilizar el filtro en la petición

¡Muy bien! Vuelve a la documentación y comprueba la ruta de recolección GET. Cuando probemos esto... ¡habrá un nuevo campo `isPublished`! Primero, pulsa "Ejecutar" sin configurarlo. Cuando nos desplacemos hasta abajo, ¡ahí lo tenemos!`hydra:totalItems: 40`. Ahora establece `isPublished` en `true` e inténtalo de nuevo.

¡Sí! Tenemos `hydra:totalItems: 16`. ¡Está vivo! Y comprueba cómo se produce el filtrado. Es muy sencillo, mediante un parámetro de consulta: `isPublished=true`. Y la cosa se pone más chula. Mira la respuesta: tenemos `hydra:view`, que muestra la paginación y ahora también tenemos un nuevo `hydra:search`. Sí, la API Platform documenta esta nueva forma de buscar directamente en la respuesta. Dice:

> Oye, si quieres, puedes añadir un parámetro de consulta `?isPublished=true` para filtrar
> estos resultados.

Bastante guay.

## Añadir filtros directamente sobre las propiedades

Ahora bien, cuando lees sobre filtros en los documentos de la API Platform, casi siempre los muestran encima de la clase, como hemos hecho nosotros. Pero también puedes poner el filtro encima de la propiedad a la que se refiere.

Observa: copia la línea `ApiFilter`, elimínala y baja a `$isPublished`. Pégala encima. Y ahora, ya no necesitamos la opción `properties`... API Platform lo resuelve por sí sola:

[[[ code('3bd0cf4bce') ]]]

¿El resultado? El mismo que antes. No lo probaré, pero si echas un vistazo a la ruta de la colección, sigue teniendo el campo de filtro `isPublished`.

## Filtro de búsqueda: Filtrar por texto

¿Qué más podemos hacer? Otro filtro realmente útil es `SearchFilter`. Hagamos que sea posible buscar por texto en la propiedad `name`. Esto es casi lo mismo: encima de `$name`, añade `ApiFilter`. En este caso queremos `SearchFilter`: de nuevo, coge el del ORM. Este filtro también acepta una opción. Aquí puedes ver que, además de `properties`, `ApiFilter` tiene un argumento llamado `strategy`. Eso no se aplica a todos los filtros, pero sí a éste. Establece `strategy`en `partial`:

[[[ code('bf1c077bbd') ]]]

Esto nos permitirá buscar en la propiedad `name` una coincidencia parcial. Es una búsqueda "difusa". Otras estrategias son `exact`, `start` y más.

¡Vamos a intentarlo! Actualiza la página de documentos. Y... ahora la ruta de la colección tiene otro cuadro de filtro. Busca `rare` y pulsa Ejecutar. Veamos, aquí abajo... ¡sí! Al parecer, 15 de los resultados tienen `rare` en algún lugar de `name`.

Y de nuevo, esto funciona añadiendo un simple `?name=rare` a la URL.

Oh, hagamos también que se pueda buscar en el campo `description`:

[[[ code('f9800671f7') ]]]

Y ahora... ¡también aparece en la API!

`SearchFilter` es fácil de configurar... pero es una búsqueda difusa bastante simple. Si quieres algo más complejo -como ElasticSearch- API Platform lo admite. Incluso puedes crear tus propios filtros personalizados, cosa que haremos en un futuro tutorial.

Muy bien: a continuación, veamos dos filtros más: uno sencillo y otro extraño... Un filtro que, en lugar de ocultar los resultados, permite al usuario de la API ocultar determinados campos en la respuesta.
