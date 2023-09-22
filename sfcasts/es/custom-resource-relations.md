# Relacionar ApiResources personalizados

Dentro de `DailyQuest`, añade una nueva propiedad: `public array $treasures`. 

[[[ code('b238d7ff93') ]]]

Ésta contendrá una serie de tesoros de dragón que puedes ganar si completas esta búsqueda: tesoros como un elegante sombrero de mago... una rana parlante... 
el segundo slinky más grande del mundo... ¡o las cuatro esquinas de un brownie! 
Mmmmmm...

## Añadir una propiedad Relaciones de matriz

En la tierra de PHP, esto es como cualquier otra propiedad. En nuestro proveedor, rellénalo: `$quest->treasures = `... y luego lo estableceremos a algo. En lugar de un aburrido array vacío, necesitamos algunos objetos `DragonTreasure`. Arriba, añade `public function __construct()` para autoconectar un`private DragonTreasureRepository $treasureRepository`. 

[[[ code('078cb1a36a') ]]]

Abajo, coge algunos tesoros:`$treasures = $this->treasureRepository->findBy()` pasando una matriz vacía por los criterios - así lo devolverá todo - no `orderBy`, y un límite de `10`.

[[[ code('8e4df2b8f8') ]]]

Sí, sólo encontraremos los 10 primeros tesoros de la base de datos. Voy a pegar un código aburrido que cogerá un conjunto aleatorio de estos objetos `DragonTreasure`. Ponlo en la propiedad `treasures`.

[[[ code('627f54872c') ]]]

¡Genial! Y, aunque ahora mismo no nos importe, para asegurarnos de que nuestra prueba sigue pasando, aquí arriba, añade `DragonTreasureFactory::createMany(5)`... porque si hay cero tesoros, pasarán cosas raras en nuestro proveedor... y los dragones escenificarán su ardiente levantamiento.

[[[ code('6a4dc937fd') ]]]

Vale, ¿aparece esta nueva propiedad en nuestra API? Dirígete a `/api/quests.jsonld` para ver.. un error familiar:

> Debes llamar a `setIsOwnedByAuthenticatedUser()` antes que a `isOwnedByAuthenticatedUser()`.

Lo sabemos: viene de `DragonTreasure`... hasta el final. 

[[[ code('23a13478e8') ]]]

Aparentemente, el serializador está intentando acceder a este campo, pero nunca lo establecemos... lo cual tiene sentido... porque el proveedor y el procesador para `DragonTreasure` no se llaman cuando estamos utilizando una ruta `DailyQuest`.

## Por qué la relación está incrustada

Pero... espera un segundo. Esto ni siquiera debería ser un problema. Deja que te muestre lo que quiero decir. Para silenciar temporalmente este error, y entender lo que está pasando, busca esa propiedad... ahí está... y dale un valor por defecto de `false`. 

[[[ code('53d1538e76') ]]]

Gira, actualiza y... ¡whoa! ¡Funciona! Aquí está nuestra búsqueda diaria... 
y aquí están los tesoros. Pero... esto no es, exactamente, lo que esperábamos. 
Cada tesoro es un objeto incrustado.

Recuerda: cuando tienes una relación con un objeto que es un `ApiResource`, como`DragonTreasure`, ese objeto sólo debe estar incrustado si la clase padre y la clase hija comparten grupos de serialización. Por ejemplo, si tuviéramos `normalizationContext`con `groups` establecido en `quest:read` así... donde el grupo `quest:read` está por encima de`$treasures`, y, en `DragonTreasure`, tuviéramos al menos una propiedad que también tuviera `quest:read`.

Pero, si no te encuentras en esta situación -demonios, no estamos utilizando grupos en absoluto-, entonces el serializador debería representar cada `DragonTreasure` como una cadena IRI. Debería ser una matriz de cadenas, ¡no objetos incrustados!

El problema es que el serializador mira esta propiedad `$treasures` y no se da cuenta de que contiene una matriz de objetos `DragonTreasure`. Sabe que es una matriz, pero antes de empezar a serializar, no sabe qué hay dentro. Y así, en lugar de enviarlos a través del sistema que serializa los objetos `ApiResource`, los envía a través del código que serializa los objetos normales... lo que hace que sólo serialice todas las propiedades.

Esto no es un problema con las entidades porque el serializador es inteligente: lee los metadatos de la relación Doctrine para averiguar que una propiedad es una colección de algún otro objeto `#[ApiResource]`. Resumiendo, esto es sencillo de arreglar... sólo que al principio es difícil de entender. Sobre la propiedad, añade algo de PHPDoc para ayudar al serializador: `@var DragonTreasure[]`.

[[[ code('0057997dec') ]]]

Pruébalo ahora... ¡bam! ¡Obtenemos cadenas IRI! No me molestaré, pero podríamos deshacer el valor por defecto que añadimos porque este objeto no se serializará... que es lo que nos dio este error en primer lugar.

Así que, aparte de la sorpresa del objeto incrustado, ¡añadir relaciones a nuestro recurso personalizado no es gran cosa! A continuación: en lugar de incrustar directamente objetos `DragonTreasure`, vamos a ver cómo podemos inventar una nueva clase y una nueva estructura de datos para representar estos tesoros.
