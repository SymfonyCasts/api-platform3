# Validación y seguridad del DTO

¡Hablemos de validación! Cuando `->post()` a nuestra ruta, el objeto interno será nuestro objeto `UserApi`... lo que significa que eso es lo que se validará. Observa. No envíes ningún campo a la petición `POST`... y ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Oh uh: ¡error 500! Y... Seguro que adivinas por qué. Dice

> `User::setEmail()`: El argumento nº 1 (`$email`) debe ser de tipo string

Procedente de nuestro procesador de estado en la línea 59. Como no hay ninguna restricción de validación en `UserApi`, la propiedad `email` sigue siendo `null`. Entonces, aquí en la línea 59, intentamos transferir ese nulo `email` a nuestra entidad. No le gusta, hay una breve pelea a puñetazos, y vemos este error. E incluso si aceptara un valor nulo, acabaría fallando en la base de datos porque allí no se permite que el correo electrónico sea nulo.

Nos falta validación. Afortunadamente, es fácil de añadir... una vez que sepas que la validación se producirá en el objeto `UserApi`, no en la entidad.

## Configurar las operaciones

Pero antes de desbocarnos y añadir restricciones, vamos a especificar las `operations`... para que sólo tengamos las que necesitamos: `new Get()`, `new GetCollection()`, `new Post()`... le añadiremos algo de configuración en un momento... así como `new Patch()` y `new Delete()`.

Antes, cuando nuestra entidad `User` era la `#[ApiResource]`, la operación `Post()` tenía una opción extra `validationContext` con `groups` establecida en `Default` y`postValidation`. Gracias a ello, cuando se producía la operación `Post()`, se ejecutaban todos los validadores normales más los que estuvieran en este grupo`postValidation`. Veremos por qué necesitamos esto dentro de un momento.

## Añadir las restricciones

Vale, ¡hora de añadir restricciones! `$id` ni siquiera es escribible... queremos que `$email` sea `#[NotBlank]`... y que sea un `#[Email]`. Queremos que `$username` sea `#[NotBlank]`... entonces `$password` es interesante. `$password` debería poder estar en blanco si estamos haciendo una petición `PATCH` para editarlo... pero ser obligatorio en una petición `POST`. Para conseguirlo, añade `#[NotBlank]` pero con una opción `groups` establecida en`postValidation`.

Esta restricción sólo se ejecutará cuando estemos validando el grupo `postValidation`... lo que significa que sólo se ejecutará para la operación `Post()`.

Vale, ¡con esto debería bastar! Ejecuta ahora la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡un bonito código de estado 422!

## ¿Constricción de entidad única?

Por cierto, otra de las restricciones de validación que teníamos antes en la entidad `User`era `#[UniqueEntity]`. Eso impedía que alguien creara dos usuarios con el mismo `email` o `username`. No la tenemos en `UserApi`, pero deberíamos. La restricción `#[UniqueEntity]`, por desgracia, sólo funciona en entidades... así que tendríamos que crear un validador personalizado para tenerla en `UserApi`. No vamos a preocuparnos por eso, pero quería señalarlo.

De todos modos, de vuelta a la prueba, vuelve a añadir los campos. Validación, ¡comprobada!

## Añadir seguridad

Lo siguiente que tenemos que volver a añadir -código que antes vivía en `User` - es la seguridad. Aquí arriba, en el nivel API, para todo el recurso, se requiere `is_granted("ROLE_USER")`.

Esto significa que tenemos que iniciar sesión para utilizar cualquiera de las operaciones de este recurso... por defecto. Entonces anulamos eso. En `Post()`, definitivamente no podemos estar conectados todavía porque estamos registrando a nuestro usuario. Digamos que`security` está configurado como `is_granted("PUBLIC_ACCESS")`, que es un atributo especial que siempre pasará.

Aquí abajo para `Patch()`, teníamos `security('is_granted("ROLE_USER_EDIT")')`.

En nuestra aplicación, hemos decidido que es necesario tener este tole especial para poder editar usuarios.

De acuerdo Vamos a ejecutar todas las pruebas para `User`:

```terminal
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Y... oh. ¡No está mal! ¡Tres de cuatro! El fallo viene de`testTreasuresCannotBeStolen()`. ¡Eso no suena bien!

Si lo comprobamos... ésta es una prueba interesante: `->patch()` para actualizar un `$user`, y luego intentamos establecer la propiedad `dragonTreasures` a un tesoro que es propiedad de un usuario diferente. Puedes ver que este `$dragonTreasure` es propiedad de `$otherUser`... pero estamos actualizando `$user`.

Lo que estamos intentando hacer es robar este `$dragonTreasure` de `$otherUser` y convertirlo en parte de `$user`. A los dragones no les gusta que les roben, así que estamos afirmando que se trata de un código de estado 422 ... porque antes teníamos un validador personalizado que lo impedía.

Bueno, sigue existiendo -es este `TreasuresAllowedOwnerChangeValidator` -, pero no se aplica a `UserApi`... y hay que actualizarlo para que funcione con él. Lo haremos más adelante.

Y lo que es más importante ahora mismo, ¡la propiedad `dragonTreasures` ni siquiera es escribible! En `UserApi`, encima de `$dragonTreasures`, tenemos `writable: false`. Dentro de un rato, vamos a cambiar eso para que podamos volver a escribir `dragonTreasures`. Y cuando lo hagamos, traeremos de vuelta ese validador y nos aseguraremos de que esta prueba pasa.

Siguiente: Si observas el procesador o el proveedor que hemos creado, estas clases son bastante genéricas. Casi podrían funcionar para `UserApi` y una futura clase`DragonTreasureApi`... y cualquier otra clase DTO que creemos que esté vinculada a una entidad. La única parte que es específica de `User` es el código que mapea hacia y desde la entidad `User` y la clase `UserApi`.

Si pudiéramos manejar ese mapeo... en algún sistema que viva fuera de nuestro proveedor y procesador... podríamos reutilizarlos. ¡Hagamos esto realidad a continuación!
