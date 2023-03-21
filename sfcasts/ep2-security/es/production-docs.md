# ¿Documentos API en producción?

Bienvenidos de nuevo, maravillosos devotos de JSON, al episodio 2 de API Platform. En la parte 1, ¡nos pusimos manos a la obra! Creamos una API bastante asesina para almacenar tesoros de dragón, pero... ¡nos olvidamos por completo de añadir seguridad! Cualquier criatura pequeña y de pies peludos podría colarse por una puerta trasera... ¡y no tendríamos ni idea! Así que esta vez hablaremos de todo lo relacionado con la seguridad. Como la autenticación: ¿debo utilizar una sesión con un formulario de inicio de sesión... o necesito tokens de API? Y autorización, como denegar el acceso a rutas completas. Luego entraremos en cosas más complicadas, como mostrar u ocultar resultados en función del usuario e incluso mostrar u ocultar determinados campos en función del usuario. También hablaremos de campos totalmente personalizados, del método HTTP PATCH y de la creación de un sistema de pruebas de la API que tus amigos estarán celosos.

## Configuración del proyecto

Ya sabes lo que hay que hacer: para profundizar realmente en este tema, debes codificar conmigo. Descarga el código del curso de esta página. Después de descomprimirlo, encontrarás un directorio `start/` con el mismo código que ves aquí. Abre este ingenioso archivo `README.md` y sigue todas las instrucciones de configuración.

Aquí abajo estoy iniciando el servidor web `symfony`. Así que iré a un terminal que ya esté dentro del proyecto y ejecutaré

```terminal
symfony serve -d
```

para iniciar un servidor web local en segundo plano. Perfecto Mantendré pulsado `Cmd` y haré clic en esa URL para abrirla en mi navegador. ¡Hola Treasure Connect! Esta es la app que creamos en el episodio 1... aunque trabajamos exclusivamente en la API. Creamos rutas para tesoros, usuarios y la posibilidad de relacionarlos.

Esta página de inicio es totalmente nueva para el episodio 2. Es una pequeña aplicación Vue que construí. Tiene un formulario de inicio de sesión... pero aún no funciona: dependerá de nosotros darle vida.

## ¿Documentos interactivos en producción?

Ahora, antes de sumergirnos en la seguridad, una pregunta que me hacen a veces es:

> Oye Ryan, los documentos interactivos son superguays... pero ¿podría ocultarlos en
> producción?

Si tu API es privada -sólo está pensada para tu JavaScript-, podría tener sentido porque no quieres dar a conocer tus rutas al mundo. Sin embargo, no me siento demasiado obligado a ocultar los documentos... porque aunque lo hagas, las rutas siguen existiendo. Así que necesitarás una seguridad adecuada de todos modos.

Pero sí, ocultarlos es posible, así que veamos cómo. Aunque muestres tus docs, éste es un proceso interesante que muestra cómo funcionan juntas varias partes del sistema.

Busca tu terminal y ejecuta:

```terminal
php ./bin/console config:dump api_platform
```

Recuerda: este comando muestra toda la configuración posible para API Platform. Veamos... busca "swagger". Ya está. Hay una sección con cosas como`enable_swagger`, `enable_swagger_ui`, `enable_re_doc`, `enable_entrypoint`, y`enable_docs`. ¿Qué significa todo eso?

## Hola ReDoc

Primero quiero enseñarte qué es ReDoc, porque no hablamos de ello en el primer tutorial. Actualmente estamos viendo la versión Swagger de nuestra documentación. Pero existe un formato competidor llamado ReDoc... ¡y puedes hacer clic en el enlace "ReDoc" de la parte inferior para verlo! ¡Sí! Es la misma información de la documentación... ¡pero con un diseño diferente! Si te gusta esto, está ahí para ti.

## Desactivar los Docs

De todas formas, volviendo al terminal, hay un montón de configuraciones de "habilitación". Todas están relacionadas... pero son ligeramente diferentes. Por ejemplo, `enable_swagger` se refiere en realidad a la documentación de OpenAPI. Recuerda que es el documento JSON que alimenta los documentos de las API Swagger y ReDoc. Entonces, estos son si queremos mostrar o no esos dos tipos de documentación frontales. Y aquí abajo, `enable_entrypoint` y `enable_docs`controlan si ciertas rutas se añaden o no a nuestra aplicación.

