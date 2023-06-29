# Autenticador de token de acceso

Para autenticarse con un token, un cliente de la API enviará una cabecera `Authorization` con la palabra `Bearer` y, a continuación, la cadena del token... que no es más que una práctica estándar:

```php
$client->request('GET', '/api/treasures', [
    'headers' => [
        'Authorization' => 'Bearer TOKEN',
    ],
]);
```

Entonces algo en nuestra aplicación leerá esa cabecera, se asegurará de que el token es válido, autenticará al usuario y montará una gran fiesta para celebrarlo.

## Activar access_token

Afortunadamente, ¡Symfony tiene el sistema perfecto para esto! Gira y abre`config/packages/security.yaml`. En cualquier lugar bajo tu cortafuegos añade `access_token`:

[[[ code('2a1d1371f6') ]]]

Esto activa una escucha que observará cada petición para ver si tiene una cabecera`Authorization`. Si lo tiene, lo leerá e intentará autenticar al usuario.

Sin embargo, requiere una clase ayudante... porque aunque sabe dónde encontrar el token en la petición... ¡no tiene ni idea de qué hacer con él! No sabe si se trata de un JWT que debe descodificar... o, en nuestro caso, que puede consultar la base de datos en busca del registro coincidente. Así que, para ayudarle, añade una opción `token_handler` establecida en el id de un servicio que crearemos: `App\Security\ApiTokenHandler`:

[[[ code('92fa27cb33') ]]]

## Cortafuegos sin estado

Por cierto, si tu sistema de seguridad sólo permite la autenticación mediante un token de API, entonces no necesitas almacenamiento de sesión. En ese caso, puedes establecer una bandera `stateless: true` que indique al sistema de seguridad que, cuando un usuario se autentique, no se moleste en almacenar la información del usuario en la sesión. Voy a eliminar eso, porque tenemos una forma de iniciar sesión que depende de la sesión.

## La clase Token Handler

Bien, vamos a crear esa clase manejadora. En el directorio `src/` crea un nuevo subdirectorio llamado `Security/` y dentro de él una nueva clase PHP llamada`ApiTokenHandler`. Esta es una clase muy sencilla. Haz que implemente`AccessTokenHandlerInterface` y luego ve a "Código"->"Generar" o `Command`+`N` en un Mac y selecciona "Implementar Métodos" para generar el que necesitamos:`getUserBadgeFrom()`:

[[[ code('f1a055ac76') ]]]

El sistema `access_token` sabe cómo encontrar el token: sabe que vivirá en una cabecera `Authorization` con la palabra `Bearer` delante. Así que coge esa cadena, llama a `getUserBadgeFrom()` y nos la pasa. Por cierto, este atributo`#[\SensitiveParameter]` es una nueva característica de PHP. Está bien, pero no es importante: sólo asegura que si se lanza una excepción, este valor no se mostrará en el stacktrace.

Nuestro trabajo aquí es consultar la base de datos utilizando el `$accessToken` y luego devolver a qué usuario se refiere. Para ello, ¡necesitamos el `ApiTokenRepository`! Añade un método construct con un argumento `private ApiTokenRepository $apiTokenRepository`:

[[[ code('b18ccc3694') ]]]

Abajo, digamos `$token = $this->apiTokenRepository` y luego llama a `findOneBy()`pasándole un array, para que consulte donde el campo `token` es igual a `$accessToken`:

[[[ code('169e984841') ]]]

Si la autenticación falla por cualquier motivo, necesitamos lanzar un tipo de excepción de seguridad. Por ejemplo, si el token no existe, lanzar una nueva`BadCredentialsException`: la de los componentes Symfony:

[[[ code('59738c2cf9') ]]]

Esto hará que falle la autenticación... pero no necesitamos pasar un mensaje. Esto devolverá un mensaje "Credenciales incorrectas." al usuario.

Llegados a este punto, hemos encontrado la entidad `ApiToken`. Pero, en última instancia, nuestro sistema de seguridad quiere autenticar a un usuario... no un "Token API". Lo hacemos devolviendo un `UserBadge` que, en cierto modo, envuelve al objeto `User`. Observa: devuelve un `new UserBadge()`. El primer argumento es el "identificador de usuario". Pasa `$token->getOwnedBy()` para obtener el`User` y luego `->getUserIdentifier()`:

[[[ code('ac86d6a501') ]]]

## Cómo se carga el objeto usuario

