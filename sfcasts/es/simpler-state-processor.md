# Procesador de estado más sencillo

Publicar un `DragonTreasure` es fácil: haz una petición `Patch` a la ruta del tesoro con `isPublished` establecido en true y... ¡celebración! Pero... ¿qué pasa si, cuando se publica un`DragonTreasure`, necesitamos ejecutar algún código personalizado - tal vez activar algunas notificaciones en el sitio.

Una opción es crear una operación personalizada, como `POST /api/treasures/5/publish`. Puedes hacerlo, y puede ser divertido verlo en un futuro tutorial. Pero, ¿quién quiere trabajo extra? Podemos mantener esa simple petición a `Patch` y seguir ejecutando el código que queramos. ¿Cómo? Utilizando un procesador de estado y detectando el cambio.

Empecemos creando una prueba que publique un tesoro. En la parte inferior, copia esta última prueba, pégala y cámbiale el nombre a `testPublishTreasure`. Comenzamos con un usuario que posee un tesoro con `isPublished` `false` . A continuación, iniciamos sesión como ese usuario, hacemos una petición`->patch()` a `/api/treasures/` utilizando el id... y enviamos`isPublished: true`. Esto debería ser un código de estado 200... y luego`->assertJsonMatches()` que `isPublished` es `true`.

[[[ code('f34b2a3a1a') ]]]

¡Bastante sencillo! Copia el nombre de la prueba, gira y ejecútala:

```terminal
symfony php bin/phpunit --filter=testPublishTreasure
```

¡Uy! Falla: esperaba que `false` fuera lo mismo que `true`. Eso es de la última línea: el JSON sigue teniendo `isPublished` false. Quizá... ¿el campo no es escribible? Comprueba los grupos que hay sobre esa propiedad. Ah: en un tutorial anterior, hicimos que este campo fuera escribible por los usuarios administradores, pero no por los usuarios normales. Añade `treasure:write`.

[[[ code('7f13ac18a5') ]]]

Eso significa que cualquiera con acceso a la operación `Patch` puede escribir en este campo... que en realidad, gracias al `security` de esa operación... y a un votador personalizado que creamos... son sólo los usuarios administradores y el propietario.

[[[ code('98ce0bb528') ]]]

Haz la prueba ahora:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

¡Ya está! Para ejecutar algún código cuando se publique el tesoro, necesitamos un procesador de estado. Y ya tenemos uno para `¡TesoroDragón! Lo creamos originalmente para establecer el propietario en el usuario autenticado en ese momento. Así que... ¿deberíamos meter el nuevo código aquí o crear un segundo procesador?

Tú decides, pero a mí me gusta tener un procesador por clase de recurso. Me simplifica la vida. Pero cambiemos el nombre de esta clase para que quede más claro: `DragonTreasureStateProcessor`.

## Cambiar la decoración de nuestro procesador de estado

En el último tutorial, aprendimos que hay dos formas de añadir un proveedor o procesador de estado personalizado al sistema. El primer método lo hemos utilizado hace unos minutos con el proveedor de estado: crear un servicio aburrido normal... utilizar `#[Autowire]` para inyectar los servicios centrales... luego establecer la opción `provider`en `DragonTreasure` para que apunte a él.

La otra forma -que hicimos en el último tutorial de esta clase- es decorar el procesador central. Aquí, decoramos el `PersistProcessor`de Doctrine... lo que significa que siempre que se guarde cualquier recurso de la API, cuando intente utilizar el núcleo `PersistProcessor`, se llamará a nuestro servicio en su lugar. Esto fue fácil de configurar porque todo lo que necesitábamos era `#[AsDecorator]` y... ¡bam! Nuestro servicio empezó a ser llamado para todos nuestros recursos. Pero también por eso necesitamos este código adicional que comprueba qué objeto se está guardando.

[[[ code('05aaee4b69') ]]]

Ambas formas están bien. Pero por coherencia con el proveedor, vamos a refactorizar esto para utilizar el otro método. Esto consta de 3 pasos. En primer lugar, elimina `#[AsDecorator]`. De repente, en lugar de sobrescribir un servicio central, se trata de un servicio normal y aburrido que nadie utiliza en este momento. En segundo lugar, como ya no estamos decorando un servicio del núcleo, Symfony no sabrá qué pasar por `$innerProcessor`. Divide esto en varias líneas... y luego utiliza el truco `#[Autowire]` para apuntar al núcleo `PersistProcessor`. Y yo limpiaré la antigua declaración `use`.

[[[ code('d7aeb5eec0') ]]]

El paso 3 es decirle a API Platform cuándo utilizar este procesador. En `DragonTreasure`, queremos que se utilice para nuestras operaciones `Post` y `Patch`. Establece`processor` en `DragonTreasureStateProcessor::class`... y repite eso hacia abajo para`Patch`.

[[[ code('2a1123eeae') ]]]

¡Listo! API Platform llamará a nuestro procesador... y contiene el núcleo `PersistProcessor`para que podamos hacer que haga el trabajo real. Vuelve a ejecutar la prueba para darnos una confianza infinita:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Me parece estupendo.

Y lo bueno de hacer el procesador con este método es que no necesitas este código condicional: esto siempre será un `DragonTreasure`. Para ayudar a mi editor y demostrarlo, `assert()` que `$data` es un `instanceof``DragonTreasure` .

[[[ code('fcaf6cba28') ]]]

Y mi editor ya está gritando

> ¡Eh, este código de aquí abajo ya no es necesario, tío!

Así que elimínalo también. Ahora que hemos refactorizado nuestro procesador de estados, volvamos a la tarea que nos ocupa: ejecutar código personalizado cuando se publica un tesoro.
