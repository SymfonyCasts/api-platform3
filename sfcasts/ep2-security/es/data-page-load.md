# Cerrar sesión y pasar datos de la API a JavaScript

¿Qué significa "cerrar sesión" en algo? ¿Como desconectarse de una API? Bueno, son dos cosas. En primer lugar, significa invalidar el token que tengas, si es posible. Por ejemplo, si tienes un token de API, le dirías a la API:

> Haz que este token de API deje de ser válido.

En el caso de la autenticación de sesión, es básicamente lo mismo: significa eliminar la sesión del almacén de sesiones.

La segunda parte de "desconectarse" es hacer que quien esté utilizando el token lo "olvide". Si tuvieras un token de API en JavaScript, lo eliminarías de JavaScript. En el caso de la autenticación de sesión, significa eliminar la cookie.

## Añadir la posibilidad de cerrar sesión

En cualquier caso, vamos a añadir la posibilidad de cerrar la sesión de nuestra autenticación basada en sesión. Volviendo a `SecurityController`, como antes, necesitamos una ruta y un controlador, aunque este controlador nunca será llamado. Llamaré al método `logout()` y devolveremos `void`. Verás por qué en un segundo. Dale a esto un `Route` de`/logout` y `name: app_logout`:

[[[ code('6c4bf8218a') ]]]

La razón por la que he elegido `void` es porque vamos a lanzar una excepción desde dentro del método. Hemos creado esto completamente porque necesitamos una ruta: el sistema de seguridad de Symfony interceptará las cosas antes de que se llame al controlador:

[[[ code('9771de5dce') ]]]

Para activar esa magia, en `security.yaml`, añade una clave llamada `logout` con `path`debajo configurada con ese nuevo nombre de ruta: `app_logout`:

[[[ code('dc308db25b') ]]]

Esto activa un oyente que ahora está atento a las peticiones a `/logout`. Cuando haya una petición a `/logout`, cerrará la sesión del usuario y lo redirigirá.

Muy bien, aquí, nuestra aplicación Vue cree que no estamos conectados, pero lo estamos: podemos verlo en la barra de herramientas de depuración web. Y si vamos manualmente a `/logout`... ¡boom! Ya hemos cerrado la sesión de verdad.

## Obtener los datos del usuario actual en JavaScript

Hace un momento hemos visto que, aunque hayamos iniciado sesión y la actualicemos, nuestra aplicación Vue no tiene ni idea de que hemos iniciado sesión. ¿Cómo podríamos solucionarlo? Una idea sería crear una ruta API `/me`. Entonces, al cargarse, nuestra aplicación Vue podría hacer una petición AJAX a esa ruta... que devolvería `null` o la información del usuario actual. Pero las rutas `/me` no son RESTful. Y hay una forma mejor: volcar la información del usuario en JavaScript al cargar la página.

## Establecer una variable JavaScript de usuario global

Hay dos formas diferentes de hacerlo. La primera es estableciendo una variable global. Por ejemplo, en `templates/base.html.twig`, en realidad no importa dónde, pero dentro del cuerpo, añade una etiqueta `script`. Y aquí digamos `window.user =` y luego`{{ app.user|serialize }}`. Serializa en `jsonld` y añade una `|raw` para que no escape la salida: queremos JSON en bruto:

[[[ code('f73ef8db54') ]]]

¿No es genial? En un minuto, lo leeremos desde nuestro JavaScript. Si refrescamos ahora mismo y miramos el código fuente, ¡sí! Vemos `window.user = null`. Y luego, cuando iniciemos sesión y actualicemos la página, fíjate: ¡ `window.user =` y una enorme cantidad de datos!

## Serialización a JSON-LD en Twig

Pero ocurre algo misterioso: ¡tiene los campos correctos! Fíjate bien, tiene `email`, `username` y luego `dragonTreasures`, que es lo que son todas estas cosas. Además, correctamente, no tiene `roles` ni `password`.

¡Así que parece que está leyendo correctamente nuestros grupos de normalización! Pero, ¿cómo es eso posible? Sólo estamos diciendo "serializa este usuario a `jsonld`". Esto no tiene nada que ver con la API Platform y no está siendo procesado por ella. Pero... nuestros grupos de normalización están configurados en API Platform. Entonces, ¿cómo sabe el serializador que debe utilizarlos?

La respuesta, por lo que sé, es que funciona... en parte por accidente. Durante la serialización, API Platform ve que estamos serializando un "recurso API" y busca los metadatos de esta clase.

Eso está bien... pero en realidad no es perfecto... y de todas formas me gusta ser explícito. Pasa un 2º argumento a serializar, que es el contexto y establece `groups` en`user:read`:

[[[ code('5051e5b57f') ]]]

Ahora, observa lo que ocurre cuando actualizamos. Como antes, se expondrán las propiedades correctas en`User`. Pero fíjate en la propiedad `dragonTreasures` incrustada. ¡Woh, ha cambiado! Antes estaba mal: incluía todo, no sólo lo que había dentro del grupo `user:read`.

## Lectura de los datos dinámicos de Vue

Bien, vamos a utilizar esta variable global en JavaScript: en`TreasureConnectApp.vue`. Ahora mismo, los datos de `user` siempre empiezan como `null`. Podemos cambiarlo a `window.user`:

[[[ code('734967ea72') ]]]

Cuando actualicemos... ¡ya está!

Siguiente: si utilizas Stimulus, una forma aún mejor de pasar datos a JavaScript es utilizar valores Stimulus.
