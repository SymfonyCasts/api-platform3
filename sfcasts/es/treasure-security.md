# DTO y Seguridad

¡Nuestro `DragonTreasureApi` tiene un aspecto estupendo! Cuando este recurso era una entidad, añadimos unas cuantas personalizaciones geniales e incluimos pruebas para ellas. El pasado "nos" mola.

El plan ahora es volver a poner esas cosas pieza a pieza y ver cómo podemos simplificar la implementación dentro de nuestra nueva configuración potenciada por DTO.

Vuélvete loco y ejecuta todas las pruebas del tesoro del dragón:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Bastantes fallan... y una de ellas dice:

> El código de estado de respuesta actual es 422, pero se esperaba 403.

Este `testPostToCreateTreasureDeniedWithoutScope` está relacionado con la seguridad, y eso tiene sentido. ¡ `DragonTreasureApi` carece por completo de seguridad!

## Volver a añadir seguridad

Empieza como hicimos con `UserApi`: especificando las operaciones que queremos. Empieza con `new Get()`, `new GetCollection()`, y `new Post()`. En el sistema original, `Post()` tenía una opción `security` establecida en `'is_granted("ROLE_TREASURE_CREATE")`.

[[[ code('5175155b27') ]]]

Esto está directamente relacionado con ese fallo de la prueba, que comprueba que nuestro token de la API tiene esa función. Bueno... si escribo "crear" correctamente, al menos.

También teníamos una operación `Patch()` y que también tenía una opción `security`. Esto aprovechaba un votador personalizado para comprobar si el usuario actual puede `EDIT` este tesoro. Más sobre esto en un minuto.

[[[ code('ac910a722e') ]]]

Y, por último, teníamos `new Delete()`, que decidimos que sólo podían hacer los administradores. Refuerza eso con `is_granted("ROLE_ADMIN")`.

[[[ code('32edf9c6a1') ]]]

Vale, antes tuvimos seis fallos y ahora

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Hemos bajado a cinco. ¡Progreso! Acerquémonos a `testPatchToUpdateTreasure` y ejecutemos justo eso:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

Vuelve aquí... mira lo que está haciendo. Vale, crea un `User`, un tesoro, se registra como propietario, intenta cambiar el valor de ese tesoro, se asegura de que obtenemos un código de estado 200 y, por último, comprueba que vemos el valor actualizado. Ahora mismo, obtenemos un 403 en lugar de un 200.

## Actualizar el votante de seguridad para el DTO

Un estado 403 es un fallo de seguridad. Por alguna razón, no se nos permite hacer una petición a `Patch()` a este tesoro... ¡aunque seamos el propietario! ¡Grosero!

Vale: `Patch()` está utilizando `is_granted("EDIT", object)`. Esto de`"EDIT", object` lo gestiona un votante personalizado llamado `DragonTreasureVoter`que creamos en un tutorial anterior. Así que, o no se está llamando a este votante o está diciendo que no deberíamos tener acceso.

Para ver lo que ocurre bajo el capó, `dump($attribute, $subject)`. Este método`supports()` es llamado cada vez que se toma una decisión de seguridad en todo el sistema, por lo que debería ser llamado.

Cuando volvamos a ejecutar la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

¡Ahí está el volcado! Vuelca `EDIT`, que procede de la operación `Patch()`. Pero aquí viene lo bueno: el objeto es ahora un `DragonTreasureApi`, ¡lo cual tiene sentido! Pero nuestro `DragonTreasureVoter` se escribió para trabajar con la entidad, no con`DragonTreasureApi`.

¡No hay problema! Actualicemos este votante para que funcione con el DTO. Para mayor claridad, renombra esto a `DragonTreasureApiVoter`. Luego, apoyaremos si`DragonTreasureApi` es el `$subject`. Y aquí abajo, este `$subject` también debería ser `DragonTreasureApi`. `dd($subject)`... y más abajo, vamos a arreglar el código. Esto dice que si el usuario no tiene este rol (en realidad un ámbito, que se relaciona con los ámbitos de los tokens), devuelve `false`.

[[[ code('c7d4ae2874') ]]]

La parte más importante es la siguiente: si el `$subject` -que es un`DragonTreasureApi` - tiene un propietario que es igual a `$user` -el usuario autenticado actualmente-, entonces devuelve true: ¡acceso concedido!

Comenta este `dd()` rápidamente. Lo que necesitamos ahora es `$subject->owner`.

Bueno, eso no está del todo bien... y si volvemos a poner ese `dd()`, veremos por qué. Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

Este volcado -el `$subject` - es, por supuesto, un `DragonTreasureApi`. Pero recuerda que su propiedad `owner` no es una entidad `User`: es un objeto `UserApi`. Así que no podemos comparar sin más el objeto `UserApi` con el objeto entidad `$user`.

