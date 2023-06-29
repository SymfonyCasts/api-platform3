# Configuración automática del "propietario

Cada `DragonTreasure` debe tener un `owner`... y para establecerlo, cuando `POST`para crear un tesoro, requerimos ese campo. Creo que deberíamos hacerlo opcional. Así que, en la prueba, deja de enviar el campo `owner`:

[[[ code('9940b47918') ]]]

Cuando esto ocurra, configurémoslo automáticamente para el usuario autenticado actualmente.

Asegúrate de que la prueba falla. Copia el nombre del método... y ejecútalo:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Falló. Obtuve un 422, 201 esperado. Ese 422 es un error de validación de la propiedad `owner`: este valor no debe ser nulo.

## Eliminar la validación del propietario

Si vamos a hacerlo opcional, tenemos que eliminar ese `Assert\NotNull`:

[[[ code('b2c1c12e9c') ]]]

Y ahora cuando intentemos la prueba

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Hola magnífico error 500! Probablemente se deba a que el nulo `owner_id` hace kaboom cuando llega a la base de datos. ¡Yup!

## Utilizar los procesadores de estado

Entonces: ¿cómo podemos establecer automáticamente este campo cuando no se envía? En el tutorial anterior de la API Platform 2, lo hice con un oyente de entidad, que es una buena solución. Pero en la API Platform 3, al igual que cuando hicimos hash de la contraseña de usuario, ahora hay un sistema muy bueno para esto: el sistema de procesadores de estado.

Como recordatorio, nuestras rutas POST y PATCH para `DragonTreasure` ya tienen un procesador de estado que proviene de Doctrine: es el responsable de guardar el objeto en la base de datos. Llegados a este punto, nuestro objetivo te resultará familiar: decorar ese proceso de estado para que podamos ejecutar código adicional antes de guardar.

Como antes, empieza ejecutando:

```terminal
php bin/console make:state-processor
```

Llámalo `DragonTreasureSetOwnerProcessor`:

[[[ code('fdf8a18e84') ]]]

En `src/State/`, ábrelo. Vale, ¡a decorar! Añade el método construct con `private ProcessorInterface $innerProcessor`:

[[[ code('b66e1324b2') ]]]

Luego abajo en `process()`, ¡llama a eso! Este método no devuelve nada - tiene un retorno `void` - así que sólo necesitamos `$this->innerProcessor` - no olvides esa parte como estoy haciendo yo - `->process()` pasando `$data`, `$operation`, `$uriVariables` y`$context`:

[[[ code('63f6998dee') ]]]

Ahora, para hacer que Symfony utilice nuestro procesador de estado en lugar del normal de Doctrine, añade `#[AsDecorator]`... y el id del servicio es`api_platform.doctrine.orm.state.persist_processor`:

[[[ code('08ff7fd147') ]]]

¡Genial! Ahora, a todo lo que utilice ese servicio en el sistema se le pasará nuestro servicio en su lugar... y luego se nos pasará el original.

## ¡Decorar varias veces está bien!

Ah, y está pasando algo guay. Mira `UserHashPasswordStateProcessor`. ¡Estamos decorando lo mismo ahí! Sí, estamos decorando ese servicio dos veces, ¡lo que está totalmente permitido! Internamente, esto creará una especie de cadena de servicios decorados.

Bien, pongámonos a trabajar en la configuración del propietario. Conecta automáticamente nuestro servicio favorito `Security` para que podamos averiguar quién ha iniciado sesión:

[[[ code('b183c1fa93') ]]]

Entonces, antes de que hagamos el guardado, si `$data` es un `instanceof DragonTreasure`y `$data->getOwner()` es nulo y `$this->security->getUser()` -asegurándonos de que el usuario está conectado- entonces `$data->setOwner($this->security->getUser())`:

[[[ code('338882358b') ]]]

¡Eso debería bastar! Ejecuta esa prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Caramba! Tamaño de memoria permitido agotado. ¡Me huele a recursión! Porque... Me estoy llamando a`process()`: Necesito `$this->innerProcessor->process()`:

[[[ code('fcbf2eb93f') ]]]

Ahora:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Una prueba superada mola mucho más que la recursividad. ¡Y el campo propietario ahora es opcional!

Siguiente: actualmente devolvemos todos los tesoros de nuestro punto final de colección GET, incluidos los tesoros no publicados. Arreglémoslo modificando la consulta detrás de ese punto final para ocultarlos.
