# En Autenticación correcta

Si actualizas la página y compruebas la barra de herramientas de depuración web, verás que no hemos iniciado sesión. Probemos a utilizar un correo electrónico y una contraseña reales. Podemos hacer trampas haciendo clic en los enlaces de correo electrónico y contraseña: este usuario existe en nuestro `AppFixtures`, así que debería funcionar. Y... vale... ¡las casillas desaparecen! Pero no ocurre nada más. Lo mejoraremos en un minuto.

## ¡Gracias Sesión!

Pero por ahora, actualiza la página y vuelve a mirar la barra de herramientas de depuración web. ¡Estamos autentificados! ¡Sí! Sólo con hacer una petición AJAX correcta a esa ruta de inicio de sesión, ha bastado para crear la sesión y mantenernos conectados. Mejor aún, si empezáramos a hacer peticiones a nuestra API desde JavaScript, esas peticiones también se autenticarían. Así es No necesitamos un lujoso sistema de tokens de API en el que adjuntemos un token a cada petición. Sólo tenemos que hacer una petición y, gracias a la magia de las cookies, esa petición se autenticará.

## REST y ¿qué datos devolver desde nuestra ruta de autenticación?

Así que el inicio de sesión ha funcionado... pero no ha pasado nada en la página. ¿Qué debemos hacer después de la autenticación? Una vez más, en realidad no importa. Si estás escribiendo tu sistema de autenticación para tu propio JavaScript, deberías hacer lo que sea útil para tu frontend. Actualmente devolvemos el id de `user`. Pero podríamos, si quisiéramos, devolver todo el objeto `user` como JSON.

Pero hay un pequeño problema con eso. No es super RESTful. Es una de esas cosas de "pureza REST". Cada URL de tu API, a nivel técnico, representa un recurso diferente. Esto representa el recurso de la colección, y esta URL representa un único recurso `User`. Y si tienes una URL diferente, se entiende que es un recurso diferente. La cuestión es que, en un mundo perfecto, sólo devolverías un recurso `User` desde una única URL en lugar de tener cinco rutas diferentes para buscar un usuario.

Si devolvemos el JSON de `User` desde esta ruta, "técnicamente" estamos creando un nuevo recurso API. De hecho, cualquier cosa que devolvamos desde esta ruta, desde un punto de vista REST, se convierte en un nuevo recurso de nuestra API. Para ser honesto, todo esto es semántica técnica y deberías sentirte libre de hacer lo que quieras. Pero tengo una sugerencia divertida.

## Devolver la IRI

Para intentar ser útil a nuestro frontend y algo RESTful, tengo otra idea. ¿Y si no devolvemos nada de la ruta .... pero colamos el IRI del usuario en la cabecera `Location` de la respuesta? Entonces, nuestro frontend podría utilizarlo para saber quién acaba de iniciar sesión.

Te lo mostraré. En primer lugar, en lugar de devolver el ID de usuario, vamos a devolver el IRI, que será algo parecido a `'/api/users/'.$user->getId()`. Pero no quiero codificarlo porque podríamos cambiar la URL en el futuro. Prefiero que la API Platform lo genere por mí.

Y, afortunadamente, API Platform nos ofrece un servicio autoinstalable para hacerlo Antes del argumento opcional, añade un nuevo argumento de tipo `IriConverterInterface`y llámalo `$iriConverter`:

[[[ code('4519be269a') ]]]

Luego, aquí abajo, `return new Response()` (el de `HttpFoundation`) sin contenido y con un código de estado `204`:

[[[ code('40e60a96c3') ]]]

El `204` significa que ha tenido "éxito... pero no hay contenido que devolver". También pasaremos una cabecera `Location` establecida en `$iriConverter->getIriFromResource()`:

[[[ code('5c93ae32a9') ]]]

Para que puedas obtener el recurso de un IRI o la cadena IRI del recurso, siendo el recurso tu objeto. Pasa este `$user`.

## Utilizar el IRI en JavaScript

¿Qué te parece? Ahora que estamos devolviendo esto, ¿cómo podemos utilizarlo en JavaScript? Lo ideal sería que, después de iniciar sesión, mostráramos automáticamente algo de información sobre el usuario a la derecha. Esta zona está construida por otro archivo Vue llamado `TreasureConnectApp.vue`:

[[[ code('18bdec01c3') ]]]

No entraré en detalles, pero mientras ese componente tenga datos del usuario, los imprimirá aquí. Y `LoginForm.vue` ya está configurado para pasar esos datos de usuario a `TreasureConnectApp.vue`. En la parte inferior, después de una autenticación correcta, aquí es donde borramos el estado de `email` y `password`, que vacía las casillas después de iniciar sesión. Si emitimos un evento llamado`user-authenticated` y le pasamos el `userIri`, `TreasureConnectApp.vue`ya está configurado para escuchar este evento. Entonces hará una petición AJAX a`userIri`, obtendrá el JSON de vuelta y rellenará sus propios datos.

Si no te sientes cómodo con Vue, no pasa nada. La cuestión es que todo lo que tenemos que hacer es coger la cadena IRI de la cabecera `Location`, emitir este evento, y todo debería funcionar.

Para leer la cabecera, di `const userIri = response.headers.get('Location')`. También descomentaré esto para que podamos `emit`:

[[[ code('d7202bed45') ]]]

¡Esto debería funcionar! Muévete y actualiza. Lo primero que quiero que notes es que seguimos conectados, pero nuestra aplicación Vue no sabe que estamos conectados. Vamos a arreglar eso en un minuto. Vuelve a iniciar sesión con nuestro correo electrónico y contraseña válidos. Y... ¡precioso! Hicimos la petición a `POST`, nos devolvió el IRI y luego nuestro JavaScript hizo una segunda petición a ese IRI para obtener los datos del usuario, que mostró aquí.

A continuación: Hablemos de lo que significa cerrar sesión en una API. A continuación, te mostraré una forma sencilla de decirle a tu JavaScript quién ha iniciado sesión al cargar la página. Porque, ahora mismo, aunque estemos conectados, en cuanto actualizo, nuestro JavaScript piensa que no lo estamos. Lamentable.
