# La potente especificación OpenAPI

Antes he dicho que estos documentos interactivos proceden de una biblioteca de código abierto llamada Swagger UI. Y siempre que tengas alguna configuración que describa tu API, como qué rutas tiene y qué campos se utilizan en cada ruta, puedes generar automáticamente estos documentos Swagger enriquecidos.

Dirígete a https://petstore3.swagger.io. Esto está muy bien: es un proyecto de demostración en el que se utiliza la interfaz de usuario Swagger en una API de demostración. Además, ¡tiene un enlace al archivo de configuración de la API que lo hace posible!

## ¡Hola OpenAPI!

Vamos... ¡a ver qué aspecto tiene! ¡Woh! Sí, este archivo JSON describe completamente la API, desde la información básica sobre la propia API, hasta las diferentes URL, como actualizar una mascota existente, añadir una nueva mascota a la tienda, las respuestas... todo. Si tienes uno de estos archivos, puedes obtener Swagger al instante.

El formato de este archivo se llama OpenAPI, que no es más que un estándar sobre cómo deben describirse las API.

De vuelta a nuestros documentos, debemos tener el mismo tipo de archivo de configuración, ¿verdad? Pues sí, visita `/api/docs.json` para ver nuestra versión. ¡Yup! Se parece mucho. Tiene rutas, describe las distintas operaciones... todo. Lo mejor es que API Platform lee nuestro código y genera este archivo gigante para nosotros. Entonces, como tenemos este archivo gigante, obtenemos Swagger UI.

De hecho, si haces clic en "Ver fuente de la página", puedes ver que esta página funciona incrustando el documento JSON real directamente en el HTML. Luego, hay algo de JavaScript Swagger que lee eso y arranca las cosas.

## OpenAPI y herramientas gratuitas

La idea de tener una especificación OpenAPI que describa tu API es poderosa... porque cada vez hay más herramientas que pueden utilizarla. Por ejemplo, vuelve a la documentación de la API Platform y haz clic en "Generador de esquemas". Esto es bastante salvaje: puedes utilizar un servicio llamado "Stoplight" para diseñar tu API. Eso te dará un documento de especificación OpenAPI... y luego puedes utilizar el Generador de Esquemas para generar tus clases PHP a partir de eso. No vamos a utilizarlo, pero es una idea genial.

También hay un generador de admin integrado en React -jugaremos con él más adelante- e incluso formas de ayudar a generar JavaScript que hable con tu API. Por ejemplo, puedes generar un frontend Next.js haciendo que lea de tu especificación OpenAPI.

La cuestión es que la interfaz Swagger es impresionante. Pero aún más impresionante es el documento de especificaciones OpenAPI que hay detrás... y que puede utilizarse para otras cosas.

## Modelos / Esquemas en OpenAPI

Además de las rutas en Swagger, también tiene algo llamado "Esquemas". Éstos son tus modelos... y hay dos: uno para JSON-LD y otro normal. Hablaremos de JSON-LD en un minuto, pero son básicamente lo mismo.

Si abres uno, vaya, esto es inteligente. Sabe que nuestro `id` es un entero,`name` es una cadena, `coolFactor` es un entero y `isPublished` es un booleano. Toda esta información procede, una vez más, de este documento de especificaciones. Si buscamos `isPublished` aquí... ¡sí! Ahí está el modelo que describe `isPublished` como`type` `boolean` . Lo mejor es que API Platform genera esto... ¡sólo con mirar nuestro código!

Por ejemplo, ve que `coolFactor` tiene un tipo entero:

[[[ code('b6ad349c37') ]]]

así que lo anuncia como un entero en OpenAPI. Pero la cosa se pone aún mejor. 
Fíjate en `id`. Se establece como `readOnly`. ¿Cómo lo sabe? Bueno, `id` es una propiedad privada y no existe el método `setId()`:

[[[ code('4b1daaab89') ]]]

Por tanto, deduce correctamente que `id` debe ser `readOnly`.

También podemos ayudar a API Platform. Encuentra la propiedad `$value`... ahí está... y añade un poco de documentación encima para que la gente sepa que `This is the estimated value
of this treasure, in gold coins.` 

[[[ code('1b433f924f') ]]]

Dirígete, actualiza... y comprueba el modelo aquí abajo. En `value`... ¡aparece! 
La cuestión es: si haces un buen trabajo escribiendo tu código PHP y documentándolo, vas a obtener una rica documentación de la API gracias a OpenAPI, con cero trabajo extra.

A continuación: Hablemos de esos extraños campos `@`, como `@id`, `@type`, y `@context`. Provienen de algo llamado JSON-LD: una potente adición a JSON que API Platform aprovecha.
