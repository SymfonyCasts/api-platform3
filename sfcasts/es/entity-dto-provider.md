# Entidades, DTO y el objeto "central

Esto de la clase entidad parece casi demasiado bueno para ser verdad. Nos da toda la flexibilidad, en teoría, de una clase personalizada, a la vez que reutiliza toda la lógica central del proveedor y procesador Doctrine. Pero mantén la calma, porque hay dos inconvenientes, aunque solucionables.

El más importante es que no se nos permite tener nombres de propiedades personalizados. Esto provocará un error al intentar serializar. En segundo lugar, aún no lo he mencionado, pero las operaciones de escritura -como `POST` o `PATCH` - no funcionan en absoluto. Bueno... si nosotros, publicáramos en nuestra ruta, los datos se deserializarían... pero no se guardarían en la base de datos.

## El problema de las operaciones de escritura

Podemos intentarlo porque ya tenemos una prueba para ello. Abre `UserResourceTest`y, aquí abajo, copia `testPostToCreateUser()`. Gira y ejecútalo con:

```terminal
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡Error 400! Abre eso. Uh oh:

> No se ha podido generar un IRI para el elemento de tipo `App\ApiResource\UserApi`.

Esto es lo que ocurre El serializador deserializa este JSON en un objeto `UserApi`. ¡Yupi! Ese objeto `UserApi` se pasa entonces al procesador de persistencia del núcleo de Doctrine: la cosa que normalmente guarda las entidades en la base de datos. Pero como `UserApi` no es una entidad, ese procesador no hace... nada. Entonces, cuando `UserApi` se serializa de nuevo a JSON, el `$id` sigue siendo nulo -porque nunca se guardó nada en la base de datos- y... por tanto, no se puede generar el IRI para él.

Podríamos solucionarlo creando un procesador de estado personalizado para `UserApi` que lo guarde en la base de datos. Pero incluso si lo hiciéramos, las operaciones de escritura, como `POST` y`PATCH`, no están diseñadas para funcionar directamente con esta solución `entityClass`. La razón... es un poco técnica, pero importante.

## Comprender el "objeto central" de una operación

Internamente, para cada petición de API, API Platform tiene un objeto central sobre el que está trabajando. Si obtenemos un único elemento, ese objeto central es ese único elemento. Y eso es muy importante. Se utiliza en varios lugares, como el atributo `security`: cuando utilizamos `is_granted`, la variable `object` será ese objeto "central". Por ejemplo, si hacemos una petición `Patch()`, eso significa que estamos editando un tesoro dragón... así que el objeto central será una entidad `DragonTreasure`. ¡Muy fácil!

¿Cuál es el truco? Bueno, cuando utilices la solución `entityClass` con una operación de lectura (es decir, una de estas peticiones `GET` ), el objeto central será la entidad. Así que la entidad `User` será el objeto central. Pero con una operación de escritura (sobre todo, la operación `POST` para crear un nuevo usuario), ese objeto central será de repente un objeto `UserApi`. Esto provoca una grave incoherencia: el objeto central será a veces una entidad... y otras veces el DTO. Buena suerte haciendo un sistema `security` que funcione con ambos... y no sea completamente confuso.

Además, cuando la entidad `User` es el objeto central, es cuando nos encontramos con el problema que nos impide tener campos personalizados en nuestro DTO.

Así que, si pudiéramos hacer que el `UserApi` fuera el objeto central en todos los casos, entonces tendríamos una seguridad coherente... y también podríamos solucionar nuestro gran problema de las propiedades personalizadas.

¿Cómo podemos conseguirlo? Escribiendo un proveedor de estado personalizado que devuelva objetos`UserApi`. Piénsalo: como el proveedor principal de la colección Doctrine devuelve objetos de entidad `User`, éstos se convierten en los objetos centrales. Si, en lugar de eso, devolvemos objetos `UserDto`, problema resuelto. Si todo esto aún no tiene sentido, no me sorprende. Vamos a recorrerlo paso a paso.

## Decorar el proveedor de estado central

Empieza por ejecutar:

```terminal
php bin/console make:state-provider
```

Llámalo `EntityToDtoStateProvider`. Mi objetivo es crear un proveedor de estado genérico que funcione para todos los casos en los que tengamos una clase de recurso API que extraiga datos de una entidad. Por lo tanto, mantendremos el código específico del usuario fuera de aquí.

[[[ code('a6aeac9b54') ]]]

En `UserApi`, establece `provider` en `EntityToDtoStateProvider`.

[[[ code('b452854c45') ]]]

En `EntityToDtoStateProvider`, podríamos consultar manualmente nuestros objetos de entidad `User`, convertirlos en objetos `UserApi`... y luego devolverlos. Pero ¡eso es lo que intentamos evitar! Queremos seguir reutilizando toda esa bonita lógica de consulta de Doctrine: ésa es la belleza de `stateOptions`.

Para ello, como hemos hecho antes, vamos a decorar el proveedor principal de Doctrine. Digamos `public function __construct()` con`private ProviderInterface $collectionProvider`. Y para ayudar a Symfony a saber cuál debe pasar, utiliza el atributo `#[Autowire()]` y di `service: CollectionProvider`(asegúrate de obtener el de Doctrine ORM), seguido de `::class`.

[[[ code('b452854c45') ]]]

Aquí abajo, añade `$entities = $this->collectionProvider->provide()`, pasando`$operation`, `$uriVariables`, y `$context`. Abajo, `dd($entities)`

[[[ code('cd465e6798') ]]]

¡A ver qué pasa! Vuelve, actualiza la ruta y... ¡ya está! Estamos llamando al proveedor principal, y está devolviendo un objeto paginador. Para ver lo que se esconde dentro de ese `Paginator`, di `dd(iterator_to_array($entities))`.

[[[ code('610b7a07dc') ]]]

De vuelta por aquí... esto muestra cinco objetos de entidad `User`.

En este punto, nuestro nuevo proveedor no está haciendo... nada especial. Si devolviéramos`$entities`, estaríamos exactamente donde empezamos: con las entidades `User` como objeto central. Nuestro objetivo es devolver objetos `UserApi`... y eso es lo que vamos a hacer a continuación.
