# Incrustar DTO personalizados

Uno de los objetivos del recurso de misiones diarias es mostrar los abundantes tesoros que puede ganar un dragón al completar una misión. Incrustar una matriz de objetos `DragonTreasure` y mostrar sus IRI es una buena forma de hacerlo Pero no es la única manera.

## Crear la clase personalizada (no ApiResource)

Hora de las ideas: olvídate de señalar los tesoros exactos. ¿Y si simplemente mostramos el nombre, el factor "cool" y el valor de cada uno como una matriz personalizada de datos incrustados? Compruébalo. En el directorio `src/ApiResource/`, aunque esta clase podría vivir en cualquier parte, crea una nueva clase llamada `DailyQuestTreasure`. Ésta representará el tesoro que podrías ganar completando un `DailyQuest`.

Dentro, crea una `public function __construct` con una `public string $name`, `public int $value` y `public int $coolFactor`. Estoy utilizando propiedades públicas por simplicidad e incluso incluyendo las tres como argumentos del constructor para facilitarte aún más la vida.

[[[ code('e80338d482') ]]]

Pero no voy a hacer de esto un `ApiResource`. Bueno, podríamos hacerlo... si necesitamos que nuestros usuarios de la API puedan obtener datos de `DailyQuestTreasure` directamente... o actualizarlos. Pero ése no es el objetivo de esta clase. Será simplemente una estructura de datos que adjuntaremos a `DailyQuest`.

En `DailyQuest`, ya no contendrá una matriz de objetos `DragonTreasure`: contendrá una matriz de objetos `QuestTreasure`. Oh, en realidad, para ser más breves... allá vamos... llámalo `QuestTreasure`... y por aquí, `QuestTreasure`.

[[[ code('9994cb8186') ]]]

Ahora que tenemos la propiedad configurada, dirígete al proveedor para rellenarla. En lugar de poner directamente los tesoros aleatorios del dragón, tenemos que crear una matriz de objetos `QuestTreasure`. Para cada uno sobre los tesoros aleatorios como`$treasure`... entonces `$questTreasures[]` es igual a nuevo `QuestTreasure` y pasamos los datos: `$treasure->getName()`, `$treasure->getValue()` y`$treasure->getCoolFactor()`. Termina con `$quest->treasures = $questTreasures`.

[[[ code('50d3dd7daa') ]]]

## "Relaciones" que son objetos normales

Antes y después de este cambio, nuestra clase `DailyQuest` tenía una propiedad que contenía una matriz de objetos. La diferencia clave es que, antes, contenía una matriz de objetos que eran recursos de la API. Pero ahora, contiene una matriz de objetos normales y aburridos que no son recursos de la API.

¿Qué diferencia hay? Compruébalo. ¡Pum! ¡Objetos incrustados! Cuando la API Platform serializa la propiedad `treasures`, ve que nuestro `QuestTreasure` no es un `ApiResource`. Así que lo serializa de la forma normal: incrustando cada propiedad.

Esto es maravillosamente sencillo. Y es algo que quiero que recuerdes: siempre puedes crear nuevas clases de datos si quieres incrustar datos adicionales.

## El .conocido genId

Pero seguro que has notado este extraño `@id` con `.well-known/genId`. Esto... es una cadena generada aleatoriamente que existe, creo, porque se supone que los recursos JSON-LD tienen un `@id`. Pero como en realidad no tenemos un lugar donde puedas obtener Tesoros de Búsqueda individuales... API Platform nos da este falso.

Ahora, en teoría, podrías desactivarlo diciendo `#[ApiProperty()]` con`genId: false`.

[[[ code('6b1c8ea46a') ]]]

Por desgracia, esto no parece funcionar para las propiedades de matriz... quizá estoy haciendo algo mal. Me sale ese id. Pero sí funciona para objetos individuales. Para probarlo, cambia esto por un único `QuestTreasure`. Ya no necesitamos nuestro `@var`porque ahora tiene un tipo adecuado.

[[[ code('4a6206a518') ]]]

En nuestro proveedor, cambiaré algunas cosas superrápido... para obtener un único `QuestTreasure` aleatorio. Termina con `$quest->treasure` igual a este `QuestTreasure`. Utiliza `$randomTreasure` para todos los nombres de variables.

[[[ code('35a83c33e1') ]]]

¡Me encanta! Ahora, cuando actualicemos... veremos un objeto incrustado y ningún campo`@id` generado.

Lo siguiente: con un recurso personalizado como éste, no obtenemos paginación en nuestro recurso de colección automáticamente. Sí, devuelve los 50 elementos. Así que vamos a añadir eso.
