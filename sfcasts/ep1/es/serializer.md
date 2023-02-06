# El serializador

La clave detrás de cómo API Platform convierte nuestros objetos en JSON... y también de cómo transforma JSON de nuevo en objetos es el Serializador de Symfony. `symfony/serializer`
es un componente independiente que puedes utilizar fuera de la API Platform y es increíble. Le das cualquier entrada -como un objeto o cualquier otra cosa- y luego lo transforma en cualquier formato, como `JSON`, `XML` o `CSV`.

## El funcionamiento interno del serializador

Como puedes ver en este extravagante diagrama, sigue dos pasos. Primero, toma tus datos y los normaliza en una matriz. En segundo lugar, los codifica en el formato final. También puede hacer lo mismo a la inversa. Si partimos de JSON, como si enviáramos JSON a nuestra API, primero lo descodifica en una matriz y luego lo desnormaliza de nuevo en un objeto.

Para que todo esto ocurra, internamente hay muchos objetos normalizadores distintos que saben cómo trabajar con datos diferentes. Por ejemplo, hay un`DateTimeNormalizer` que es muy bueno manejando objetos `DateTime`. Por ejemplo, nuestra entidad tiene un campo `createdAt`, que es un objeto `DateTime`. Si te fijas en nuestra API, cuando probamos la ruta `GET`, ésta se devuelve como una cadena especial de fecha y hora. El `DateTimeNormalizer` es el responsable de hacerlo.

## Averiguar qué campos serializar

También hay otro normalizador muy importante llamado `ObjectNormalizer`. Su trabajo consiste en leer las propiedades de un objeto para poder normalizarlas. Para ello, utiliza otro componente llamado `property-access`. Ese componente es inteligente.

Por ejemplo, si miramos nuestra API, cuando hacemos una petición GET a la ruta de colecciones, uno de los campos que devuelve es `name`. Pero si miramos la clase,`name` es una propiedad privada. Entonces, ¿cómo demonios se lee eso?

Ahí es donde entra en juego el componente `PropertyAccess`. Primero mira si la propiedad`name` es pública. Y si no lo es, busca un método `getName()`. Así que eso es lo que se llama cuando se construye el JSON.

Lo mismo ocurre cuando enviamos JSON, por ejemplo para crear o actualizar un `DragonTreasure`. PropertyAccess examina cada campo del JSON y, si ese campo se puede establecer, por ejemplo mediante un método `setName()`, lo establece. Y es incluso un poco más genial que eso: buscará métodos getter o setter que no correspondan a ninguna propiedad real. Puedes utilizar esto para crear campos "extra" en tu API que no existan como propiedades en tu clase.

## Añadir un campo virtual "textDescription

¡Vamos a probarlo! Imagina que, cuando estamos creando o editando un tesoro, en lugar de enviar un campo `description`, queremos poder enviar un campo `textDescription`que contenga texto sin formato pero con saltos de línea. Entonces, en nuestro código, transformaremos esos saltos de línea en etiquetas HTML `<br>`.

Deja que te muestre lo que quiero decir. Copia el método `setDescription()`. A continuación, pega y llama a este nuevo método `setTextDescription()`. Básicamente va a establecer la propiedad `description`... pero también vamos a llamar a `nl2br()`. Esa función transforma literalmente las nuevas líneas en etiquetas `<br>`.

Sólo con ese cambio, actualiza la documentación y abre las rutas POST o PUT. ¡Vaya! ¡Tenemos un nuevo campo llamado `textDescription`! ¡Yup! El serializador vio el método `setTextDescription()` y determinó que `textDescription` es una propiedad virtual "configurable".

Sin embargo, no lo vemos en la ruta GET. ¡Y eso es perfecto! No existe el método `getTextDescription()`, por lo que aquí no habrá un nuevo campo. El nuevo campo es escribible, pero no legible.

¡Vamos a probarlo! Primero... Tengo que ejecutar la ruta de recolección GET para ver qué identificadores tenemos en la base de datos. Perfecto: tengo un Tesoro con ID 1. Cierra esto. Vamos a probar la ruta PUT para hacer nuestra primera actualización. Cuando utilices la ruta PUT, no necesitas enviar todos los campos: sólo los que quieras cambiar.

