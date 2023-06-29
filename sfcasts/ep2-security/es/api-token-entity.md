# Tipos de token y la entidad ApiToken

Vale, ¿y si necesitas permitir el acceso programático a tu API?

## Tipos de tokens de acceso

Cuando hablas con una API mediante código, envías un token de API, comúnmente conocido como token de acceso:

```javascript
fetch('/api/kittens', {
    headers: {
        'Authorization': 'Bearer THE-ACCESS-TOKEN',
    }
});
```

La forma exacta de obtener ese token varía. Pero hay dos casos principales.

En primer lugar, como usuario del sitio, como un dragón, quieres generar un token de API para poder utilizarlo personalmente en un script que estés escribiendo. Esto es como un token de acceso personal de GitHub. Estos se crean literalmente a través de una interfaz web. Vamos a mostrar esto.

El segundo caso de uso principal es cuando un tercero quiere hacer peticiones a tu API en nombre de un usuario de tu sistema. Por ejemplo, un nuevo sitio llamado`DragonTreasureOrganizer.com` quiere hacer una petición a nuestra API en nombre de algunos de nuestros usuarios, por ejemplo, buscar los tesoros de un usuario y mostrarlos artísticamente en su sitio. En esta situación, en lugar de que nuestros usuarios generen tokens manualmente y luego... como... los introduzcan en ese sitio, ofrecerás OAuth. OAuth es básicamente un mecanismo para que los usuarios normales den de forma segura tokens de acceso para su cuenta a un tercero. Y así, tu sitio, o en algún lugar de tu infraestructura tendrás un servidor OAuth.

Eso está fuera del alcance de este tutorial. Pero lo importante es que, una vez hecho el OAuth, el cliente de la API acabará con, lo has adivinado, ¡un token de API! Así que no importa en qué viaje estés, si estás haciendo acceso programático, tus usuarios de la API terminarán con un token de acceso. Y tu trabajo consistirá en leerlo y comprenderlo. Haremos exactamente eso.

## ¿JWT vs Almacenamiento en Base de Datos?

Como he mencionado, vamos a mostrar un sistema que permite a los usuarios generar sus propios tokens de acceso. ¿Cómo lo hacemos? De nuevo, hay dos formas principales. ¡Muerte por elección!

La primera es generar algo llamado Token Web JSON o JWT. Lo bueno de los JWT es que no necesitan almacenamiento en bases de datos. Son cadenas especiales que en realidad contienen información en su interior. Por ejemplo, puedes crear una cadena JWT que incluya el id de usuario y algunos ámbitos.

Uno de los inconvenientes de los JWT es que no hay una forma fácil de "cerrar sesión"... porque no hay una forma automática de invalidar los JWT. Les das una fecha de caducidad cuando los creas... pero entonces son válidos hasta entonces... pase lo que pase, a menos que añadas alguna complejidad extra... lo que anula un poco el propósito.

Los JWT están de moda, son populares y divertidos Pero... puede que no los necesites. Son geniales cuando tienes un sistema de inicio de sesión único porque, si ese JWT se utiliza para autenticarse con varios sistemas o API, cada API puede validar el JWT por sí misma: sin necesidad de hacer una petición de API a un sistema central de autenticación.

Así que es posible que acabes utilizando JWT, para lo que existe un bundle estupendo llamado LexikJWTAuthenticationBundle. Los JWT son también el tipo de token de acceso que al final te da OpenID.

En lugar de los JWT, la segunda opción principal es muy sencilla: generar una cadena de token aleatoria y almacenarla en la base de datos. Esto también te permite invalidar los tokens de acceso... ¡simplemente borrándolos! Esto es lo que haremos.

## Generar la entidad

Así que manos a la obra. Para almacenar los tokens de la API, ¡necesitamos una nueva entidad! Busca tu terminal y ejecuta:

```terminal
php ./bin/console make:entity
```

Y llamémosla `ApiToken`. En teoría, podrías permitir a los usuarios autenticarse a través de un formulario de inicio de sesión o HTTP básico y luego enviar una petición POST para crear tokens de API si quieres... pero no lo haremos.

Añade una propiedad `ownedBy`. Esto va a ser un `ManyToOne` a `User` y no `nullable`. Y diré "sí" a la inversa. Así que la idea es que cada `User`pueda tener muchos tokens de API. Cuando se utiliza un token de API, queremos saber con qué `User`está relacionado. Lo utilizaremos durante la autenticación. Llamar a la propiedad`apiTokens` está bien y decir no a la eliminación de huérfanos. Siguiente propiedad: `expiresAt` `datetime_immutable` y diré que sí a `nullable`. Tal vez permitamos que los tokens no caduquen nunca dejando este campo en blanco. La siguiente es `token`, que será una cadena. Voy a establecer la longitud en `68` -veremos por qué en un minuto- y no en`nullable`. Y por último, añade una propiedad `scopes` como tipo `json`. Esto va a ser bastante guay: almacenaremos una matriz de "permisos" que debe tener este token de API. En este caso, tampoco `nullable`. Pulsa intro para terminar.

Muy bien, gira a tu editor. Sin sorpresas: eso ha creado una entidad `ApiToken`... y no hay nada muy interesante dentro de ella:

[[[ code('2f5533961b') ]]]

Así que vamos a hacer la migración correspondiente:

```terminal
symfony console make:migration
```

Gira y echa un vistazo a ese archivo para asegurarte de que se ve bien. ¡Sí! Crea la tabla `api_token`:

[[[ code('3a91c8e82e') ]]]

Ejecuta eso con:

```terminal
symfony console doctrine:migrations:migrate
```

Y... ¡genial! A continuación: vamos a añadir una forma de generar la cadena de tokens aleatorios. Luego, hablaremos de ámbitos y cargaremos nuestros accesorios con algunos tokens de la API.
