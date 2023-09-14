# Utilizar un identificador personalizado (fecha)

Para nuestras rutas de la API `DailyQuest`, configuramos un `id` como identificador. Pero lo que realmente queremos es una fecha... para poder tener URLs extravagantes como`/api/quests/2023-06-05`.

¡Vamos a intentarlo! En `DailyQuest`, en lugar de `public int $id`, di`public \DateTimeInterface $day`. Y en el constructor, sustituye el argumento por`\DateTimeInterface $day`... y `$this->day = $day`.

[[[ code('2ac336b29d') ]]]

A continuación, en `DailyQuestStateProvider`, diremos... ¿qué tal `new \DateTime('now')`y `new \DateTime('yesterday')`.

[[[ code('8029c29ec2') ]]]

Cuando actualizamos los documentos... volvemos a estar como antes: nos falta el ID en `PUT`, `DELETE`, y `PATCH`, y nuestro único `GET` ha desaparecido. Eso se debe a que la API Platform no sabe que la propiedad `$day` debe ser nuestro identificador. Aunque, si probamos la ruta de la colección `GET`... ¡eh! ¡El campo `day` sí aparece en el JSON como una propiedad normal!

Lo que queremos hacer es decirle a API Platform:

> ¡Eh! Esto no es una propiedad normal: `day` es nuestro identificador.

Lo hacemos añadiendo un atributo `#[ApiProperty]` por encima de éste con `identifier: true`.

[[[ code('6efb30b69a') ]]]

## Depuración de errores de generación de IRI

Cuando lo comprobamos, esto, de hecho, arregla todas nuestras rutas. Pero cuando probamos la ruta de recogida... obtenemos un error 400:

> No se ha podido generar un IRI para el elemento de tipo `DailyQuest`.

Así que API Platform cargó nuestros dos objetos `DailyQuest`... pero cuando intentó generar la propiedad `@id` (el IRI), por alguna razón, ¡explotó!

Para saber más, baja a la barra de herramientas de depuración web y abre esa petición en el perfilador. En la pestaña Excepción, había dos excepciones en esta página: una situación de excepción anidada.

El nivel superior - `Unable to generate an IRI` - no nos dice realmente por qué había un problema. Aquí abajo, podemos ver:

> No hemos podido resolver el identificador que coincide con el parámetro "día".

Este error tampoco es superclaro, pero está más cerca. En realidad está diciendo

> ¡Oye! He intentado generar el IRI utilizando el campo `day`... pero eso es un objeto
> objeto `DateTimeInterface`... y no sé cómo convertirlo en una cadena.

En realidad, hemos elegido un IRI bastante complicado con el que trabajar, y creo que eso está bien. API Platform tiene un sistema llamado "transformador de variables URI". El `{day}`es una variable en la ruta... y puede ayudar a "transformar" el objeto `DateTimeInterface`en algo que pueda utilizarse en esa cadena. La documentación "Identificadores" habla de ello.

Pero también hay una solución sencilla. Crea una nueva función llamada `getDayString()`que devolverá un `string`. Dentro, `return $this->day->format()` con el formato que queramos: `Y-m-d`.

## Hacer que un método sea el identificador

El truco está en hacer que este método sea el identificador: mueve el `ApiProperty`de la propiedad real... por encima de esto.

[[[ code('3d1ced3863') ]]]

Perfecto Aquí detrás... las rutas siguen pareciendo correctas. Puedes ver que ahora tenemos`{dayString}`. Y cuando probamos nuestra ruta de recolección `GET`... ¡fíjate! Vemos `"@id": "/api/quests/` y luego la cadena de fecha. ¡Eso es exactamente lo que queríamos!

Aunque, ahora tenemos un campo `dayString` en el JSON... además del propio `day`. Pensemos. Realmente no necesitamos en absoluto el campo `day`: existe internamente sólo para ayudar al `dayString`. Y como el `dayString` está en la URL, tenerlo como campo también parece innecesario. ¿Podemos ocultarlos?

## Ocultar campos específicos de tu API

Por supuesto ¡Y ni siquiera necesitamos utilizar grupos de serialización! Profundizaremos en esto más adelante, pero por encima de la propiedad `day`, podemos ocultarla por completo de nuestra API utilizando un atributo `#[Ignore]` del serializador de Symfony.

[[[ code('ca75691d0c') ]]]

Si nos dirigimos aquí y "Ejecutamos" eso... ¡boom! Ese campo ha desaparecido: no se puede leer ni escribir.

Podríamos hacer lo mismo con `getDayString()`. Pero otra opción es decir`readable: false`. Esto significa que no se podrá leer, pero técnicamente seguirá siendo escribible. Sin embargo, como no hay `setDayString`, en realidad no es escribible.

[[[ code('1128443ed7') ]]]

Ahora, cuando "Ejecutamos" esto... ese campo también desaparece.

¡Ésta es la configuración que queremos! Tenemos el ID que queremos, no tenemos ningún campo extra que no queramos, y ahora podemos añadir los campos que queramos. Para ello, vamos a crear un Enum.

Crea un directorio `src/Enum/`... y, dentro, una nueva clase PHP, o realmente enum, llamada`DailyQuestStatusEnum`. Voy a pegar un poco de código aquí.

[[[ code('8734a5dd3b') ]]]

Esto es sólo una forma de hacer un seguimiento del estado de cada `DailyQuest`. De vuelta a esa clase, vamos a añadir algunas propiedades: `public string $questName`,`public string $description`.... y cualquier otra propiedad que necesitemos en nuestra API, como `public int $difficultyLevel`, y una `public DailyQuestStatusEnum` llamada`$status`.

[[[ code('49e90aa9c8') ]]]

## Los campos nulos se ocultan

¡Qué bien! ¡Vamos a probar esto! Dirígete... ¡y Ejecuta! Hmm, aún no vemos ninguno de los nuevos campos. Eso es porque no están rellenados y, por defecto, la API Platform oculta los campos nulos o no inicializados.

Pero si actualizamos la página y bajamos a la documentación de la respuesta... muestra que forman parte de la API.

Dirígete a `DailyQuestStateProvider` para que podamos rellenarlos. Digamos`return $this->createQuests()`: una nueva función privada que crearemos. También la pegaré: puedes coger el código del bloque de código de esta página.

[[[ code('a6f723de8b') ]]]

Esto crea 50 búsquedas -cada una un día más en el pasado- y rellena datos sencillos para el resto de los campos. Algunas de las búsquedas serán `ACTIVE`, y otras `COMPLETED`.

Ah, y fíjate en que estoy utilizando `getDayString()` como clave para esta matriz. No hace falta que lo hagas: las claves de la matriz devuelta por tu proveedor de colecciones no son importantes. Sólo lo he hecho porque será útil dentro de unos minutos, cuando creemos la operación get one.

¡Hora de probar! Muévete, vuelve a darle a "Ejecutar" y... ¡mira esto! Tenemos 50 elementos con datos en todos ellos. ¡Es una pasada!

A continuación: Hagamos que nuestro proveedor funcione para las operaciones de ítems: es decir, cuando obtenemos un único ítem. El proveedor de elementos se utiliza para las operaciones GET one, `PUT`,`PATCH` y `DELETE`.