También debemos tener cuidado debido a nuestro mapeador. Gracias a la profundidad, el `UserApi`no está poblado: es un objeto superficial. No pasa nada, podemos comparar el id de los objetos, pero tenlo en cuenta.

Así que el tl;dr es: compara la propiedad `id` con `$user->getId()`. Ah, y no autocompletó `getId()`... pero podemos ayudar a nuestro editor haciendo que`instanceof` compruebe específicamente que se trata de una entidad `User`, que siempre lo será en nuestra aplicación.

Ahora usa `getId()`... y codificaré a la defensiva añadiendo un `?`... por si este `DragonTreasureApi` no tiene propietario: como para un tesoro que estamos creando ahora mismo.

[[[ code('375a148290') ]]]

¡Uf! ¡Ve y pruébalo ahora!

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

## Añadiendo la cabecera application/merge-patch+json

¡Progreso! El código de estado de respuesta actual es ahora 415. Esto es gracias a un pequeño detalle del que hemos hablado varias veces:

> El tipo de contenido `application/json` no es compatible. Los tipos MIME admitidos son
> `application/merge-patch+json`.

Cuando hacemos una petición PATCH, necesitamos tener una clave `headers` con `Content-Type`ajustado a `application/merge-patch+json`. La razón por la que no necesitábamos eso antes, como mencioné en un tutorial anterior... se debe a algún asunto curioso con los formatos que hizo que, accidentalmente, no fuera necesario para este recurso. Pero ahora sí lo necesitamos.

Añadámoslo rápidamente a todas nuestras peticiones de `patch()`. Hay un montón de ellas. ¡Zoom!

[[[ code('ca1b62c840') ]]]

¡Veamos si tenemos suerte!

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

Y... ooh... se muere. ¡Ha golpeado nuestro vertedero! Eso viene de`DragonTreasureApiToEntityMapper`: cuando se envía el `owner` en el JSON. Comenta esto un momento para que podamos ver la imagen completa. Ejecuta de nuevo la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

> El código de estado de respuesta actual es 200, pero se espera 422.

Procedente de la línea 157. Así que, mirando nuestra prueba, la mayor parte pasa. La línea 157 está muy abajo. Esto significa que podemos enviar una petición a `patch()` ¡y que se actualiza!

¡Y el flujo completo aquí es fascinante! Cuando hacemos una petición `patch()` a un tesoro, API Platform empieza utilizando nuestro proveedor de datos para encontrar la entidad`DragonTreasure`. A continuación, la asignamos a un objeto `DragonTreasureApi`. A continuación, el nuevo `value` se deserializa en ese `DragonTreasureApi`. Por último, en nuestro procesador, volvemos a mapear el `DragonTreasureApi` actualizado a una entidad `DragonTreasure`, y eso es en definitiva lo que se guarda. A continuación, el `DragonTreasureApi` se serializa y se devuelve como JSON.

Así que esto funciona... y me encanta cómo encajan todas las piezas.

## Actualizando el Validador Personalizado

Donde estamos fallando es aquí abajo. Esto comprueba si se nos permite cambiar el `owner` por otro. Entramos como `$user` y editamos nuestro propio tesoro... ¡pero intentamos cambiar el tesoro a otro propietario! Esto es como un Papá Noel dragón que se cuela en las cuevas de otros dragones para hacer una entrega nocturna de tesoros. Eso está muy bien... pero no es algo que queramos permitir.

Antes teníamos un validador personalizado que lo impedía. Así que vamos a volver a añadirlo

Abre `DragonTreasureApi` y busca la propiedad `$owner`. Añade `#[IsValidOwner]`: un validador que creamos en un tutorial anterior.

[[[ code('3b803b7896') ]]]

Lo encontrarás en `src/Validator/`. Anteriormente, este validador esperaba que su restricción se utilizara sobre una propiedad que contuviera una entidad `User`. Nosotros lo estamos poniendo sobre una propiedad que contiene una `UserApi`. Así que, al igual que con el votador, tenemos que actualizarlo para la nueva realidad.

Justo aquí, `assert()` que `$value` es un `instanceof UserApi`.

[[[ code('6fcf17e2ed') ]]]

Aquí abajo, tenemos que comprobar si el valor (es decir, el `UserApi` que hay en esta propiedad) no es igual al usuario autenticado actualmente. Una vez más, utilizaremos el `id`s para comparar esto. Y... también una vez más, utilizaré `assert()` para ayudar a mi editor. Ahora... está contento con `getId()`... ¡pero no con el punto y coma que me falta!

[[[ code('7a8cffb028') ]]]

¡El momento de la verdad! Ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

¡Pasa! Prueba todo:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Y... ¡ah! Sólo nos quedan tres fallos. Y todos están relacionados con lo mismo: la propiedad `isPublished`. Nuestro `DragonTreasureApi` ni siquiera tiene aún la propiedad `isPublished`. La hemos dejado para el final porque es un poco diferente e interesante. Vamos a abordarla a continuación.