Apuesto a que no ha tenido mucho sentido, así que vamos a jugar con esto. Imagina que queremos desactivar la documentación por completo. De acuerdo Abre `config/packages/api_platform.yaml`y, para empezar, añade `enable_docs: false`:

[[[ code('014b51d152') ]]]

En cuanto lo hagas y actualices... ¡bien! La documentación de nuestra API ha desaparecido... pero con un error 500. Cuando `enable_docs: false`, elimina literalmente la ruta a nuestra documentación.

Retrocedamos. Ir a `/api` siempre fue una especie de atajo para llegar a la documentación. La ruta real era `/api/docs`, `/api/docs.json` o `.jsonld`. Y ahora todas son 404 porque hemos desactivado esa ruta. Así que, ¡viva nuestra documentación!

Sin embargo, cuando vas a `/api`, en realidad no es una página de documentación. Es lo que se conoce como "punto de entrada": es nuestra página de inicio de la API. Esta página sigue existiendo... pero intenta enlazar con nuestra documentación de la API... que no existe, y explota.

Para desactivar el punto de entrada, muévete y añade `enable_entrypoint: false`:

[[[ code('9af27a9478') ]]]

Ahora yendo a `/api` nos da... ¡hermoso! A 404.

Vale, ya sabemos que podemos ir a `/api/treasures.json` o a `.jsonld`. Pero, ¿y si vamos a `/api/treasures`? Eso... ¡desgraciadamente es un error 500! Cuando nuestro navegador hace una petición, envía una cabecera `Accept` que dice que queremos HTML. Así que estamos pidiendo a nuestra API la versión `html` de los tesoros. Y la versión `html`es... la documentación. Así que intenta enlazar con la documentación y explota.

Para desactivar esto, podemos comunicar al sistema que no tenemos Swagger ni documentación de la API en absoluto... para que deje de intentar enlazar con ella. Hazlo configurando`enable_swagger: false`:

[[[ code('e6d40ceaa4') ]]]

Aunque... eso sólo da lugar a otro error 500 que dice:

> ¡Eh, no puedes activar Swagger UI sin activar Swagger!

Arregla eso con `enable_swagger_ui: false`:

[[[ code('78d4a3c223') ]]]

Y ahora... ¡más cerca!

## Deshabilitar el formato HTML

> No se admite la serialización para el formato `html`.

El problema es que seguimos solicitando la versión `html` de este recurso. Pero ahora que no tenemos documentación, nuestra API es como:

> Um... no estoy muy seguro de cómo devolver una versión HTML de esto.

Y la verdad es: si desactivamos totalmente nuestra documentación, ¡ya no necesitamos un formato HTML! Y, por tanto, podemos desactivarlo. Hazlo, muy sencillamente, eliminando `html` de`formats`:

[[[ code('c876501d2c') ]]]

Y... en realidad tenemos otro punto donde necesitamos hacerlo: en`src/Entity/DragonTreasure.php`. Cuando añadimos nuestro formato personalizado `csv`... veámoslo aquí... repetimos todos los formatos, incluido `html`. Así que quita también `html` de ahí:

[[[ code('2eaff81582') ]]]

Cuando actualicemos ahora... ¡ya está! Como no hay formato HTML, se pone por defecto `JSON-LD`. Nuestros documentos están ahora totalmente desactivados.

Ah, y para desactivar los documentos sólo para producción, crearía una variable de entorno -como `ENABLE_API_DOCS` - y luego haría referencia a ella en mi configuración:

```yaml
# config/packages/api_platform.yaml
api_platform:
    enable_swagger_ui: '%env(bool:ENABLE_API_DOCS)%'
```

Pero... Me gusta la documentación, así que voy a deshacer este cambio... y este cambio también para recuperar nuestros documentos.

[[[ code('7e052a8094') ]]]

[[[ code('66b2f1c986') ]]]

¡Me encanta!

A continuación, vamos a tener una charla informal sobre la autenticación. Tienes una API elegante: ¿necesitas tokens de API? ¿O algo más?