# Hacer que DragonTreasureApi sea escribible

Vamos a hacer que nuestras rutas de escritura funcionen en `DragonTreasureApi` Si miras aquí abajo, tenemos una prueba llamada `testPostToCreateTreasure()`. ¡Suena bien! En tu terminal, ejecútalo:

```terminal
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Y... ¡hace kaboom! Ejecuta unas cuantas pruebas... y todas dicen lo mismo:

> No se ha encontrado ningún mapeador para `DragonTreasureApi` -&gt `DragonTreasure`

Vale, cuando hacemos un POST, se deserializa el JSON en un nuevo objeto `DragonTreasureApi` y luego se llama a nuestro procesador. Nuestro procesador toma ese objeto API e intenta utilizar MicroMapper para asignarlo a la entidad `DragonTreasure`. Como nos falta el mapeador de `DragonTreasureApi` a `DragonTreasure`, ¡kablooie!

## Crear el mapeador

¡Ya sabemos cómo funciona! En `src/Mapper/`, crea un nuevo `DragonTreasureApiToEntityMapper`. Dentro, implementa `MapperInterface`, utiliza `#[AsMapper()]` para decir que estamos mapeando `from: DragonTreasureApi::class`, `to: DragonTreasure::class`... y añade los dos métodos.

[[[ code('45209028d0') ]]]

Esto será muy similar a nuestro `UserApiToEntityMapper`. En `load()`, si tenemos un ID, queremos consultar ese objeto. Añade un constructor, con`private DragonTreasureRepository $repository`. Aquí abajo, incluye el ya familiar`$dto = $from`, y `assert` que `$dto` es un `instanceof DragonTreasureApi`. Para hacernos la vida aún más fácil, roba algo de código de nuestro otro mapeador. Copia esto... y ponlo aquí. Pero dale a "Cancelar" porque no necesitamos esa declaración `use`... y renombra esto a sólo `$entity`. Así que si el `$dto` tiene un `id`, significa que lo estamos editando y queremos encontrar el existente. Si no, vamos a crear un`new DragonTreasure()`. Y aunque no debería ocurrir, tenemos un `Exception` por si no encontramos el tesoro.

Una cosa interesante de la entidad `DragonTreasure` es que tiene un argumento constructor: el nombre. Y no tenemos un método `setName()`: la única forma de establecerlo es a través del constructor. Así que, para transferir el `name` del`$dto` a la entidad, pásalo al constructor.

[[[ code('236cd88165') ]]]

Dos notas rápidas sobre esto. Sí, esto significa que no puedes cambiar el nombre de un tesoro existente a través de la API. Y eso es lo esperado: si hemos escrito nuestro`DragonTreasure` sin un método `setName()`, entonces pretendemos que el nombre se establezca una vez y nunca se cambie. En segundo lugar, éste es el único caso en el que rellenamos un poco de información dentro de `load()`. Normalmente dejamos ese trabajo para `populate()`, pero aquí no se puede evitar, y no pasa nada.

Dirígete a `populate()` y empieza con el mismo código de `load()`. Añade también`$entity = $to`... y un `assert()` más que `$entity instanceof DragonTreasure`. Digamos `TODO` por un momento.

[[[ code('d52af620d8') ]]]

Quiero asegurarme de que al menos se llama a nuestro mapeador. Antes, cuando ejecutamos la prueba, ejecutó tres pruebas que coinciden con el nombre. Así que hagamos que el método sea un poco más único. Se llama `testPostToCreateTreasure()` y utiliza el mecanismo de inicio de sesión normal, así que añade `WithLogin` al final. Cuando ejecutemos la prueba con el nuevo nombre:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

¡Un error 500! Veamos qué ocurre. Vale, ¡bien! Hemos llegado más lejos! Ahora explota cuando entra en la base de datos. Así que está intentando guardar, y se está quejando porque `owner_id` es nulo.

## Añadir restricciones de validación

Recordatorio: se supone que el campo `owner` es opcional. Si no pasamos un propietario, debería establecerse automáticamente en el usuario autenticado. Antes teníamos código para eso, y lo volveremos a añadir dentro de un momento.

Pero este fallo en realidad viene de antes: de la línea 71, justo aquí. Esta prueba comienza comprobando nuestra validación. No envía ningún JSON, y se asegura de que nuestras restricciones de validación salvan el día. No tenemos restricciones de validación, así que en lugar de fallar la validación, intenta guardar. Boo.

Volvamos a añadir las restricciones... esta vez a nuestra clase API. Para `$name`,`#[NotBlank]`, `$description`, `#[NotBlank]`, `$value` será`#[GreaterThanOrEqual(0)]` y `$coolFactor` será `#[GreaterThanOrEqual(0)]`y también `#[LessThanOrEqual(10)]`.

[[[ code('1a8bb5530c') ]]]

Vuelve a hacer la prueba.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

Probablemente nos encontremos con el mismo error, y... sip - error 500. Pero ¡mira! ¡Ahora viene de la línea 78! Eso significa que aquí estamos obteniendo el código de estado de error de validación. Luego, más abajo, cuando enviamos datos válidos, intenta guardarlos en la base de datos, pero no puede porque, como vimos hace un segundo, el `owner_id` sigue siendo nulo.

## Establecer automáticamente el propietario

Ésta es una de las grandes ventajas de estos objetos mapeadores. En`DragonTreasureApiToEntityMapper`, normalmente, vamos a hacer cosas como`$entity->setValue($dto->value)`: simplemente transferir datos de uno a otro. Pero también podemos hacer cosas personalizadas, como establecer campos extraños que requieran cálculos o... establecer el propietario en el usuario autenticado en ese momento.

Compruébalo: `if ($dto->owner)` bueno, no lo haremos todavía, sólo `dd()` por ahora. Este es el caso en el que sí incluimos el campo `owner` en el JSON... y pronto hablaremos más de ello.

[[[ code('474c84c9c5') ]]]

Para el `else`, esto es cuando el usuario no envía un campo `owner`. Para establecerlo en el usuario autenticado actualmente, arriba, inyecta el servicio `Security` en una nueva propiedad. Luego, abajo, establece `owner` en `$this->security->getUser()`.

[[[ code('bbe060608b') ]]]

¡Estupendo! Todavía nos falta la configuración del otro campo... así que si intentamos ejecutar la prueba... seguirá dando un 500. Pero, si compruebas el error, está fallando porque `description` es nulo. Se está configurando `owner`.

Así que vamos a rellenar los otros campos: `$entity->setDescription($dto->description)`,`$entity->setCoolFactor($dto->coolFactor)`, y `$entity->setValue($dto->value)`.

[[[ code('5175155b27') ]]]

Trabajo aburrido pero claro. Incluye también un `TODO` abajo para `published`. Hablaremos más de ello en breve.

Vale, ejecuta ahora la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

Y... pasa. ¡Guau! Prueba todas las pruebas de `DragonTreasure`:

```terminal
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Y... ooo. Tenemos varios fallos, relacionados con cabeceras que faltan, seguridad, validación, etc. Vamos a ponerlo verde a continuación.
