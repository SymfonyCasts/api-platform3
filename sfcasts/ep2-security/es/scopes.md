# Ámbitos de los tokens de la API

Cada `ApiToken` tiene una matriz de ámbitos, aunque todavía no la estamos utilizando. La idea es genial: cuando se crea un token, puedes seleccionar qué permisos tiene. Por ejemplo, puede que un token tenga permiso para crear nuevos tesoros, pero no para editar tesoros existentes. Para permitirlo, vamos a asignar los ámbitos de un token a roles en Symfony.

## ¿Cómo se cargan ahora los roles?

Ahora mismo en `ApiTokenHandler`, básicamente devolvemos el usuario... y luego el sistema se autentica completamente como ese usuario. Esto significa que obtenemos los roles que haya en ese objeto `User`. ¿Cómo podríamos cambiar eso para que nos autentiquemos como ese usuario... pero con un conjunto diferente de roles? ¿Un conjunto basado en los ámbitos del token?

Estamos utilizando el sistema de seguridad `access_token`. Pulsa `Shift`+`Shift` y abre una clase principal llamada `AccessTokenAuthenticator`. Esto es genial: ¡es el código real que hay detrás de ese sistema de autenticación! Por ejemplo, aquí es donde coge el token de la petición y llama al método `getUserBadgeFrom()` de nuestro controlador de token.

Los roles que tendrá el usuario también se determinan aquí: abajo dentro de`createToken()`. El "token" es, en cierto modo, una "envoltura" del objeto `User` en el sistema de seguridad. Y aquí es donde le pasamos los roles que debe tener. Como puedes ver, pase lo que pase, los roles serán `$passport->getUser()->getRoles()`. En otras palabras, siempre obtenemos los roles llamando a `getRoles()` en la clase `User`... que sólo devuelve la propiedad `roles`.

## Configurar el sistema de roles personalizados

Así que no hay un gran punto de enganche. Podríamos crear una clase autenticadora personalizada e implementar nuestro propio método `createToken()`. Pero eso es un fastidio porque tendríamos que reimplementar completamente la lógica de esta clase autenticadora. Así que, en lugar de eso, podemos... hacer una especie de trampa.

Empieza en `User`. Desplázate hasta la parte superior, donde están nuestras propiedades. Añade una nueva:`private ?array` llamada `$accessTokenScopes` e inicialízala a `null`:

[[[ code('984789ddcc') ]]]

Observa que no es una columna persistente. Es sólo un lugar para almacenar temporalmente los ámbitos que debe tener el usuario. A continuación, en la parte inferior añade un nuevo método público llamado `markAsTokenAuthenticated()` con un argumento `array $scopes`. Vamos a llamarlo durante la autenticación. Dentro, di`$this->accessTokenScopes = $scopes`:

[[[ code('c440abb92d') ]]]

Aquí es donde las cosas se ponen interesantes. Busca el método `getRoles()`. Sabemos que, pase lo que pase, Symfony llamará a esto durante la autenticación y lo que esto devuelva, esos serán los roles que tendrá el usuario. Vamos a "colar" nuestros roles de alcance.

En primer lugar, si la propiedad `$accessTokenScopes` es `null`, significa que estamos iniciando sesión como un usuario normal. En este caso, establece `$roles` en `$this->roles` para que obtengamos todos los `$roles` en `User`. A continuación, añade un rol extra llamado `ROLE_FULL_USER`:

[[[ code('de96bd3d3e') ]]]

Hablaremos de ello en un minuto.

Por otra parte, si iniciamos sesión mediante un token de acceso, digamos `$roles = $this->accessTokenScopes`:

[[[ code('21516fc3ad') ]]]

Y, en ambos casos, asegúrate de que siempre tenemos `ROLE_USER`:

[[[ code('dae0e07190') ]]]

Una vez hecho esto, dirígete a `ApiTokenHandler`. Justo antes de devolver`UserBadge`, añade `$token->getOwnedBy()->markAsTokenAuthenticated()` y pasa`$token->getScopes()`:

[[[ code('6252d9ba60') ]]]

¡Listo! ¡Vamos a probarlo! De vuelta a Swagger, ya tiene nuestro token de API... así que podemos volver a ejecutar la petición. Genial: vemos la cabecera `Authorization`. ¿Se ha autenticado con los ámbitos correctos?

Haz clic para abrir el perfil de esa petición... y dirígete a "Seguridad". ¡Lo hizo! Mira: hemos iniciado sesión como ese usuario, pero con `ROLE_USER`, `ROLE_USER_EDIT`y `ROLE_TREASURE_CREATE`: los dos ámbitos del token. Pero si iniciáramos sesión a través del formulario de acceso, en lugar de estos ámbitos, tendríamos los roles que el usuario tenga normalmente, además de `ROLE_FULL_USER`.

## Dar acceso sudo a usuarios normales con role_hierarchy

En el próximo capítulo, utilizaremos estos roles para proteger distintas operaciones de la API. Por ejemplo, para utilizar la ruta POST tesoros, necesitaremos `ROLE_TREASURE_CREATE`. Pero también tenemos que asegurarnos de que si un usuario se conecta a través del formulario de acceso, pueda utilizar esta operación, aunque no tenga exactamente ese rol. Ahí es donde `ROLE_FULL_USER` resulta útil.

Abre `config/packages/security.yaml` y, en cualquier lugar, añade `role_hierarchy`... Te recomiendo que lo escribas correctamente. Di `ROLE_FULL_USER`. Así, si has iniciado sesión como usuario completo, vamos a darte todos los ámbitos posibles que podría tener un token. Copia los tres ámbitos de los roles: `ROLE_USER_EDIT`, `ROLE_TREASURE_CREATE`y `ROLE_TREASURE_EDIT`:

[[[ code('4e9ecb4ca6') ]]]

Debemos asegurarnos de que si añadimos más ámbitos, también los añadimos aquí.

Gracias a esto, si protegemos algo requiriendo `ROLE_USER_EDIT`, los usuarios que se registren a través del formulario de acceso tendrán acceso.

Bien equipo, ¡hemos terminado con la autenticación! ¡Guau! A continuación, vamos a empezar con la "autorización", aprendiendo a bloquear operaciones para que sólo puedan acceder a ellas determinados usuarios.
