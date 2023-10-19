# Dtos, mapeo y profundidad máxima de las relaciones

Dirígete a `/api/users.jsonld` para ver... una referencia circular procedente del serializador. ¡Caramba! Pensemos: API Platform serializa todo lo que devolvemos del proveedor de estado. Así que dirígete a .... y busca dónde se crea la colección. Vuelca los DTOs. Esto es lo que se está serializando, así que el problema debe estar aquí.

[[[ code('fb4857194e') ]]]

Actualiza y... ninguna sorpresa: vemos 5 objetos `UserApi`. Ah, pero aquí está el problema: el campo `dragonTreasures` contiene una matriz de objetos de entidad `DragonTreasure`... y cada uno tiene un `owner` que apunta a una entidad `User`... y que vuelve a apuntar a una colección de entidades `DragonTreasure`... lo que hace que el serializador serialice eternamente. ¡Pero ése ni siquiera es el verdadero problema! Lo sé, estoy lleno de buenas noticias. El verdadero problema es que el objeto `UserApi` debería referirse realmente a una entidad `DragonTreasureApi`, no a una `DragonTreasure`.

En `UserApi`, éste será ahora un `array` de `DragonTreasureApi`. Una vez que empecemos a seguir la ruta de los DTO, para conseguir la máxima fluidez, deberíamos relacionar los DTO con otros DTO... en lugar de mezclarlos con entidades.

[[[ code('f332970e0c') ]]]

Para rellenar los objetos DTO, ve al mapeador: `UserEntityToApiMapper`. Aquí abajo, en `dragonTreasures`, ya no podemos hacerlo porque eso nos dará objetos entidad`DragonTreasure`. Lo que queremos hacer básicamente es convertir de`DragonTreasure` a `DragonTreasureApi`. Así que, una vez más, ¡el micromapeador al rescate!

## Micro-Mapeo DragonTreasure -> DragonTreasureApi

Añade `public function __construct()` con `private MicroMapperInterface $microMapper`. Aquí abajo, añade algo de código extravagante: `$dto->dragonTreasures =` ajustado a `array_map()`, con una función que tiene un argumento `DragonTreasure`. Lo terminaremos en un segundo... pero primero pasa el array sobre el que hará el bucle:`$entity->getPublishedDragonTreasures()->toArray()`.

Así que: obtenemos un array de los objetos publicados `DragonTreasure` y PHP hace un bucle sobre ellos y llama a nuestra función para cada uno - pasándole el`DragonTreasure`. Lo que devolvamos se convertirá en un elemento dentro de una nueva matriz que se establece en `dragonTreasures`. Y lo que queremos devolver es un objeto `DragonTreasureApi`. Hazlo con`$this->microMapper->map($dragonTreasure, DragonTreasureApi::class)`.

[[[ code('6d4ad3fbbb') ]]]

## Relaciones circulares

¡Genial! Cuando actualizamos para probarlo... nos encontramos con un problema de referencia circular diferente. ¡Qué divertido! Éste viene de MicroMapper... y es un problema que ocurrirá siempre que tengas relaciones que hagan referencia unas a otras.

Piénsalo: pedimos a Micro Mapper que convierta una entidad `DragonTreasure` en`DragonTreasureApi`. Sencillo. Para ello, utiliza nuestro mapeador. ¿Y adivina qué? En nuestro mapeador, le pedimos que convierta la `owner` -una entidad `User` - en una instancia de`UserApi`. Para ello, el micro mapeador vuelve a `UserEntityToApiMapper` y... el proceso se repite. Estamos en un bucle: para convertir una entidad `User`, necesitamos convertir una entidad `DragonTreasure`... lo que significa que necesitamos convertir su `owner`... que es esa misma entidad `User`.

## Establecer la profundidad del mapeo

La solución está en tu mapeador, cuando llamas a la función `map()`. Pasa un tercer argumento, que es un "contexto"... una especie de matriz de opciones. Puedes pasar lo que quieras, pero Micro Mapper sólo tiene una opción que le interese. Pon `MicroMapperInterface::MAX_DEPTH` a 1. 

