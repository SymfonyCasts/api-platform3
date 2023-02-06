# Operaciones / Rutas finales

API Platform funciona tomando una clase como `DragonTreasure` y diciendo que quieres exponerla como recurso en tu API. Lo hacemos añadiendo el atributo `ApiResource`. En este momento, lo estamos colocando sobre una entidad Doctrine, aunque, en un futuro tutorial, aprenderemos que, en realidad, puedes colocar `ApiResource` sobre cualquier clase.

## Hola Operaciones

Por defecto, cada `ApiResource` incluye 6 rutas, que la API Platform denomina operaciones. Puedes verlas en el perfilador. Este es el perfilador de`GET /api/dragon_treasures.json`. Haz clic en la sección "API Platform". En la parte superior, vemos los metadatos de este recurso API. Debajo, vemos las operaciones. Esto... es más información de la que necesitamos ahora, pero hay `Get`, `GetCollection`, `Post`,`Put`, `Patch` y finalmente `Delete`. Estas son las mismas cosas que vemos en la documentación de Swagger.

Echémosles un vistazo rápido. En primer lugar, ¿qué operaciones devuelven datos? En realidad, todas, excepto `Delete`. Tanto `Get` como las rutas `Post`, `Put`y `Patch` devuelven un único recurso, es decir, un único tesoro. Y`GET /api/dragon_treasures` devuelve una colección.

¿A qué rutas enviamos datos cuando las utilizamos? A `POST` para crear, y a `PUT` y `PATCH` para actualizar. No enviamos ningún dato para `DELETE` ni para ninguna de las dos operaciones de`GET`.

## PUT vs PATCH

La mayoría de las rutas se explican por sí mismas: obtener una colección de tesoros, un solo tesoro, crear un tesoro y eliminar un tesoro. Los únicos confusos son poner frente a parchear. `PUT` dice "sustituye" y `PATCH` dice "actualiza". Eso... ¡parecen dos formas de decir lo mismo!

El tema de PUT frente a PATCH en las API puede ponerse picante. Pero en la API Platform, al menos hoy, PUT y PATCH funcionan igual: ambos se utilizan para actualizar un recurso. Y los veremos en acción a lo largo del curso.

## Personalizar las operaciones

Una de las cosas que podrías querer hacer es personalizar o eliminar algunas de estas operaciones... o incluso añadir más operaciones. ¿Cómo podríamos hacerlo? Como vimos en el perfilador, cada operación está respaldada por una clase.

De vuelta sobre la clase `DragonTreasure`, después de `description`, añade una clave `operations`. Fíjate en que estoy obteniendo autocompletado para las opciones porque son argumentos con nombre para el constructor de la clase `ApiResource`. Te lo mostraré dentro de un momento.

Establece esto como una matriz y luego repite todas las operaciones que tenemos actualmente. Así que,`new Get()`, pulsa tabulador para autocompletar eso, `GetCollection`, `Post`, `Put`, `Patch`y `Delete`.

Ahora, si vamos a la documentación de Swagger y la actualizamos... ¡no cambia absolutamente nada! Eso es lo que queríamos. Acabamos de repetir exactamente la configuración por defecto. Pero ahora somos libres de personalizar las cosas. Por ejemplo, supongamos que no queremos que se borren los tesoros... porque un dragón nunca permitiría que le robaran su tesoro. Elimina `Delete`... e incluso eliminaré la declaración `use`.

Ahora, cuando actualicemos, la operación `DELETE` habrá desaparecido.

## Opciones de ApiResource

Vale, así que cada atributo que utilizamos es en realidad una clase. Y saber eso es poderoso. Mantén pulsado comando o control y haz clic en `ApiResource` para abrirla. Esto es realmente genial. Cada argumento del constructor es una opción que podemos pasar al atributo. Y casi todos ellos tienen un enlace a la documentación donde puedes leer más. Hablaremos de los elementos más importantes, pero es un gran recurso que hay que conocer.

## Cambiar el shortName

Un argumento se llama `shortName`. Si miras en Swagger, nuestro "modelo" se conoce actualmente como `DragonTreasure`, que obviamente coincide con la clase. A esto se le llama "nombre corto". Y por defecto, las URL -`/api/dragon_treasures` - se generan a partir de él.

Supongamos que queremos acortar todo esto a "tesoro". No hay problema: establece `shortName` en `Treasure`.

En cuanto lo hagamos, observa el nombre y las URL. Muy bien. Este recurso se conoce ahora como "Tesoro" y las URL se han actualizado para reflejarlo.

## Opciones de funcionamiento

Aunque esa no es la única forma de configurar las URL. Al igual que con `ApiResource`, cada operación es también una clase. Mantén pulsada la tecla Comando (o Ctrl) y haz clic para abrir la clase `Get`. Una vez más, estos argumentos del constructor son opciones... y la mayoría tienen documentación.

Un argumento importante es `uriTemplate`. Sí, podemos controlar el aspecto de la URL operación por operación.

Compruébalo. Recuerda que `Get` es la forma de obtener un único recurso. Añade`uriTemplate` ajustado a `/dragon-plunder/{id}` donde esa última parte será el marcador de posición para el id dinámico. Para `GetCollection`, pasemos también `uriTemplate`ajustado a `/dragon-plunder`.

De acuerdo ¡Vamos a consultar la documentación! ¡Estupendo! Las demás operaciones mantienen la URL antigua, pero éstas utilizan el nuevo estilo. Más adelante, cuando hablemos de los subrecursos, profundizaremos en `uriTemplate` y su opción hermana `uriVariables`.

Vale... como es un poco tonto tener dos operaciones con URL raras, vamos a eliminar esa personalización.

Ahora que sabemos un montón sobre `ApiResource` y estas operaciones, es hora de hablar del corazón de API Platform: El serializador de Symfony. Eso a continuación.