Pasa un `textDescription`... y yo incluiré `\n` para representar algunas líneas nuevas en JSON.

Cuando lo probemos, ¡sí! código de estado 200. Y fíjate: ¡el campo `description` tiene esos saltos de línea HTML!

## Eliminar campos

Vale, ahora que tenemos `setTextDescription()`... quizá sea la única forma en que queremos permitir que se establezca ese campo. Para imponerlo, elimina el método `setDescription()`.

Ahora, cuando actualizamos... y miramos la ruta PUT, ¡todavía tenemos `textDescription`, pero el campo `description` ha desaparecido! El serializador se ha dado cuenta de que ya no se puede establecer y lo ha eliminado de nuestra API. Seguiría siendo devuelto porque es algo que podemos leer, pero ya no es escribible.

Todo esto es realmente increíble. Simplemente nos preocupamos de escribir nuestra clase como queremos y luego API Platform construye nuestra API en consecuencia.

## Hacer que el campo plunderedAt sea de sólo lectura

Vale, ¿qué más? Bueno, es un poco raro que podamos establecer el campo `createdAt`... que normalmente se establece interna y automáticamente. Vamos a arreglarlo.

Pero, ¿sabes qué? Quería llamar a este campo `plunderedAt`. Reaccionaré y cambiaré el nombre de esa propiedad... luego dejaré que PhpStorm también cambie el nombre de mis métodos getter y setter.

¡Genial! Esto también hará que cambie la columna de mi base de datos... así que gira a tu consola y ejecuta:

```terminal
symfony console make:migration
```

Viviré peligrosamente y lo ejecutaré inmediatamente:

```terminal
symfony console doctrine:migrations:migrate
```

¡Listo! Gracias a ese cambio de nombre... en la API, excelente: el campo es ahora`plunderedAt`.

Vale, olvídate de la API por un momento: vamos a hacer un poco de limpieza. La finalidad de este campo `plunderedAt` es que se establezca automáticamente cada vez que creemos un nuevo `DragonTreasure`.

Para ello, crea un `public function __construct()` y, dentro, pon`this->plunderedAt = new DateTimeImmutable()`. Y ahora no necesitamos el `= null`en la propiedad.

Y si buscamos `setPlunderedAt`, ya no necesitamos ese método, así que elimínalo.

Esto significa ahora que la propiedad `plunderedAt` es legible pero no escribible. Así que, no te sorprendas, cuando actualizamos y abrimos la ruta `PUT` o `POST`, `plunderedAt`ha desaparecido. Pero si miramos el aspecto que tendría el modelo si buscáramos un tesoro, `plunderedAt` sigue ahí.

## Añadir un campo "Fecha Hace" falso

Muy bien, ¡un objetivo más! Vamos a añadir un campo virtual llamado `plunderedAtAgo` que devuelva una versión legible por humanos de la de la fecha, como "hace dos meses". Para ello, tenemos que instalar un nuevo paquete:

```terminal
composer require nesbot/carbon
```

Una vez que termine... busca el método `getPlunderedAt()`, cópialo, pégalo debajo, devolverá un `string` y llámalo `getPlunderedAtAgo()`. Dentro, devuelve`Carbon::instance($this->getPlunderedAt))` y luego `->diffForHumans()`.

Así que, como ahora entendemos, no hay ninguna propiedad `plunderedAtAgo`... pero el`serializer` debería ver esto como un legible a través de su getter y exponerlo como un nuevo campo. Ah, y ya que estoy aquí, añadiré un poco de documentación arriba para describir el significado del campo.

Bien, probemos esto. En cuanto actualizamos y abrimos la ruta `GET`, ¡vemos el nuevo campo en el ejemplo! También podemos ver los campos que obtendremos más abajo, en la sección Esquemas. Volvamos atrás, probemos la ruta `GET` con el ID `one`. Y... ¡genial! ¿A que mola?

Siguiente: ¿qué pasa si queremos tener ciertos métodos getter o setter en nuestra clase, como `setTextDescription()`, pero no queremos que formen parte de nuestra API? La respuesta: grupos de serialización.
