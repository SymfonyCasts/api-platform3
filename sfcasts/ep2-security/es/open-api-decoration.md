# Personalizar los documentos de OpenAPI

Para utilizar tokens de API en Swagger, tenemos que escribir la palabra "Bearer" (portador) y luego el token. ¡Lamentable! Sobre todo si pretendemos que lo utilicen usuarios reales. ¿Cómo podemos solucionarlo?

## La especificación OpenAPI es la clave

Recuerda que Swagger se genera enteramente a partir del documento de especificaciones OpenAPI que construye API Platform. Puedes ver este documento consultando la fuente de la página -puedes verlo todo ahí mismo- o yendo a `/api/docs.json`. Hace unos minutos, hemos añadido una configuración a API Platform llamada `Authorization`:

[[[ code('79ef0de195') ]]]

El resultado final es que ha añadido estas secciones de seguridad aquí abajo. Sí, es así de sencillo: esta configuración activó estas nuevas secciones en este documento JSON: nada más. Swagger entonces lee eso y sabe que debe hacer que esta "Autorización" esté disponible.

Así que indagué un poco directamente en el sitio de OpenAPI y descubrí que sí tiene una forma de definir un esquema de autenticación en el que no necesitas pasar manualmente la parte del "Portador". Desgraciadamente, a menos que me lo esté perdiendo, la configuración de API Platform no permite añadirlo. Entonces, ¿hemos terminado? De ninguna manera, y por una razón increíble.

## Crear nuestro OpenApiFactory

Para crear este documento JSON, internamente, API Platform crea un objeto `OpenApi`, rellena todos estos datos en él y luego lo envía a través del serializador de Symfony. Esto es importante porque podemos modificar el objeto `OpenApi` antes de que pase por el serializador. ¿Cómo? El objeto `OpenApi` se crea a través de un núcleo`OpenApiFactory`... y podemos decorarlo.

Compruébalo: en el directorio `src/`, crea un nuevo directorio llamado`ApiPlatform/`... y dentro, una nueva clase PHP llamada `OpenApiFactoryDecorator`. Haz que implemente `OpenApiFactoryInterface`. Luego ve a "Código"->"Generar" o`Command`+`N` en un Mac para implementar el único método que necesitamos: `__invoke()`:

[[[ code('cc243e4b14') ]]]

## ¡Hola Servicio Decoración!

Ahora mismo, existe un servicio central `OpenApiFactory` en la API Platform que crea el objeto `OpenApi` con todos estos datos. Éste es nuestro astuto plan: vamos a decirle a Symfony que utilice nuestra nueva clase como `OpenApiFactory`en lugar de la del núcleo. Pero... definitivamente no queremos reimplementar toda la lógica del núcleo. Para evitarlo, también le diremos a Symfony que nos pase el núcleo original `OpenApiFactory`.

Puede que te resulte familiar lo que estamos haciendo. Es la decoración de clases: una estrategia orientada a objetos para extender clases. Es muy fácil de hacer en Symfony y API Platform lo aprovecha mucho.

Siempre que hagas decoración, crearás un constructor que acepte la interfaz que estás decorando. Así que `OpenApiFactoryInterface`. Lo llamaré`$decorated`. Y déjame poner `private` delante de eso:

[[[ code('4aca171c9f') ]]]

Perfecto.

Aquí abajo, para empezar, di `$openApi = $this->decorated` y luego llama al método `__invoke()`pasándole el mismo argumento: `$context`:

[[[ code('50e9473a57') ]]]

Eso llamará a la fábrica del núcleo que hará todo el trabajo duro de crear el objeto `OpenApi` completo. Aquí abajo, devuelve eso:

[[[ code('b87699742c') ]]]

¿Y entre medias? Sí, ¡ahí es donde podemos liarnos! Para asegurarnos de que esto funciona, por ahora, simplemente vuelca el objeto `$openApi`:

[[[ code('01bee687b9') ]]]

## El atributo #[AsDecorator]

En este momento, desde un punto de vista orientado a objetos, esta clase está configurada correctamente para la decoración. Pero el contenedor de Symfony sigue configurado para utilizar el`OpenApiFactory` normal: no va a utilizar nuestro nuevo servicio en absoluto. De alguna manera tenemos que decirle al contenedor que, en primer lugar, el servicio principal `OpenApiFactory` debe ser sustituido por nuestro servicio, y en segundo lugar, que el servicio principal original debe pasarse a nosotros.

¿Cómo podemos hacerlo? Encima de la clase, añade un atributo llamado `#[AsDecorator]` y pulsa tabulador para añadir esa declaración `use`. Pásale el id de servicio del núcleo original`OpenApiFactory`. Puedes indagar un poco para encontrarlo o normalmente la documentación te lo dirá. En realidad, API Platform documenta la decoración de este servicio, así que en sus documentos encontrarás que el identificador del servicio es `api_platform.openapi.factory`:

[[[ code('ca759ab7bc') ]]]

¡Eso es! Gracias a esto, cualquiera que antes utilizara el servicio principal de`api_platform.openapi.factory` recibirá en su lugar nuestro servicio, pero nos pasará el original.

Así que... ¡debería funcionar! Para probarlo, dirígete a la página principal de la API y actualízala. ¡Sí! Cuando esta página se carga, renderiza el documento JSON de OpenAPI en segundo plano. ¡El volcado en la barra de herramientas de depuración web demuestra que ha dado con nuestro código! Y fíjate en ese precioso objeto `OpenApi`: lo tiene todo, incluido `security`, que coincide con lo que vimos en el JSON. Así que ahora, ¡podemos retocarlo!

## Personalizar la configuración OpenAPI

El código que voy a poner aquí es un poco específico del objeto `OpenApi` y de la configuración exacta que sé que necesitamos en el JSON final de la Open API:

[[[ code('4491bc450c') ]]]

Obtenemos el objeto `$securitySchemes`, y luego anulamos `access_token`. Esto coincide con el nombre que utilizamos en la configuración. Establece un nuevo objeto `SecurityScheme()` con dos argumentos con nombre: `type: 'http'` y `scheme: 'bearer'`:

[[[ code('3e6f7237ef') ]]]

¡Ya está! Primero actualiza el documento JSON sin procesar para que podamos ver qué aspecto tiene. Déjame buscar "Portador". ¡Ya está! ¡Hemos modificado el aspecto del JSON!

¿Qué opina Swagger de esta nueva configuración? Actualiza y pulsa "Autorizar". Genial: `access_token`, `http, Bearer`. Ve a robar un token de API... pégalo sin decir `Bearer` primero y dale a "Autorizar". Probemos la misma ruta. Uy, tengo que darle a "Probar". Y... ¡precioso! Mira esa cabecera `Authorization`! Nos ha pasado `Bearer`. Misión cumplida.

Por cierto, podrías pensar, dado que estamos anulando por completo la configuración de`access_token`, que podríamos simplemente eliminarla de `api_platform.yaml`. Por desgracia, por razones sutiles que tienen que ver con cómo se genera la documentación de seguridad, seguimos necesitándola. Pero diré`# overridden in OpenApiFactoryDecorator`:

[[[ code('165c605914') ]]]

Esto era sólo un ejemplo de cómo podrías ampliar tu documento de especificaciones de la API Abierta. Pero si alguna vez necesitas modificar algo más, ahora ya sabes cómo.

A continuación, hablemos de los ámbitos.
