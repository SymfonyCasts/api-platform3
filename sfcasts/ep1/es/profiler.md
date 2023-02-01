# Depuración de la API con el Perfilador

Vamos a hacer cosas muy interesantes y complejas con la API Platform, así que antes de empezar, quiero asegurarme de que tenemos una configuración de depuración realmente impresionante. Porque... ¡a veces depurar APIs puede ser un coñazo! ¿Alguna vez has hecho una petición Ajax desde JavaScript... y la ruta final explota en un error 500 lleno de HTML? Sí, no es muy útil.

## Instalar el Perfilador

Una de las mejores características de Symfony es su barra de herramientas de depuración web. Pero si estamos construyendo una API... no va a haber una Barra de Herramientas de Depuración Web en la parte inferior de estas respuestas JSON. Entonces, ¿deberíamos molestarnos en instalar ese paquete? La respuesta es: ¡absolutamente!

Ve al terminal y ejecuta:

```terminal
composer require debug
```

Este es otro paquete Symfony Flex que instala `symfony/debug-pack`. Si te acercas a tu archivo `composer.json`, esto instaló un montón de cosas buenas: un logger... luego abajo, en `require-dev`, también añadió MakerBundle, DebugBundle y WebProfilerBundle, que es lo más importante para lo que vamos a hablar.

[[[ code('21fbef5319') ]]]

## Peticiones AJAX en la Barra de Herramientas de Depuración Web

Vuelve a nuestra página principal de documentación y actualízala. ¡Qué bien! Tenemos la barra de herramientas de depuración web en la parte inferior Aunque... eso no nos ayuda realmente porque... toda esta información es literalmente para la propia página de documentación. No es especialmente útil.

Lo que realmente queremos es toda esta información del perfilador para cualquier petición que hagamos a la API. Y eso es superposible. Utiliza la ruta de recolección GET. Pulsa "Probar" y luego observa atentamente aquí abajo en la Barra de Herramientas de Depuración Web. Cuando pulsé "Ejecutar"... ¡bum! Como eso era una petición AJAX, ¡apareció el icono AJAX en la barra de herramientas de depuración web! ¿Quieres ver toda la información del perfil profundo de esa petición? Sólo tienes que hacer clic en el pequeño enlace de ese panel. Sí, como puedes ver aquí, ahora estamos viendo el perfilador de la llamada a la API `GET /api/dragon_treasures`.

## API Platform y serializador en el perfilador

Y aquí hay muchas cosas interesantes. Obviamente, está la sección Rendimiento y todas las cosas normales. Pero una de mis partes favoritas es la pestaña "Excepción". Si tienes una ruta API y esa ruta API explota con un error -suele ocurrir-, puedes abrir esta parte del perfilador para ver la hermosa excepción HTML completa: incluido el seguimiento de pila en todo su esplendor. Muy práctico.

Tengo otros dos puntos favoritos cuando trabajo en una API. El primero, que no es ninguna sorpresa, es la pestaña "API Platform". Nos da información sobre la configuración de todos nuestros recursos API. Vamos a hablar más sobre esta configuración, pero esto te muestra las opciones actuales y posibles que podrías poner dentro de este atributo`ApiResource`. Está muy bien. Por ejemplo, esto muestra una opción `description`... ¡y ya la tenemos!

La otra sección realmente útil del perfilador es relativamente nueva: es para el "Serializador". Vamos a hablar mucho del serializador de Symfony y esta herramienta nos ayudará a echar un vistazo a lo que ocurre internamente.

## Encontrar el perfilador de una petición API

Así que la gran conclusión es que, en realidad, ¡cada petición de la API tiene un perfilador! Y hay varias formas de encontrarlo. La primera: si estás haciendo una petición AJAX -aunque sea a través de tu propio JavaScript-, puedes utilizar la barra de herramientas de depuración web.

Y, si miras un poco aquí abajo, éstas son las cabeceras de respuesta que nos ha devuelto nuestra API. Una se llama `X-Debug-Token-Link`, que nos ofrece una segunda forma de encontrar el perfilador para cualquier petición de la API. Ésta es exactamente la URL en la que acabamos de estar.

La última forma es... quizá la más sencilla. Supongamos que vamos directamente a `/api/dragon_treasure.json`. Desde aquí, no hay una forma fácil de llegar al perfilador. Pero ahora, abre una nueva pestaña y ve manualmente a `/_profiler`. ¡Sí! Esto nos muestra una lista de las últimas peticiones a nuestra aplicación... ¡incluida la petición GET que acabamos de hacer! Si haces clic en el pequeño enlace del token... ¡boom! Estamos dentro de ese perfilador.

Puedes hacer clic en "Últimas 10" en cualquier momento para volver a esa lista... y encontrar la petición que necesites.

Bonitas herramientas de depuración, ¡comprobado! A continuación: hablemos del concepto de "operaciones" en API Platform, que representan estas seis rutas. ¿Cómo podemos configurarlas? ¿O desactivar alguna? ¿O añadir más? ¡Averigüémoslo!