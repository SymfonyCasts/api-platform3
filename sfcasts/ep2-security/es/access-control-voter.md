# Votante de seguridad

Nuestra seguridad se está convirtiendo en una casa de locos, lo que no me gusta. Quiero que mi lógica de seguridad sea simple y centralizada. La forma de hacerlo en Symfony es con un votador. Vamos a crear uno.

En la línea de comandos, ejecuta:

```terminal
php ./bin/console make:voter
```

Llámalo `DragonTreasureVoter`. Es bastante común tener un votante por entidad para la que necesites lógica de seguridad. Así que este votante tomará todas las decisiones relacionadas con `DragonTreasure`: puede el usuario actual editar una, borrar una, ver una: lo que eventualmente necesitemos.

Ve a abrirlo: `src/Security/Voter/DragonTreasureVoter.php`:

[[[ code('7292be53c6') ]]]

Antes de hablar de esta clase, déjame mostrarte cómo la utilizaremos. En`DragonTreasure`, vamos a seguir utilizando la función `is_granted()`. Pero para el primer argumento, pasa `EDIT`... que es sólo una cadena que me estoy inventando: ya verás cómo se utiliza en el votante. Luego pasa `object`:

[[[ code('9a8585b8fc') ]]]

Normalmente pasamos a `is_granted()` un único argumento: ¡un papel! Pero también puedes pasarle cualquier cadena aleatoria como `EDIT`... siempre que tengas un votante configurado para manejar eso.  Si tu votante necesita información adicional para tomar su decisión, puedes pasársela como segundo argumento.

A grandes rasgos, estamos preguntando al sistema de seguridad si el usuario actual tiene permiso o no para `EDIT` este objeto `DragonTreasure`. `DragonTreasureVoter` tomará esa decisión.

Copia esto y pégalo abajo para `securityPostDenormalize`:

[[[ code('c9c6106770') ]]]

## Cómo funcionan los votantes

Así que el asunto es el siguiente: cada vez que se llama a `is_granted()` -desde cualquier lugar, no sólo desde API Platform- Symfony recorre una lista de clases "votantes" e intenta averiguar cuál de ellas sabe cómo tomar esa decisión. Cuando comprobamos un rol, hay un votante existente que sabe cómo manejarlo. En el caso de `EDIT`, no hay ningún votante principal que sepa cómo manejarlo. Así que haremos que `DragonTreasureVoter` pueda manejarlo.

Para determinar quién puede manejar una llamada a `isGranted`, Symfony llama a `supports()` en cada votante pasándole los mismos dos argumentos. En nuestro caso, `$attribute` será`EDIT` y `$subject` será el objeto `DragonTreasure`:

[[[ code('66bcad11c0') ]]]

MakeBundle generó un votante que se encarga de comprobar si podemos "editar" o "ver" un `DragonTreasure`. Ahora mismo no necesitamos esa "vista", así que la borraré. A continuación, cambiaré esto por una instancia de `DragonTreasure` y volveré a escribir el final y le daré al tabulador para añadir la declaración `use`... sólo para limpiar las cosas:

[[[ code('3061682697') ]]]

Así, si alguien llama a `isGranted()` y le pasa la cadena `EDIT` y un objeto `DragonTreasure`, sabremos cómo tomar esa decisión.

Ah, y tengo que cambiar el valor de la constante a `EDIT` para que coincida con la cadena `EDIT` que pasamos a `is_granted()`.

Si devolvemos `true` desde `supports()`, Symfony llamará entonces a `voteOnAttribute()`. Muy sencillo: devolvemos `true` si el usuario debe tener acceso, `false` en caso contrario.

Para empezar, basta con `return false`:

[[[ code('7b72c7feb7') ]]]

Si hemos jugado bien nuestras cartas, nuestro votante se abalanzará como un superhéroe hiperactivo cada vez que hagamos una petición PATCH y cerrará de golpe la puerta de acceso. Antes de probar esa teoría, elimina el caso "vista" de aquí abajo:

[[[ code('b783f0106f') ]]]

Bien, ¡asegurémonos de que nuestras pruebas fallan! Ejecuta:

```terminal
symfony php bin/phpunit
```

Y... ¡sí! Fallan dos pruebas: ambas porque se deniega el acceso. Nuestro votante está siendo llamado.

## Añadir la lógica del votante

De vuelta a la clase, a `voteOnAttribute()` se le pasa el atributo - `EDIT` - el`$subject` - un objeto `DragonTreasure` y un `$token`, que es una envoltura alrededor del objeto `User` actual. Así que primero comprobamos que el usuario está autenticado.

Después, `assert()` que `$subject` es una instancia de `DragonTreasure` porque este método sólo debería llamarse cuando `supports()` devuelve `true`:

[[[ code('a7f395b935') ]]]

Principalmente escribo esto para que mi editor sepa que `$subject` es una `DragonTreasure`:`assert()` es una forma práctica de hacerlo.

La declaración `switch` sólo tiene un `case` en este momento. Y aquí es donde vivirá nuestra lógica. Muy sencillo: si `$subject` - que es el `DragonTreasure` - `->getOwner()`es igual a `$user`, entonces devuelve `true`. En caso contrario, será igual a `break` y devolverá`false`:

[[[ code('b8196c0110') ]]]

Ésta no es toda la lógica que necesitamos, ¡pero es un buen comienzo!

Prueba ahora las pruebas:

```terminal-silent
symfony php bin/phpunit
```

¡Un fallo menos!

## Comprobación de roles en el votante

¿Qué es lo siguiente? Bueno, no tenemos una prueba para ello, pero si nos autenticamos con un token de la API, para editar un tesoro, necesitas `ROLE_TREASURE_EDIT`, que puedes obtener a través del ámbito del token.

Así que, en el votante, tenemos que comprobar si el usuario tiene ese rol. Añade un método `__construct()`y autoconecta `Security` - el del SecurityBundle - `$security`:

[[[ code('e475fdfaf0') ]]]

Entonces, a continuación, antes de comprobar el propietario, si no`$this->security->isGranted('ROLE_TREASURE_EDIT')`, entonces devuelve definitivamente`false`:

[[[ code('971ba34699') ]]]

La última prueba que falla es comprobar que un administrador puede parchear para editar cualquier tesoro. Como ya hemos inyectado el servicio `Security`, esto es fácil.

Hagamos como si los usuarios administradores pudieran hacer cualquier cosa. Así que por encima de `switch`, si `$this->security->isGranted('ROLE_ADMIN')`, entonces devuelve `true`:

[[[ code('e3e67e67cf') ]]]

Momento de la verdad:

```terminal-silent
symfony php bin/phpunit
```

¡Voilà! Nuestra lógica ha encontrado un hogar acogedor dentro del votante, la expresión `security`es ahora tan sencilla que casi da miedo, y hemos conseguido escribir nuestra lógica en PHP.

A continuación: vamos a explorar la posibilidad de ocultar determinados campos en la respuesta en función del usuario.
