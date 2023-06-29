# Permitir editar sólo a los propietarios

Nueva búsqueda de seguridad: Quiero permitir que sólo el propietario de un tesoro pueda editarlo. Ahora mismo, puedes editar un tesoro siempre que tengas este rol. Pero eso significa que puedes editar el tesoro de cualquiera. Alguien sigue cambiando el`coolFactor` de mi cuadro Velvis a 0. Eso no mola nada.

## TDD: Probar que sólo los Propietarios pueden Editar

Escribamos una prueba para esto. En la parte inferior di`public function testPatchToUpdateTreasure()`:

[[[ code('8f416537c9') ]]]

Y empezaremos como siempre: `$user = UserFactory::createOne()` luego`$this->browser->actingAs($user)`.

Como vamos a editar un tesoro, vamos a `->patch()` a `/api/treasures/`... ¡y luego necesitamos un tesoro para editar! Crea uno encima:`$treasure = DragonTreasureFactory::createOne()`. Y para esta prueba, queremos asegurarnos de que el `owner` es definitivamente este `$user`. Termina la URL con`$treasure->getId()`.

Para los datos, envía algo de `json` para actualizar sólo el campo `value` a `12345`, luego `assertStatus(200)` y `assertJsonMatches('value', 12345)`:

[[[ code('1b7917ca30') ]]]

¡Excelente! Esto debería estar permitido porque somos el `owner`. Copia el nombre del método, luego busca tu terminal y ejecútalo:

```terminal
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

No te sorprendas, pasa.

Ahora probemos el otro caso: iniciemos sesión como otra persona e intentemos actualizar este tesoro.

Copia toda la sección `$browser`. Podríamos crear otro método de prueba, pero esto funcionará bien todo en uno. Antes de esto, añade`$user2 = UserFactory::createOne()` - y luego inicia sesión como ese usuario. Esta vez, cambia el `value` por `6789` y, como esto no debería estar permitido, afirma que el código de estado es 403:

[[[ code('3d13ebcbed') ]]]

Cuando intentemos la prueba ahora

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Falla! Esto está permitido: ¡la API devuelve un 200!

## Expresiones de seguridad más complejas

Entonces, ¿cómo podemos hacer que sólo el propietario de un tesoro pueda editarlo? Bueno, en `DragonTreasure`, la respuesta está en la opción `security`:

[[[ code('96e307bd73') ]]]

Una cosa que resulta complicada con `Put()` y `Patch()` es que ambos se utilizan para editar usuarios. Así que si vas a tener ambos, necesitas mantener sus opciones `security`sincronizadas. De hecho, voy a eliminar `Put()` para que podamos centrarnos en `Patch()`.

La cadena dentro de `security` es una expresión... y podemos ponernos un poco elegantes. Podemos conceder acceso si tienes `ROLE_TREASURE_EDIT` y si `object.owner == user`:

[[[ code('2badf72b95') ]]]

Dentro de la expresión de seguridad, Symfony nos da unas cuantas variables. Una es `user`, que es el objeto actual `User`. Otra es `object`, que será el objeto actual para esta operación. Así que el objeto `DragonTreasure`. Así que estamos diciendo que se debe permitir el acceso si el `DragonTreasure`s `owner` es igual al`user` autenticado actualmente. Eso es... ¡exactamente lo que queremos!

Así que, ¡vuelve a intentar la prueba!

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

Y... ¡oh! ¡Bajamos a un error 500! Aquí es donde resulta útil ese archivo de registro guardado. Haré clic para abrirlo. Si esto es difícil de leer, mira la fuente de la página. Mucho mejor. Dice

> No se puede acceder a la propiedad privada `DragonTreasure::$owner`.

Y viene de `ExpressionLanguage` de Symfony . Ah, ya sé lo que pasa. El lenguaje de expresión es como Twig... pero no exactamente igual. No podemos hacer cosas extravagantes como `.owner` cuando `owner` es una propiedad privada. Tenemos que llamar al método público:

[[[ code('f9ca58be21') ]]]

Redoble de tambores, por favor:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Pasa con éxito!

## Impedir el cambio de propietario: securityPostDenormalize

Pero ya me conoces, tengo que hacerlo más difícil. Copia parte de la prueba. Esta vez, iniciar sesión como propietario y editar nuestro propio tesoro. Hasta aquí, todo bien. Pero ahora intenta cambiar el `owner` por otro: `$user2->getId()`:

[[[ code('769533beb6') ]]]

Ahora puede que esto sea algo que quieras permitir. Tal vez digas

> Si puedes editar un `DragonTreasure`, entonces eres libre de asignarle un > propietario diferente
> propietario.

Pero supongamos que queremos impedirlo. Entonces `assertStatus(403)`. ¿Crees que la prueba pasará? Inténtalo:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Falla! ¡Nos ha permitido cambiar el `owner`! Vuelve a `DragonTreasure`. La expresión `security` se ejecuta antes de que los nuevos datos se deserialicen en el objeto. En otras palabras, `object` será el `DragonTreasure` de la base de datos, antes de que se le aplique nada del nuevo JSON. Esto significa que se está comprobando que el `owner` actual es igual al usuario conectado en ese momento, que es el caso principal que queremos proteger.

Pero a veces quieres ejecutar la seguridad después de que los nuevos datos se hayan introducido en el objeto. En ese caso, utiliza una opción llamada `securityPostDenormalize`. Recuerda que desnormalizar es el proceso de tomar los datos y ponerlos en el objeto. Así que`security` seguirá ejecutándose primero... y se asegurará de que somos el propietario original. Ahora también podemos decir `object.getOwner() == user`:

[[[ code('6936091374') ]]]

Esto parece idéntico... pero esta vez `object` será el `DragonTreasure` con los nuevos datos. Así que estamos comprobando que el nuevo propietario también es igual al usuario actualmente conectado.

Por cierto, en `securityPostDenormalize`, también tienes una variable `previous_object`, que es igual al objeto antes de la desnormalización. Por tanto, es idéntica a `object`en la opción `security`. Pero, no necesitamos eso.

Haz la prueba ahora:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

¡Lo hemos conseguido!

## Seguridad frente a validación

Este último ejemplo pone de manifiesto dos tipos diferentes de comprobaciones de seguridad. La primera comprobación determina si el usuario puede o no realizar esta operación. Por ejemplo: ¿puede el usuario actual hacer una petición a este tesoro a `PATCH`? Eso depende del usuario actual y del DragonTreasure actual en la base de datos.

Pero la segunda comprobación dice

> Vale, ahora que sé que se me permite hacer una petición a `PATCH`, ¿se me permite
> cambiar los datos de esta forma exacta?

Esto depende del usuario conectado en ese momento y de los datos que se estén enviando.

Traigo a colación esta diferencia porque, para mí, el primer caso -en el que intentas averiguar si una operación está permitida en absoluto, independientemente de los datos que se envíen- es tarea de la seguridad. Y así es exactamente como yo lo implementaría.

Sin embargo, en el segundo caso, en el que intentas averiguar si el usuario está autorizado a enviar esos datos exactos -por ejemplo, si puede cambiar la dirección`owner` o no-, creo que es mejor que se encargue de ello la capa de validación.

Por ahora voy a mantener esto en la capa de seguridad. Pero más adelante, cuando hablemos de la validación personalizada, lo trasladaremos a ella.

Próximamente: ¿podemos flexibilizar la opción `security` lo suficiente como para permitir también a los usuarios administradores editar el tesoro de cualquiera? ¡Permanece atento!
