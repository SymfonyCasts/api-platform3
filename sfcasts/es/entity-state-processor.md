# DTO -> Procesador del Estado de la Entidad

Ya hemos comprobado el aspecto "proveedor" de nuestra nueva clase `UserApi`. Así que vamos a centrarnos en el procesador para poder guardar las cosas. Y tenemos algunas pruebas bastante encantadoras para nuestras rutas `User`. Abrir`UserResourceTest`.

## Anatomía del procesador de peticiones y estados

Vale, `testPostToCreateUser()`, publica algunos datos, crea el usuario y, a continuación, comprueba que la contraseña que hemos publicado funciona al iniciar sesión. Añade`->dump()` para ayudarnos a ver lo que ocurre. A continuación, copia el nombre del método y ejecútalo:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

No te sorprendas... falla:

> El código de estado de respuesta actual es 400, pero se esperaba 201.

El volcado es realmente útil. ¡Es nuestro error favorito!

> No se ha podido generar un IRI para el elemento de tipo `UserApi`.

Ya hemos hablado de lo que ocurre: el JSON se deserializa en un objeto `UserApi`. ¡Bien! Entonces se llama al núcleo de la Doctrine `PersistProcessor` porque ése es el `processor` por defecto cuando se utiliza `stateOptions`. Pero... como nuestro`UserApi` no es una entidad, `PersistProcessor` no hace nada. Por último, la API Platform vuelve a serializar el `UserApi` en JSON... pero sin el `id`poblado, no consigue generar el IRI.

¡Fíjate! En `UserApi`, por defecto temporalmente `$id` a `5`. Cuando intentamos la prueba ahora...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Parece que funciona. Vale, falla... pero sólo después... aquí abajo en`UserResourceTest` línea 33. El POST se realiza correctamente.

## Creación del procesador de estado

Mira la respuesta de arriba, está devolviendo este JSON de usuario. Pero, aún así, no se está guardando nada. Vuelve a cambiar el id a null. Tenemos que solucionar esta falta de guardado creando un nuevo procesador de estado. Así que gíralo y ejecútalo:

```terminal
php bin/console make:state-processor
```

Llámalo `EntityClassDtoStateProcessor` porque, de nuevo, vamos a hacer que esta clase sea genérica para que funcione con cualquier clase de recurso de la API que esté vinculada a una entidad Doctrine. La utilizaremos más adelante para `DragonTreasure`.

[[[ code('74612c8838') ]]]

Con el procesador vacío generado, ve a conectarlo en `UserApi` con`processor: EntityClassDtoStateProcessor::class`.

[[[ code('83860cae49') ]]]

A partir de ahora, cada vez que hagamos POST, PATCH o DELETE de este recurso, se llamará a este procesador.

## Volver a asignar el DTO a una entidad

Pero, ¿qué es exactamente esta variable `$data`? Puede que lo adivines, pero por si acaso, vamos a `dd($data)`... y volvamos a ejecutar la prueba.

[[[ code('83860cae49') ]]]

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Sí, ¡es un objeto `UserApi`! El JSON que enviamos se deserializa en este objeto `UserApi`, y luego se pasa a nuestro procesador de estado. El objeto `UserApi` es el "objeto central" dentro de API Platform para esta petición.

Nuestro trabajo en el procesador de estado es sencillo pero importante: convertir este `UserApi`de nuevo en una entidad `User` para que podamos guardarlo. Digamos que `assert($data instanceof UserApi)`y, dentro, `$entity =` se establecen en una nueva función de ayuda: `$this->mapDtoToEntity($data)`. Debajo, `dd($entity)`.

[[[ code('a24e17818d') ]]]

Luego ve a añadir ese nuevo `private function mapDtoToEntity()`, que aceptará un argumento`object $dto` y devolverá otro `object`.

De nuevo, sabemos que esto realmente aceptará un objeto `UserApi` y devolverá una entidad `User`... pero estamos intentando mantener esta clase genérica para poder reutilizarla más adelante. Aunque vamos a tener algo de código específico de usuario aquí abajo temporalmente. De hecho, para ayudar a nuestro editor, añade otro `assert($dto instanceof UserApi)`.

[[[ code('ef0007c3a8') ]]]

## Consulta de la entidad existente

Tenemos que pensar en dos casos diferentes. El primero es cuando vamos a crear un usuario totalmente nuevo. En ese caso, `$dto` tendrá un id `null`. Y eso significa que deberíamos crear un objeto `User` nuevo. El otro caso es si hiciéramos, por ejemplo, una petición a `PATCH` para editar un usuario. En ese caso, el proveedor de elementos cargará primero esa entidad `User` de la base de datos... nuestro proveedor la convertirá en un objeto `UserApi` con `id` igual a `6`... y eso nos lo pasará finalmente aquí. Si el `id` es 6... no queremos crear un nuevo objeto `User`: queremos consultar la base de datos en busca del `User` existente. Nuestro trabajo consiste en manejar ambas situaciones.

Deshaz los cambios en la prueba para no romper nada... y ahora, `if``$dto->id` , tenemos que consultar por un `User` existente. Para ello, en la parte superior, añade un constructor con `private UserRepository $userRepository`. 

[[[ code('7e5f1f358e') ]]]

Aquí abajo, digamos `$entity = $this->userRepository->find($dto->id)`.

Si no encontramos ese `User`, lanza una gran excepción gigante que provocará un error 500 con `Entity %d not found`.

[[[ code('1940784361') ]]]

Puede que te preguntes:

> ¿No debería esto desencadenar un error 404 en su lugar?

La respuesta, en este caso, es no. Si nos encontramos en esta situación, significa que el proveedor de estado del artículo ya ha consultado con éxito un `User` con este id. Así que no debería haber forma de que, de repente, no lo encontremos. Hay algunas excepciones a esto, como si permitieras a tu usuario cambiar su `id`... o si permitieras a los usuarios crear objetos completamente nuevos y establecer el id manualmente... pero para la mayoría de las situaciones, incluida la nuestra, si esto ocurre, algo ha ido mal.

A continuación, si no tenemos un `id`, digamos `$entity = new User()`.

[[[ code('8b8036b3fb') ]]]

¡Listo! En ambos casos, aquí abajo, vamos a mapear el objeto `$dto` al objeto`$entity`. Este código es aburrido... así que lo haré más rápido. Para la contraseña, pon un `TODO` temporalmente porque aún tenemos que hacer el hash. Añade también un `TODO`para `handle dragon treasures`. Céntrate en lo fácil... y al final,`return $entity`.

[[[ code('4c79b2c5b0') ]]]

Si hemos hecho las cosas bien, cogeremos el `UserApi`, lo transformaremos en un `$entity` y lo volcaremos. Vuelve a ejecutar la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡404! Veamos qué ha pasado aquí. Ah... claro. No he vuelto a montar el test. Esto debería ser `->post('/api/users')`. Inténtalo de nuevo y... ¡ya está! ¡Ahí está nuestro objeto entidad `User` con el correo electrónico y el nombre de usuario transferidos correctamente!

Siguiente: Guardemos esto aprovechando el núcleo de Doctrine `PersistProcessor` y`RemoveProcessor`. También nos encargaremos del hashing de la contraseña. Al final, nuestras pruebas de usuario estarán pasando con éxito.
