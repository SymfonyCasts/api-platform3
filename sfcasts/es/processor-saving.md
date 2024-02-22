# Aprovechar el procesador central

¡Mira cómo vamos! En nuestro procesador de estados, hemos transformado con éxito el `UserApi`en una entidad `User`. Así que ¡vamos a guardarla! Podríamos inyectar el gestor de entidades, persistir y vaciar... y darlo por terminado. Pero prefiero descargar ese trabajo al núcleo`PersistProcessor`. Busca ese archivo y ábrelo.

Hace la persistencia y el vaciado sencillos... pero también tiene una lógica bastante compleja para las operaciones de `PUT`. En realidad, no las vamos a utilizar, pero la cuestión es que es mejor reutilizar esta clase que intentar desarrollar nuestra propia lógica.

## Llamar al Core PersistProcessor

A estas alturas, ya debería resultarte familiar cómo lo hacemos. Añade un`private ProcessorInterface $persistProcessor`... y para que Symfony sepa exactamente qué servicio queremos, incluye el atributo `#[Autowire()]`, con `service` establecido en `PersistProcessor` (en este caso, sólo hay uno para elegir) `::class`.

[[[ code('30e342cb40') ]]]

¡Muy bonito! A continuación, guarda con `$this->persistProcessor->process()` pasando`$entity`, `$operation`, `$uriVariables`, y `$context`... que son todos los mismos argumentos que tenemos aquí arriba.

[[[ code('c090b91c44') ]]]

Ah, y como antes, cuando generamos esta clase, generó `process()` con un tipo de retorno `void`. Eso no es exactamente correcto. No tienes que devolver nada de los procesadores de estado, pero puedes hacerlo. Y lo que devuelvas -en este caso, devolveremos `$data` - se convertirá en última instancia en la "cosa" que se serializa y se devuelve al usuario. Si no devuelves nada, se utilizará`$data`.

[[[ code('e53f469063') ]]]

## Establecer el id en el DTO

Vale, creo que esto debería funcionar (Famosas últimas palabras...).

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... falla. Seguimos recibiendo un error 400, y sigue siendo`Unable to generate an IRI for the item`.

Entonces... ¿qué pasa? Mapeamos el `UserApi` a un nuevo objeto `User` y guardamos el nuevo`User`... lo que hace que Doctrine asigne el nuevo `id` a ese objeto entidad. Pero nunca cogemos ese nuevo id y lo volvemos a poner en nuestro `UserApi`.

Para solucionarlo, después de guardar, añade `$data->id = $entity->getId()`.

[[[ code('ae8dcd02a8') ]]]

Y si lo intentamos ahora...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

sigue fallando... ¡pero esta vez hemos llegado más lejos! La respuesta parece buena. Devuelve un código de estado 201 con la nueva información del usuario. Falla en la parte de la prueba en la que intenta utilizar la contraseña para iniciar sesión. Esto se debe a que nuestra contraseña está actualmente configurada como... `TODO`. Lo arreglaremos en un minuto.

## Manejo de la operación de borrado

Pero primero, cuando establecimos el `processor` en el nivel superior `#[ApiResource]`, éste se convirtió en el procesador de todas las operaciones: `POST`, `PUT`, `PATCH`, y`DELETE`. `POST`, `PUT`, y `PATCH` son todas prácticamente iguales: guardar el objeto en la base de datos. Pero `DELETE` es diferente: no estamos guardando, sino eliminando.

Para ello, consulta `if ($operation instanceof DeleteOperationInterface)`.

[[[ code('056da4ecba') ]]]

Al igual que guardar, eliminar no es difícil... pero sigue siendo mejor descargar este trabajo al procesador de eliminación del núcleo de Doctrine. Así que, aquí arriba, copia el argumento... e inyecta otro procesador: `RemoveProcessor`... y cámbiale el nombre a`$removeProcessor`.

[[[ code('d6f507ebad') ]]]

Aquí abajo, di `$this->removeProcessor->process()` y pásale `$entity`,`$operation`, `$uriVariables`, y `$context` igual que al otro procesador.

[[[ code('86f49faf2a') ]]]

Una cosa clave a tener en cuenta es que vamos a `return null`. En el caso de una operación `DELETE`, no devolvemos nada en la respuesta... lo que conseguimos devolviendo `null` desde aquí. No tengo una prueba preparada para esto, pero haremos un acto de fe y supondremos que funciona. ¡Envíalo!

[[[ code('00b4dbf920') ]]]

## Cifrar la contraseña

Sólo nos queda un problema por resolver: cifrar la contraseña. Ya lo hemos hecho antes, así que no pasa nada. Antes de hacer demasiado aquí, abre `UserApi`... y añade un`public ?string $password = null`... con un comentario. Esto siempre contendrá null o la contraseña "en texto plano" si el usuario envía una. Nunca vamos a necesitar manejar la contraseña "hash" en nuestra API, así que no necesitamos espacio para ello... ¡lo cual está muy bien!

De vuelta en el procesador, `if ($dto->password)`, entonces sabemos que tenemos que aplicar el hash y establecerlo en el usuario. Si se está creando un nuevo usuario, siempre se establecerá... pero al actualizar un usuario, haremos que este campo sea opcional. Si no se establece, no haremos nada, de modo que se mantendrá la contraseña actual del usuario.

Para hacer el hash, arriba, añade un argumento más:`private UserPasswordHasherInterface $userPasswordHasher`. Luego, abajo,`$entity->setPassword()` se establece en `$this->userPasswordHasher->hashPassword()`, pasando a`$entity` (el objeto `User` ) y la contraseña simple: `$dto->password`.

[[[ code('545fd04d31') ]]]

Uf. Intentemos de nuevo la prueba. Y... falla... con

> La anotación "@El" de la propiedad `UserApi::$password` nunca se importó.

Así que... me he tropezado con el teclado y he añadido un `@` de más. Elimínalo... e inténtalo de nuevo:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

¡Pasa! ¡Lo que significa que se ha registrado completamente utilizando esa contraseña! Aunque, oh oh, mira la respuesta JSON volcada: esto es después de que `POST` creara el usuario. En la respuesta JSON, se incluye la propiedad `password` en texto plano que el usuario acaba de establecer. ¡Vaya!

## El flujo de una petición de escritura

Desglosemos esto. Nuestro proveedor de estado se utiliza para todas las operaciones `GET`, así como para la operación `PATCH`. Y fíjate, no vamos a establecer nunca la propiedad `password`. No queremos devolver ese campo en el JSON, así que, correctamente, no lo estamos mapeando desde nuestra entidad a nuestro DTO. ¡Eso está bien!

Pero la operación `POST` es la única situación en la que nunca se llama al proveedor. Estos datos se deserializan directamente en un nuevo objeto `UserApi` y se pasan a nuestro procesador. Esto significa que nuestro DTO sí tiene establecida la contraseña simple... Y, en última instancia, ese objeto DTO es lo que se serializa y se devuelve al usuario.

Esto es una forma larga de decir que, en `UserApi`, esta contraseña debe ser un campo de sólo escritura. El usuario nunca debería poder leerla. A continuación: hablemos de cómo podemos hacer personalizaciones como ésta dentro de`UserApi`, evitando la complejidad de los grupos de serialización.