[[[ code('0064be67d0') ]]]

Veamos qué hace eso. Cuando actualizamos... mira el volcado, que viene del proveedor de estado. Mapea las entidades `User` a objetos `UserApi`... y vemos 5. También podemos ver que la propiedad `dragonTreasures` se rellena con objetos`DragonTreasureApi`. Así que ha realizado el mapeo de `DragonTreasure` a`DragonTreasureApi`. Pero cuando fue a mapear el `owner` de ese `DragonTreasure`a un `UserApi`, está ahí... pero está vacío. Es un mapeo superficial.

Cuando pasamos `MAX_DEPTH => 1`, estamos diciendo:

> ¡Eh! Quiero que mapees completamente esta entidad `DragonTreasure` a `DragonTreasureApi`.
> Esa es la profundidad 1. Pero si se vuelve a llamar al micro mapeador para mapear más profundamente,
> sáltate eso.

Bueno, no saltar exactamente. Cuando se llama al mapeador la 2ª vez para mapear la entidad`User` a `UserApi`, se llama al método `load()` de ese mapeador... pero no a `populate()`. Así que acabamos con un objeto `UserApi` con un `id`... pero nada más. Eso soluciona nuestro bucle circular. Y, en realidad, no nos importa que la propiedad `owner`sea un objeto vacío... ¡porque nuestro JSON nunca se renderiza tan profundamente!

Observa. Elimina el `dd()` para que podamos ver los resultados. Y... ¡perfecto! ¡El resultado es exactamente el esperado! Para `DragonTreasures`, sólo estamos mostrando la IRI.

Así que, por regla general, cuando llames a un micro mapeador desde dentro de una clase mapeadora, probablemente querrás establecer `MAX_DEPTH` en `1`. ¡Diablos, podríamos establecer `MAX_DEPTH` en `0`! Aunque la única razón para hacerlo sería una ligera mejora del rendimiento.

Esta vez, cuando mapeemos `$dragonTreasure` a `DragonTreasureApi`, prueba con `MAX_DEPTH => 0`. 

[[[ code('0fc141fa54') ]]]

Esto hará que la profundidad sea golpeada inmediatamente. Cuando vaya a mapear la entidad `DragonTreasure` a `DragonTreasureApi`, utilizará el mapeador, pero sólo llamará al método `load()`. El método `populate()` nunca será llamado. Vuelve a colocar el `dd()`. Lo que obtenemos es un objeto superficial para `DragonTreasureApi`.

Esto puede parecer raro, pero técnicamente está bien... porque esta matriz `dragonTreasures`se va a representar como cadenas IRI... y lo único que necesita API Platform para construir ese IRI es... ¡el `id`! ¡Compruébalo! Elimina el volcado y vuelve a cargar la página. Tiene exactamente el mismo aspecto. Acabamos de ahorrarnos un poquito de trabajo.

Así que, para ir sobre seguro -en caso de que incrustes el objeto- utiliza `MAX_DEPTH => 1`. Pero si sabes que estás utilizando IRIs, puedes poner `MAX_DEPTH` en `0`.

Por aquí, hagamos lo mismo: `MicroMapperInterface::MAX_DEPTH` puesto a 0 porque sabemos que aquí también sólo mostramos el IRI.

[[[ code('0cd0139f29') ]]]

## Forzar una matriz JSON

Otra cosa que habrás notado es que `dragonTreasures` de repente parece un objeto, con sus corchetes en lugar de corchetes. Bueno, en PHP es un array - `array_map` devuelve un array con la clave `0` establecida en algo y la clave `2` establecida en algo. Pero debido a que falta la clave `1`, cuando se serializa a JSON parece una matriz asociativa, o un "objeto" en JSON.

Si cambiamos `toArray()` por `getValues()` y actualizamos la página... ¡perfecto! Volvemos a tener una matriz normal de elementos.

Siguiente: Podemos leer de nuestro nuevo recurso `DragonTreasureApi`, pero aún no podemos escribir en él. Creemos un `DragonTreasureApiToEntityMapper` y volvamos a añadir cosas como la seguridad y la validación.
