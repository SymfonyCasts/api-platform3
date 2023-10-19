# MicroMapper: Mapeo DTO Central

Hacer la transformación de datos, de `UserApi` a la entidad `User`, o de la entidad `User`a `UserApi`, es la única parte de nuestro proveedor y procesador que no es genérica y reutilizable. ¡Rayos! Si no fuera por ese código, podríamos crear una clase `DragonTreasureApi` y volver a hacer todo esto sin apenas trabajo Afortunadamente, se trata de un problema bien conocido llamado "mapeo de datos".

Para este tutorial, probé algunas bibliotecas de mapeo de datos, sobre todo`jane-php/automapper-bundle`, que es superrápida, avanzada y divertida de usar. Sin embargo, no es tan flexible como yo necesitaba... y ampliarla parecía complejo. Sinceramente... Me quedé atascado en algunos sitios... aunque sé que se está trabajando para que este paquete sea aún más amigable.

El caso es que no vamos a utilizar esa biblioteca. En su lugar, para manejar el mapeo, he creado un pequeño paquete propio. Es fácil de entender y nos da un control total... aunque no sea tan genial como el automapper de Jane.

## Instalar micromapper

¡Vamos a instalarlo! Ejecuta:

```terminal
composer require symfonycasts/micro-mapper
```

Eso suena a superhéroe. Ahora que lo tenemos en nuestra aplicación, tenemos un nuevo servicio micromapper que sirve para convertir datos de un objeto a otro. Empecemos por utilizarlo en nuestro procesador.

## Utilizar el servicio MicroMapper

Arriba, autocablea un `private MicroMapperInterface $microMapper`. Y aquí abajo, para todas las cosas del mapeo, copia la lógica existente, porque la necesitaremos en un minuto. Sustitúyela por `return $this->microMapper->map()`. Éste tiene dos argumentos principales: El objeto `$from`, que será `$dto` y la clase toClass, así que `User::class`.

¡Listo! Bueno... no del todo, pero probemos a ejecutar `testPostToCreateUser` de todos modos.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... falla con un error 500. Lo interesante es lo que dice ese error 500. Vamos a "Ver la fuente de la página" para poder leerlo mejor. Dice

> No se ha encontrado ningún mapeador para `App\UserResource\UserApi` -&gt `App\Entity\User`

Y esto viene de `MicroMapper`. Esto básicamente dice:

> ¡Eh, no sé cómo convertir un objeto `UserApi` en un objeto `User`! ¡Alto!

## Crear un mapeador

MicroMapper no es magia... en realidad es todo lo contrario. Para enseñar a MicroMapper cómo hacer esta conversión, tenemos que crear una clase que explique lo que queremos. Eso se llama una clase mapeadora. ¡Y éstas son divertidas!

Déjame empezar cerrando algunas cosas... y luego creando un nuevo directorio `Mapper/` en `src/`. Dentro de él, añade una nueva clase PHP llamada... qué tal`UserApiToEntityMapper`, porque vamos a pasar de `UserApi` a la entidad `User`.

Esta clase necesita 2 cosas. Primero, implementar `MapperInterface`. Y segundo, encima de la clase, para describir a qué y desde qué se está mapeando, necesitamos un atributo `#[AsMapper()]`con `from: UserApi::class` y `to: User::class`.

Para ayudar a la interfaz, ve a "Generar código" (o "comando" + "N" en un Mac) y genera los dos métodos que necesita: `load()` y `populate()`. Para empezar, vamos a `dd($from, $toClass)`.

Ahora, sólo con crear esto y darle `#[AsMapper]`, cuando utilicemos MicroMapper para hacer esta transformación, debería llamar a nuestro método `load()`. ¡Veamos si lo hace!

Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡ya está! Ahí está el objeto `UserApi` que le estamos pasando, y nos está pasando la clase `User`. El propósito de `load()` es cargar el objeto `$toClass` y devolverlo, por ejemplo, consultando una entidad `User` o creando una nueva.

Para hacer la consulta, arriba, añade `public function __construct()` e inyecta el`UserRepository $userRepository` normal. Aquí abajo, esto contendrá el mismo código que vimos antes. Me gusta decir `$dto = $from` y `assert($dto instanceof UserApi)`. Eso ayuda a mi cerebro y a mi editor.

A continuación, si nuestro `$dto` tiene un `id`, entonces llama a `$this->userRepository->find($dto->id)`. Si no, crea un objeto de marca `new User()`.

Así de sencillo. Y si, por alguna razón, no tenemos un `$userEntity`,`throw new \Exception('User not found')`, similar a lo que hicimos antes. Aquí abajo,`return $userEntity`.