Observa que en realidad no estamos devolviendo el objeto `User`. Esto se debe principalmente a que... ¡no lo necesitamos! Deja que te lo explique. Mantén pulsado `Command` o `Ctrl` y haz clic en`getUserIdentifier()`. Lo que esto devuelve realmente es el `email` del usuario . Así que estamos devolviendo un `UserBadge` con el `email` del usuario dentro. Lo que ocurre a continuación es lo mismo que ocurre cuando enviamos un `email` al punto final de autenticación `json_login`. El sistema de seguridad de Symfony toma ese correo electrónico y, como tenemos este proveedor de usuario, sabe que debe consultar la base de datos en busca de un `User` con ese `email`.

Así que volverá a consultar la base de datos en busca del `User` a través del correo electrónico... lo cual es un poco innecesario, pero está bien. Si quieres evitarlo, podrías pasar un callable al segundo argumento y devolver `$token->getOwnedBy()`. Pero esto funcionará bien tal como está.

Ah, ¡y probablemente sea buena idea comprobar y asegurarnos de que el token es válido! Si no lo es`$token->isValid()`, entonces podríamos lanzar otro `BadCredentialsException`. Pero si quieres personalizar el mensaje, también puedes lanzar un nuevo`CustomUserMessageAuthenticationException` con "Token caducado" para devolver ese mensaje al usuario:

[[[ code('1a581349e2') ]]]

## ¿Usar el Token en Swagger?

Y... ¡listo! Entonces... ¿cómo probamos esto? Bueno, lo ideal sería probarlo en nuestros documentos Swagger. Voy a abrir una nueva pestaña... y luego cerraré la sesión. Pero mantendré abierta mi pestaña original... ¡así podré robar estos tokens válidos!

Dirígete a los documentos de la API. ¿Cómo podemos decirle a esta interfaz que envíe un token de API cuando haga las peticiones? Bueno, habrás notado que hay un botón "Autorizar". Pero cuando lo pulsamos... ¡está vacío! Eso es porque todavía no le hemos dicho a Open API cómo pueden autenticarse los usuarios. Afortunadamente, podemos hacerlo a través de API Platform.

Abre `config/packages/api_platform.yaml`. Y una nueva clave llamada `swagger`, aunque en realidad estamos configurando los documentos de OpenAPI. Para añadir una nueva forma de autenticación, configura `api_keys` para activar ese tipo, luego `access_token`... que puede ser lo que quieras. Debajo de esto, dale un nombre a este mecanismo de autenticación... y `type: header` porque queremos pasar el token como cabecera:

[[[ code('e3440097b3') ]]]

Esto le dirá a Swagger -a través de nuestros documentos OpenAPI- que podemos enviar tokens de API a través de la cabecera `Authorization`. Ahora, cuando pulsemos el botón "Autorizar"... ¡sí! Dice "Nombre: Autorización", "En cabecera".

Para usar esto, tenemos que empezar con la palabra `Bearer` y luego un espacio... porque no lo rellena por nosotros. Hablaremos de ello más adelante. Probemos primero con un token no válido. Pulsa "Autorizar". En realidad, aún no se ha realizado ninguna petición: sólo se ha almacenado el código en JavaScript.

Probemos con la ruta get treasure collection. Cuando ejecutamos... ¡impresionante! ¡A 401! No necesitamos autenticarnos para utilizar este punto final, pero como pasamos una cabecera `Authorization` con `Bearer` y luego un token, el nuevo sistema `access_token`lo captó, pasó la cadena a nuestro manejador... pero luego no pudimos encontrar un token coincidente en la base de datos, así que lanzamos el error `BadCredentialsException`

Puedes verlo aquí abajo: la API devolvió una respuesta vacía, pero con una cabecera que contenía `invalid_token` y `error_description`: "Credenciales no válidas".

## Comprobación de que la autenticación por token funciona

Así que el caso malo funciona. ¡Probemos el caso feliz! En la otra pestaña, copia uno de los tokens válidos. Vuelve a deslizarte hacia arriba, pulsa "Autorizar" y luego "Cerrar sesión". Cerrar sesión sólo significa que "olvida" el token de la API que hemos establecido hace un minuto. Vuelve a escribir `Bearer `, pega, pulsa "Autorizar", cierra... y bajemos a probar de nuevo esta ruta. Y... ¡woohoo! ¡A 200!

Así que parece que ha funcionado... ¿pero cómo podemos saberlo? Pues bien, abajo, en la barra de herramientas de depuración web, haz clic para abrir el perfilador de esa petición. En la pestaña Seguridad... ¡sí! Hemos iniciado sesión como Bernie. ¡Éxito!

Lo único que no me gusta es tener que escribir esa cadena `Bearer` en el cuadro de autorización. No es muy fácil de usar. Así que, a continuación, vamos a solucionarlo aprendiendo cómo podemos personalizar el documento de especificaciones OpenAPI que utiliza Swagger.
