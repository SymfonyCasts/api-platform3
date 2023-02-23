# Relaciones incrustadas

Así que cuando dos recursos están relacionados en nuestra API, aparecen como una cadena IRI, o colección de cadenas. Pero podrías preguntarte:

> Oye, ¿podríamos incluir los datos de `DragonTreasure` aquí mismo
> en lugar del IRI para que no tenga que hacer una segunda, tercera o cuarta petición
> para obtener esos datos?

Por supuesto Y, de nuevo, también puedes hacer algo realmente genial con Vulcain... pero aprendamos a incrustar datos.

## Incrustar Vs IRI mediante Grupos de Normalización

Cuando se serializa el objeto `User`, utiliza los grupos de normalización para determinar qué campos incluir. En este caso, tenemos un grupo llamado`user:read`. Por eso se devuelven `email`, `username` y `dragonTreasures`.

[[[ code('d2df34ad85') ]]]

Para transformar la propiedad `dragonTreasures` en datos incrustados, tenemos que ir a`DragonTreasure` y añadir este mismo grupo `user:read` al menos a un campo. Observa: encima de `name`, añade `user:read`. Luego... ve hacia abajo y añade también esto para `value`.

[[[ code('177f5ffe30') ]]]

Sí, en cuanto tengamos al menos una propiedad dentro de `DragonTreasure` que esté en el grupo de normalización `user:read`, el aspecto del campo `dragonTreasures` cambiará totalmente.

Observa: cuando lo ejecutemos... ¡impresionante! En lugar de una matriz de cadenas IRI, es una matriz de objetos, con `name` y `value`... y, por supuesto, los campos normales `@id`y `@type`.

Así que: cuando tengas un campo de relación, se representará como una cadena IRI o como un objeto... y esto depende totalmente de tus grupos de normalización.

## Incrustar en la otra dirección

Intentemos esto mismo en la otra dirección. Tenemos un `treasure` cuyo id es 2. Dirígete a la ruta GET un único tesoro... pruébalo... e introduce 2 para el id.

Sin sorpresa, vemos `owner` como una cadena IRI. ¿Podríamos convertirla en un objeto incrustado? ¡Por supuesto! Sabemos que `DragonTreasure` utiliza el grupo de normalización `treasure:read`. Así que, entra en `User` y añádelo a la propiedad `username`:`treasure:read`.

[[[ code('8f9f8ca297') ]]]

Sólo con ese cambio... cuando lo probemos... ¡sí! ¡El campo `owner` acaba de transformarse en un objeto incrustado!

## Incrustado para una ruta, IRI para otra

Vale, vamos a buscar también una colección de `treasures`: sólo hay que pedirlos todos. Gracias al cambio que acabamos de hacer, la propiedad `owner` de cada tesoro es ahora un objeto.

Esto me da una idea descabellada. ¿Y si disponer de toda la información de `owner` cuando obtengo un único `DragonTreasure` está bien... pero tal vez resulte exagerado que esos datos se devuelvan desde la ruta de recogida? ¿Podríamos incrustar `owner`al obtener un único `treasure`... pero utilizar la cadena IRI al obtener una colección?

La respuesta es... ¡no! Estoy bromeando, ¡por supuesto! ¡Podemos hacer las locuras que queramos! Aunque, cuantas más cosas raras añadas a tu API, más complicada se vuelve la vida. ¡Así que elige bien tus aventuras!

Hacer esto es un proceso de dos pasos. Primero, en `DragonTreasure`, busca la operación `Get`, que es la operación para obtener un único tesoro. Una de las opciones que puedes pasar a una operación es `normalizationContext`... que anulará la predeterminada. Añade `normalizationContext`, luego `groups` ajustado al estándar `treasure:read`. A continuación, añade un segundo grupo específico para esta operación: `treasure:item:get`.

[[[ code('77a687acd4') ]]]

Puedes llamarlo como quieras... pero a mí me gusta esta convención: nombre del recurso seguido de `item` o `collection` y luego el método HTTP, como `get` o `post`.

Y sí, olvidé la clave `groups`: lo arreglaré en un minuto.

En cualquier caso, si hubiera codificado esto correctamente, significaría que cuando se utilice esta operación, el serializador incluirá todos los campos que estén al menos en uno de estos dos grupos.

Ahora podemos aprovechar eso. Copia el nuevo nombre del grupo. Luego, en `User`, encima de`username`, en lugar de `treasure:read`, pega ese nuevo grupo.

[[[ code('ca6964b2f1') ]]]

¡Vamos a comprobarlo! Prueba de nuevo con la ruta GET. ¡Sí! Volvemos a `owner`que es una cadena IRI. Y si probamos con el punto final GET uno... oh, el propietario es... ¿también un IRI aquí? Culpa mía. Volviendo a `normalization_context` olvidé decir `groups`. Básicamente estaba poniendo dos opciones sin sentido en`normalization_context`.

Intentémoslo de nuevo. Esta vez... ¡lo tengo!

Cuando te pones así, es un poco más difícil saber qué grupos de serialización se están utilizando y cuándo. Aunque puedes utilizar el Perfilador para ayudarte con eso. Por ejemplo, ésta es nuestra petición más reciente para el tesoro único.

Si abrimos el perfilador para esa petición... y bajamos a la sección Serializador, vemos los datos que se están serializando... pero, lo que es más importante, el contexto de normalización... incluido `groups` establecido en los dos que esperamos.

Esto también es genial porque puedes ver otras opciones de contexto que establece la API Platform. Éstas controlan ciertos comportamientos internos.

Siguiente: vamos a volvernos locos con nuestras relaciones utilizando una ruta `DragonTreasure` para cambiar el campo `username` del propietario de ese tesoro. Woh.
