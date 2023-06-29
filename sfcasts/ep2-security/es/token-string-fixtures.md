# Generar el token de la API y los accesorios

La propiedad más importante de `ApiToken` es la cadena del token... que tiene que ser algo aleatorio. Crea un método constructor con un argumento `string $tokenType`:

[[[ code('8783e12576') ]]]

Esto no es obligatorio, pero GitHub ha dado con algo ingenioso: como tienen diferentes tipos de tokens, como tokens de acceso personal y tokens OAuth, dan a cada tipo de token su propio prefijo. Esto ayuda a saber de dónde viene cada uno.

Nosotros sólo vamos a tener un tipo, pero seguiremos la idea. En la parte superior, para almacenar el prefijo del tipo, añade `private const PERSONAL_ACCESS_TOKEN_PREFIX = 'tcp_'`:

[[[ code('776bfd9e9b') ]]]

Yo... me acabo de inventar ese prefijo. Nuestro sitio se llama Treasure Connect... y éste es un token de acceso personal, así que `tcp_`.

Abajo, para `string $tokenType =` pon por defecto`self::PERSONAL_ACCESS_TOKEN_PREFIX`:

[[[ code('34ca0ceeaf') ]]]

Para el token en sí, digamos `$this->token = $tokenType.` y luego usaré un código que generará una cadena aleatoria de 64 caracteres:

[[[ code('3e72ca8311') ]]]

Así que aquí hay 64 caracteres más el prefijo de 4 caracteres, igual a 68. Por eso he elegido esa longitud. Y como estamos configurando el `$token` en el constructor, esto ya no necesita `= null` ni ser anulable. Siempre será un `string`.

## Configurar los accesorios

¡Vale! ¡Ya está configurado! Así que vamos a añadir algunos tokens API a la base de datos. En tu terminal, ejecuta

```terminal
php ./bin/console make:factory
```

para que podamos generar una fábrica Foundry para `ApiToken`. Ve a ver la nueva clase:`src/Factory/ApiTokenFactory.php`. Abajo en `getDefaults()`:

[[[ code('88d2e1bb97') ]]]

Esto se ve bien en su mayor parte, aunque no necesitamos pasar el `token`. Ah, y quiero retocar los ámbitos:

[[[ code('1b34bbdd8a') ]]]

Normalmente, cuando creas un token de acceso -ya sea un token de acceso personal o uno creado a través de OAuth- puedes elegir qué permisos tendrá ese token: no tiene automáticamente todos los permisos que tendría un usuario normal. También quiero añadir eso a nuestro sistema.

De vuelta a `ApiToken`, en la parte superior, después de la primera constante, pegaré algunas más:

[[[ code('0433e894db') ]]]

Esto define tres ámbitos diferentes que puede tener un token. No son todos los ámbitos que podríamos imaginar, pero son suficientes para que las cosas sean realistas. Así, cuando creas un token, puedes elegir si ese token debe tener permiso para editar los datos del usuario, o si puede crear tesoros en nombre del usuario o si puede editar tesoros en nombre del usuario. También he añadido un `public const SCOPES` para describirlos:

[[[ code('ce1ba1730e') ]]]

Volviendo a nuestro `ApiTokenFactory`, vamos a dar, por defecto, a cada `ApiToken` dos de esos tres ámbitos:

[[[ code('641102420a') ]]]

¡Bien! `ApiTokenFactory` está listo. Último paso: abre `AppFixtures` para que podamos crear algunos ámbitos `ApiToken`. Quiero asegurarme de que, en nuestros datos ficticios, cada usuario tiene al menos uno o dos tokens de API. Una forma fácil de hacerlo, aquí abajo, es decir `ApiTokenFactory::createMany()`. Como tenemos 10 usuarios, vamos a crear 30 tokens. Luego le pasamos una función de devolución de llamada y, dentro, devolvemos una anulación de los datos por defecto. Vamos a anular `ownedBy` para que sea `UserFactory::random()`:

[[[ code('90c32c2c0c') ]]]

Esto creará 30 tokens y los asignará aleatoriamente a los 10, bueno en realidad 11, usuarios de la base de datos. Así que, de media, cada usuario debería tener asignados unos tres tokens API. Hago esto porque, para simplificar las cosas, no vamos a crear una interfaz de usuario en la que el usuario pueda hacer clic y crear tokens de acceso y seleccionar ámbitos. Vamos a saltarnos todo eso. En lugar de eso, como cada usuario ya tendrá algunos tokens de la API en la base de datos, podemos pasar directamente a aprender a leer y validar esos tokens.

Recarga los accesorios con:

```terminal
symfony console doctrine:fixtures:load
```

## Mostrar los tokens en el frontend

Y... ¡precioso! Pero ya que no vamos a construir una interfaz para crear tokens, al menos necesitamos una forma fácil de ver los tokens de un usuario... para poder probarlos en nuestra API. Cuando estemos autenticados, podemos mostrarlos aquí.

No es un detalle muy importante, así que lo haré rápidamente. En `User`, en la parte inferior, pegaré una función que devuelva una matriz de las cadenas de token de API válidas para este usuario:

[[[ code('74f02bde2d') ]]]

En `ApiToken`, también necesitamos un método `isValid()`... así que también lo pegaré:

[[[ code('7d53689fdd') ]]]

Puedes obtener todo esto de los bloques de código de esta página.

A continuación, abre `assets/vue/controllers/TreasureConnectApp.vue`... y añade una nueva prop que se pueda pasar: `tokens`:

[[[ code('78239d1a12') ]]]

Gracias a eso, tendremos una nueva variable `tokens` en la plantilla. Después del enlace "Cerrar sesión", pegaré algo de código que los muestre:

[[[ code('c50f9a4cf7') ]]]

Último paso: abrir `templates/main/homepage.html.twig`. Aquí es donde pasaremos props a nuestra aplicación Vue. Pásale uno nuevo llamado `tokens` y configúralo como, si `app.user`, entonces`app.user.validTokenStrings`, si no `null`:

[[[ code('af5d685f49') ]]]

¡Vamos a probarlo! Si actualizamos, ahora mismo no estamos conectados. Utiliza nuestros enlaces tramposos para iniciar sesión. Observa que no los muestra inmediatamente... podríamos mejorar nuestro código para que lo hiciera... pero no es gran cosa. Actualiza y... ¡ahí están! ¡Tenemos dos tokens!

Siguiente paso: vamos a escribir un sistema para que pueda leer estos tokens y autenticar al usuario en lugar de utilizar la autenticación de sesión.
