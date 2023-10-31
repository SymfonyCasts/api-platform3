# Activar un "Publicar

Sólo nos queda un fallo de prueba: está en `testPublishTreasure`. Vamos a comprobarlo. Vale, esto comprueba que se crea una notificación en la base de datos cuando el estado de un tesoro cambia de `'isPublished' => false` a`'isPublished' => true`. Antes, implementábamos esto mediante un procesador de estado personalizado.

Pero ahora, ¡podríamos ponerlo en nuestra clase mapeadora! En `DragonTreasureApiToEntityMapper`, podríamos comprobar si la entidad era `'isPublished' => false` y ahora está cambiando a `'isPublished' => true`. Si es así, crear una notificación allí mismo. Si esto te parece bien, ¡adelante!

Sin embargo, para mí, poner la lógica aquí no me parece del todo bien... simplemente porque es un "mapeador de datos", por lo que huele un poco raro hacer algo más allá de simplemente mapear los datos.

## Crear el procesador de estados

Volvamos a nuestra solución original: crear un procesador de estados. En tu terminal, ejecuta:

```terminal
php bin/console make:state-processor
```

Llámalo `DragonTreasureStateProcessor`. Nuestro objetivo debería resultarte familiar: añadiremos algo de lógica personalizada aquí, pero llamaremos al procesador de estado normal para dejar que haga el trabajo pesado.

[[[ code('46ddf47311') ]]]

Para ello, añade un método `__construct()` con`private EntityClassDtoStateProcessor $innerProcessor`. Aquí abajo, úsalo con`return $this->innerProcessor->process()` pasándole los argumentos que necesita: `$data`,`$operation`, `$uriVariables`, y `$context`. Ah, y puedes ver que esto está resaltado en rojo. Esto no es realmente un método `void`, así que quítalo.

[[[ code('da9de4e448') ]]]

Bien, ¡vamos a conectar nuestro recurso API para utilizarlo! Dentro de `DragonTreasureApi`, cambia el procesador a `DragonTreasureStateProcessor`.

[[[ code('196d969dd3') ]]]

Llegados a este punto, en realidad no hemos cambiado nada: el sistema llamará a nuestro nuevo procesador... pero luego sólo llama al antiguo. Y así, cuando ejecutamos las pruebas:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Todo sigue funcionando excepto ese último fallo.

## Detectar el cambio isPublished

Así que ¡añadamos nuestro código de notificación! Originalmente, averiguamos si `isPublished`estaba cambiando de `false` a `true` utilizando los "datos anteriores" que hay dentro de `$context`. Vuelca `$context['previous_data']` para ver qué aspecto tiene.

[[[ code('694e3ae205') ]]]

Ahora, ejecuta sólo esta prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

¡Genial! Los datos anteriores son el `DragonTreasureApi` con `isPublished: false`.. porque ése es el valor con el que empieza nuestra entidad en la prueba. Volquemos también `$data`.

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

Vale, ¡el original tiene `isPublished: false`, y el nuevo tiene`isPublished: true`! Y eso nos pone en peligro.

Volvamos atrás, escribimos el código de notificación en un tutorial anterior... así que lo pegaré. ¡Esto es deliciosamente aburrido! Usamos` $previousData` y `$data` para detectar el cambio de estado de `isPublished` falso a verdadero... luego creamos un`Notification`.

[[[ code('a4ce6eae4b') ]]]

Lo único un poco interesante es que la entidad `Notification` está relacionada con una entidad `DragonTreasure`... así que consultamos la `$entity` utilizando la `repository` y la `id` de la clase DTO.

Vamos a inyectar los servicios que necesitamos: `private EntityManagerInterface $entityManager`
para que podamos guardar y `private DragonTreasureRepository $repository`.

[[[ code('1fb1295d72') ]]]

¡Ya está! Momento de la verdad:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPublishTreasure
```

¡La prueba pasa! Demonios, en este punto, ¡todas nuestras pruebas del tesoro pasan! Hemos convertido completamente este complejo recurso API a nuestro sistema impulsado por DTO! Choca esos cinco!

A continuación: Vamos a hacer posible la escritura de la propiedad `$owner` en el tesoro dragón. Esto implica un truco que nos ayudará a comprender mejor cómo carga la API Platform los datos de las relaciones.