Así que hemos inicializado nuestro objeto `$to` y lo hemos devuelto. Y ese es el objetivo de`load()`: hacer el menor trabajo posible para obtener el objeto `$to`... pero sin rellenar los datos.

Internamente, después de llamar a `load()`, el micro mapeador llamará a `populate()`y nos pasará el objeto entidad `User` que acabamos de devolver. Para ver esto, vamos a`dd($from, $to)`.

Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Perfecto! Aquí está nuestro objeto "de" `UserApi`, y la nueva entidad `User`.

Ahora... puede que te estés preguntando por qué tenemos un método `load()` y un método `populate()`... cuando parece que podrían ser un solo método. ¡Y en gran parte tendrías razón! Pero hay una razón técnica por la que están separados, y será útil más adelante cuando hablemos de las relaciones. Pero por ahora, puedes imaginar que estos dos métodos son en realidad un solo proceso continuo: se llama a `load()` y luego a `populate()`.

Y no te sorprendas, aquí es donde tomaremos los datos del objeto `$from` y los pondremos en el objeto `$to`. Una vez más, para mantenerme cuerdo, diré `$dto = $from`y `assert($dto instanceof UserApi)`... luego`$entity = $to` y `assert($entity instanceof User)`.

El código de aquí abajo va a ser muy aburrido... así que lo pegaré. En la parte inferior, `return $entity`.

Aquí estamos utilizando `$this->userPasswordHasher`... así que también tenemos que asegurarnos, en la parte superior, de añadir `private UserPasswordHasherInterface $userPasswordHasher`.

Así que éste es básicamente el mismo código que teníamos antes... pero en un lugar diferente.

¡Veamos qué opina el test!

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Pasa! ¡Esto es enorme! Hemos descargado este trabajo a nuestro mapeador... lo que significa que nuestro procesador es casi completamente genérico. Ahora podemos eliminar el`UserPasswordHasher` que ya no necesitamos... y el `UserRepository` de aquí arriba. Incluso podemos eliminar esas declaraciones `use`.

Seguimos teniendo que escribir el código de mapeo, pero ahora vive en una bonita ubicación central.

## Mapear la otra dirección

Listo para repetir esto para el proveedor. Cierra el procesador... y ábrelo. Esta vez, iremos de la entidad `User` a `UserApi`. Copia todo este código, bórralo y, como antes, autocablea `MicroMapperInterface $microMapper`. Aquí abajo, esto se simplifica a `return $this->microMapper->map()` pasando de nuestro`$entity` a `UserApi::class`.

¡Genial! Si intentáramos esto ahora, obtendríamos un error 500 porque no tenemos un mapeador para ello. De vuelta a `src/Mapper/`, crea una nueva clase llamada `UserEntityToApiMapper`... implementa `MapperInterface`... y encima de la clase, añade `#[AsMapper()]`. En este caso, vamos a `from: User::class`, `to: UserApi::class`.

Implementa los dos métodos que necesitamos... y empezamos prácticamente igual que antes, con `$entity = $from` y `assert($entity instanceof User)`.

Aquí abajo, para crear el DTO, no necesitamos hacer ninguna consulta. Siempre vamos a instanciar un nuevo `UserApi()`. Ponle el ID con`$dto->id = $entity->getId()`... y luego `return $dto`.

Vale, el trabajo del método `load()` es realmente crear el objeto `$to` y... al menos asegurarnos de que tiene su identificador si lo tiene.

Todo lo demás que tenemos que hacer está aquí abajo, en `populate()`. Empieza de la forma habitual:`$entity = $from`, `$dto = $to` y dos asserts: `assert($entity instanceof User)`
y `assert($dto instanceof UserApi)`. Debajo, utiliza el código exacto que teníamos antes. Sólo estamos transfiriendo los datos. En la parte inferior, `return $dto`.

¡Uf! ¡Vamos a probarlo! Ve a tu navegador, actualiza esta página y... oh...

> Se requiere autenticación completa para acceder a este recurso.

Por supuesto. Eso es porque hemos añadido seguridad Vuelve a la página de inicio, haz clic en este acceso directo de nombre de usuario y contraseña... boop... y ahora intenta actualizar esa página. ¡Funciona! Aunque faltan algunos datos, lo cual es culpa mía.

He dicho `$dto = new UserApi()`. Así que en lugar de modificar el objeto `$to` que me pasan, creé uno nuevo... y el original no se modificó. Ya está. Si lo vuelvo a intentar... mucho mejor.

¡Así que esto es enorme gente! Nuestro proveedor y procesador son ahora genéricos! Vamos a terminar el proceso de hacer que funcionen para cualquier clase de recurso API a continuación
