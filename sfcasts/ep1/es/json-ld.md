# JSON-LD: Dar sentido a tus datos

Acabo de utilizar el punto final de la colección `GET` para obtener todos mis recursos... lo que muestra que tenemos un tesoro con `id=1`. Cerraré esta operación... y utilizaré este otro punto final `GET`. Haz clic en "Probar", pon "1" como ID y haz clic en "Ejecutar".

## ¿Qué significan nuestros datos?

¡Estupendo! Pero... Tengo algunas preguntas. Concretamente: ¿qué significan estos campos? ¿Qué significan realmente `name` o `description` o `value`? ¿La descripción es texto sin formato? ¿HTML? ¿Es `name` un nombre corto del artículo o un nombre propio? ¿El valor está en dólares? ¿en euros? ¿en patatas fritas? ¿Qué demonios es `coolFactor`? ¿Y por qué te estoy haciendo todas estas preguntas injustas?

Si eres humano (lo eres... ¿verdad?), probablemente puedas averiguar por ti mismo gran parte del "significado" de estos campos. Pero las máquinas -vale, quizá menos las IA futuristas- bueno, no pueden descifrarlo. No saben lo que significan estas claves. Así que... ¿cómo podemos dar contexto y significado a nuestros datos?
# RDF: Marco de Descripción de Recursos

En primer lugar, existe esta cosa llamada "RDF" o "Marco de Descripción de Recursos", que es un conjunto de reglas sobre cómo describimos el significado de los datos para que los ordenadores puedan entenderlo. Es... aburrido y abstracto, pero básicamente es una guía sobre cómo puedes definir que un dato tiene un tipo determinado, o que un recurso es una subclase de algún otro tipo. En HTML, puedes añadir atributos a tus elementos para añadir estos metadatos RDF. Podrías decir que este `<div>` describe a una "persona", y que el nombre y el teléfono de esta persona son estos otros datos. Esto hace que el HTML aleatorio de tu sitio sea comprensible para las máquinas. Es incluso mejor si dos sitios diferentes utilizan exactamente la misma definición de "persona", que es por lo que los tipos son URL... y los sitios intentan reutilizar los tipos existentes en lugar de inventar otros nuevos.

## Hola JSON-LD

¿Por qué hablamos de esto? Porque JSON-LD intenta hacer lo mismo con nuestra API. Nuestras rutas API devuelven JSON. Pero la cabecera `content-type` de la respuesta dice que se trata de `application/ld+json`.

Cuando ves `application/ld+json`, significa que los datos son JSON... pero con campos extra que tienen un significado especial según un gigantesco documento de especificaciones JSON-LD. Así que, literalmente, JSON-LD es JSON... con golosinas extra.

## El campo @id

Por ejemplo, cada recurso, como `DragonTreasure`, tiene tres campos `@`. El más importante es probablemente `@id`. Es el identificador único del recurso. Es básicamente lo mismo que `id`, pero es aún mejor porque es una URL. Así que en lugar de decir simplemente `"id": 1`, tienes `@id` `/api/dragon_treasures/1` . Esto significa que, en primer lugar, la cadena será única en todas nuestras clases de recursos API y, en segundo lugar, ¡esta URL es práctica! Puedes introducirla en tu navegador y, si tienes la cabecera`accept` o añades `.jsonld` al final... ¡vaya!... deja que me deshaga de mi `/` extra... ¡sí! Podrás ver ese recurso. Así que `@id` es igual que `id`... pero mejor.

## Los campos @tipo y @contexto

Otro campo especial es `@type`. Describe el tipo de recurso, como los campos que tiene. Y si vemos dos recursos diferentes que tienen ambos `@type``DragonTreasure` , sabremos que representan lo mismo.

Puedes pensar en `@type` casi como una clase, que podemos utilizar para averiguar qué campos tiene y el tipo de cada campo. Pero... ¿dónde podemos ver realmente esa información?

Aquí es donde `@context` resulta útil. Copia la URL del contexto, pégala en tu navegador y... ¡precioso! Obtenemos este documento tan sencillo que dice que`DragonTreasure` tiene los campos `name`, `description`, `value`, `coolFactor`, `createdAt`, y`isPublished`. Si queremos aún más información sobre lo que significan, podemos seguir el enlace `@vocab`... para llegar a otra página de información.

Aquí podemos ver todas las clases de nuestra API -como `DragonTreasure` - y todas sus propiedades, como `name`. También podemos ver cosas como`required: false`, `readable: true`, `writeable: true` y también que es un `string`. Y tenemos esta información para cada campo. Mira: abajo en `value`. Podemos ver que se trata de un `integer`. Este `xmls:integer` remite a otro documento, arriba, que, si lo siguiéramos, describiría `xmls:integer` con más detalle.

Llegados a este punto, puede que estés diciendo

> ¡Eh! ¡Esto se parece mucho al documento de especificaciones OpenAPI!

Y tienes razón. Hablaremos más de ello dentro de unos minutos.

También podrías estar pensando:

> Um... más o menos entiendo lo que dices... pero esto es confuso.

¡Y también tendrías razón! Es difícil, como simple humano, seguir todos estos enlaces para encontrar los campos y sus tipos. Pero imagínate lo que le parecería esto a una máquina. ¡Es una mina de oro de información!

Ah, y quiero mencionar que, si miras en `value`... `hydra:description`... ha recogido la documentación PHP que añadimos antes a ese campo.

## Añadir información extra

También podemos añadir información extra por encima de la clase para describir este modelo. Podríamos hacerlo a través de la documentación de PHP como siempre, pero `ApiResource` también tiene algunas opciones que podemos pasar. Una es `description`. Vamos a describirlo como `A rare and valuable treasure.`

[[[ code('da2a8477b5') ]]]

Ahora, cuando actualizamos la página... y buscamos "raro" (voy a cerrar algunas cosas aquí...), ¡sí! Se ha añadido la descripción al tipo `DragonTreasure`. Y, como es lógico, estos datos también aparecen aquí dentro de Swagger, porque también se añadieron al documento de especificaciones OpenAPI.

La cuestión es que, gracias a JSON-LD, tenemos campos adicionales en cada respuesta que dan a cada recurso un id único y una forma de descubrir exactamente cómo es ese "tipo".

A continuación: tenemos que discutir una última parte de la teoría: qué significan estas cosas de `hydra`.
