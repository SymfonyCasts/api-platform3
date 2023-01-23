# Swagger UI: Documentación interactiva

¡La increíble documentación interactiva con la que nos hemos topado no es algo de la API Platform! No, en realidad es una biblioteca de documentación de API de código abierto llamada Swagger UI. Y lo realmente genial de Swagger UI es que, si alguien crea un archivo que describa cualquier API, ¡esa API puede obtener todo esto gratis! ¡Me encantan las cosas gratis! Obtenemos Swagger UI porque la API Platform proporciona ese archivo de descripción de forma inmediata. Pero hablaremos de ello más adelante.

## Jugando con nuestra nueva API

Vamos a jugar con esto. Utiliza la ruta POST para crear un nuevo `DragonTreasure`. Recientemente hemos saqueado unas "Monedas de oro"... que obtuvimos de "Rico McPato". Está loco. Para nuestros propósitos, ninguno de los otros campos importa realmente. Aquí abajo, pulsa "Ejecutar" y... ¡boom! Cuando te desplaces hacia abajo, podrás ver que se ha realizado una petición POST a `/api/dragon_treasures` y se han enviado todos los datos como JSON Entonces, nuestra API devolvió un código de estado "201". Un estado 201 significa que la petición tuvo éxito y se creó un recurso. Luego devolvió este JSON, que incluye un `id` de `1`. Así que, como he dicho, esto no es sólo documentación: ¡realmente tenemos una API que funciona! Aquí también hay algunos campos adicionales: `@context`, `@id`, y `@type` De ellos hablaremos pronto.

Ahora que tenemos un `DragonTreasure` con el que trabajar, abre esta ruta "GET", haz clic en "Probar" y luego en "Ejecutar". Me encanta. Swagger acaba de hacer una petición `GET` a `/api/dragon_treasures` - este `?page=1` es opcional. Nuestra API devolvió información dentro de algo llamado `hydra:member`, que aún no es especialmente importante. Lo que importa es que nuestra API devolvió una lista de todos los `DragonTreasures` que tenemos actualmente, que es justo éste.

Así que, en sólo unos minutos de trabajo, tenemos una API completa para nuestra entidad Doctrine. Eso es genial.

## Negociación del contenido

Copia la URL de la ruta de la API, abre una nueva pestaña y pégala. ¡Guau! Esto... ¿ha devuelto HTML? Pero hace un segundo, Swagger dijo que hizo una petición `GET` a esa URL... y devolvió JSON. ¿Qué está pasando?

Una característica de la API Platform se llama "Negociación de contenido". Significa que nuestra API puede devolver el mismo recurso -como `DragonTreasure` - en varios formatos, como JSON, o HTML... o incluso cosas como CSV. Un formato ASCII sería genial. En cualquier caso, le decimos a la API Platform qué formato queremos pasando una cabecera `Accept` en la petición. Cuando utilizamos los documentos interactivos, nos pasa esta cabecera `Accept` configurada como `application/ld+json`. Pronto hablaremos de la parte `ld+json`... pero, gracias a esto, ¡nuestra API devuelve JSON!

Y aunque no lo veamos aquí, cuando vas a una página en tu navegador, éste envía automáticamente una cabecera `Accept` que dice que queremos `text/html`. Así que esto es la API Platform mostrándonos la "representación HTML" de nuestros tesoros dragón..., que no es más que la documentación. Observa: cuando abro la ruta para la que está esta URL, la ejecuta automáticamente.

La cuestión es: si queremos ver la representación JSON de nuestros tesoros dragón, tenemos que pasar esta cabecera `Accept`... lo cual es superfácil, por ejemplo, si estás escribiendo JavaScript.

Pero pasar una cabecera personalizada `Accept` no es tan fácil en un navegador... y estaría bien poder ver la versión JSON de esto. Afortunadamente, la API Platform nos da una forma de hacer trampas. Elimina el `?page=1` para simplificar las cosas. Luego, al final de cualquier ruta, puedes añadir `.` seguido de la extensión del formato que quieras: como `.jsonld`.

Ahora vemos el recurso `DragonTreasure` en ese formato. La API Platform también admite JSON normal de fábrica, así que podemos ver lo mismo, pero en JSON puro y estándar.

## ¿De dónde vienen las nuevas Rutas?

El hecho de que todo esto funcione significa que... aparentemente tenemos una nueva ruta para `/api`, así como un montón de otras rutas nuevas para cada operación -como `GET /api/dragon_treasures`. Pero... ¿de dónde vienen? ¿Cómo se añaden dinámicamente a nuestra aplicación?

Para responder a esto, ve a tu terminal y ejecuta:

```terminal
./bin/console debug:router
```

Haré esto un poco más pequeño para que podamos verlo todo. ¡Sí! Cada ruta está representada por una ruta normal, tradicional. ¿Cómo se añaden? Cuando instalamos la API Platform, su receta añadió un archivo `config/routes/api_platform.yaml`. Esto es en realidad una importación de rutas. Parece un poco raro, pero activa la API Platform cuando el sistema de rutas se está cargando. A continuación, la API Platform encuentra todos los recursos API de nuestra aplicación y genera una ruta para cada ruta.

La cuestión es que lo único en lo que tenemos que centrarnos es en crear estas bonitas clases PHP y decorarlas con `ApiResource`. La API Platform se encarga de todo el trabajo pesado de conectar esas rutas. Por supuesto, tendremos que ajustar la configuración y hablar de cosas más avanzadas, pero ¡eh! Ese es el objetivo de este tutorial. Y ya hemos tenido un comienzo épico.

Lo siguiente: Quiero hablar del secreto que hay detrás de cómo se genera esta documentación Swagger UI. Se llama OpenAPI.