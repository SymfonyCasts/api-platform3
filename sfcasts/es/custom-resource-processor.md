# Procesador de estado de recursos personalizado

No hemos configurado la clave `operations` en nuestro `#[ApiResource]`. Y así, obtenemos todas las operaciones por defecto. Pero en realidad sólo necesitamos unas pocas. Añade`operations` con un `new GetCollection()`, `new Get()` para obtener una única búsqueda y `new Patch()` para que los usuarios puedan actualizar el estado de una búsqueda existente cuando la completen.

[[[ code('e89482bebf') ]]]

Al actualizar... ¡Me encanta!

Hablando de esa operación `Patch`, cuando se utilice, API Platform llamará al procesador de estado, para que podamos guardar... o hacer lo que queramos. Aún no tenemos uno, así que ése será nuestro próximo trabajo.

## Añadir una prueba de parcheo

Pero empecemos con una prueba. Abajo, en `tests/Functional/`, crea una nueva clase llamada `DailyQuestResourceTest`. Haz que ésta extienda la `ApiTestCase` que creamos en el último tutorial y la `use ResetDatabase` de Foundry para asegurarnos de que nuestra base de datos está vacía al inicio de cada prueba. También `use Factories`.

[[[ code('2c769d7f47') ]]]

Vale, no los necesitamos... ya que no vamos a hablar con la base de datos... pero si decidimos hacerlo más adelante, ya estamos listos.

Aquí abajo, añade `public function testPatchCanUpdateStatus()`. Lo primero que necesitamos es un `new \DateTime()` que represente `$yesterday`: `-1 day`.

[[[ code('8d13f439a1') ]]]   

Recuerda: en nuestro proveedor, estamos creando búsquedas diarias desde hoy hasta los últimos 50 días. Cuando hacemos una petición a `PATCH`, se llama a nuestro proveedor de objetos para que "cargue" el objeto. Así que tenemos que utilizar una fecha que sepamos que se va a encontrar.

Ahora digamos `$this->browser()`, `->patch()`... y la URL:`/api/quests/` con `$yesterday->format('Y-m-d')`. Pasa un segundo argumento de opciones con `json` y un array con `'status' => 'completed'`.

El campo `status` es un enum... pero como está respaldado por una cadena, el serializador lo deserializará a partir de la cadena `active` o `completed`. Termina con`->assertStatus(200)`, `->dump()` (que será útil en un segundo), y luego`->assertJsonMatches()` para comprobar que `status` cambió a `completed`.

[[[ code('11e5afb43e') ]]]

¡Maravilloso! En realidad no vamos a guardar el estado actualizado... pero al menos deberíamos ver que el JSON final tiene `status` `completed` . Copia este nombre de prueba... y por aquí, ejecuta: `symfony php bin/phpunit --filter=` y pega ese nombre:

```terminal-silent
symfony php bin/phpunit --filter=testPatchCanUpdateStatus
```

Y... ¡ups! Obtenemos un 415. El error dice

> El tipo de contenido `application/json` no es compatible.

Ah... olvidé añadir una cabecera a mi petición `PATCH`. Añade `headers` en una matriz con `Content-Type`, `application/merge-patch+json`.

[[[ code('352959afa8') ]]]

Ya hablamos de esto en el último tutorial: esto indica al sistema qué tipo de parche tenemos. Este es el único que se admite ahora mismo, pero sigue siendo necesario.

Si probamos esto... ¡pasa! Pero espera, ¡creo que me he engañado a mí mismo! Comenta el `status` y luego la prueba... ¿aún pasa? Sí, cámbialo por `-2 days`... y `$yesterday` por sólo `$day`.

En nuestro proveedor, hacemos que todas las demás búsquedas estén activas o completas: y la de ayer empieza como completa. ¡Ups! Cuando intentamos la prueba ahora... falla. Volvemos a añadir`status` al JSON y... ¡ya está! ¡La prueba pasa!

Entre bastidores, éste es el proceso. Uno: la API Platform llama a nuestro proveedor para obtener el `DailyQuest` de esta fecha. Dos: el serializador actualiza ese`DailyQuest` utilizando el JSON enviado en la petición. Tres: se llama al procesador de estado. Y cuatro: el `DailyQuest` se serializa de nuevo en JSON.

## Creación del procesador de estado

Excepto que... en nuestro caso, no hay paso tres... ¡porque aún no hemos creado un procesador de estado! ¡Añadamos uno!

```terminal
php bin/console make:state-processor
```

y llamémoslo `DailyQuestStateProcessor`.

Otro nombre chispeante de genialidad. Ve a comprobarlo: está vacío y lleno de potencial. 

[[[ code('7b8df4998c') ]]]

En `DailyQuest`, el procesador debe utilizarse para la operación `Patch`, así que añade `processor: DailyQuestStateProcessor::class`.

[[[ code('2a0d14c881') ]]]

Para demostrar que esto funciona, `dd($data)`.

[[[ code('df8863e830') ]]]

De acuerdo Vuelve a hacer la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchCanUpdateStatus
```

Y... ¡boom! El `status` se establece en `completed`.

Por cierto, hemos añadido la opción `processor` directamente a la operación `Patch()`, pero también podemos ponerla aquí abajo, en el atributo `#[ApiResource()]` directamente.

[[[ code('488aa261c2') ]]]

Eso no cambia nada... porque ésta es la única operación que tenemos que utiliza siquiera un procesador: Las operaciones del método GET nunca llaman a un procesador.

## Lógica del procesador de estado

De todos modos, aquí es donde normalmente guardaríamos los datos o... haríamos algo, como enviar un correo electrónico si se tratara de un recurso de la API "restablecer contraseña".

Para hacer las cosas un poco realistas, añadamos una propiedad `$lastUpdated` a`DailyQuest` y actualicémosla aquí. Añade`public \DateTimeInterface $lastUpdated`.

[[[ code('dffca36726') ]]]

Luego rellénalo dentro del proveedor de estado:`$quest->lastUpdated` es igual a `new \DateTimeImmutable()`... con algo de aleatoriedad: entre 10 y 100 días atrás.

[[[ code('2756e62cad') ]]]

Por último, dirígete al procesador de estado. Sabemos que sólo se utiliza para los objetos `DailyQuest`... así que `$data` será uno de ellos. Ayuda a tu editor con `assert($data instanceof DailyQuest)` y, más abajo,`$data->lastUpdated = new \DateTimeImmutable('now')`.

[[[ code('0e0227c99c') ]]]

¡Genial! No tenemos una aserción de prueba para ese campo, pero seguimos volcando la respuesta... y podemos verla aquí. Estoy mirando mi reloj y... es la hora correcta en mi pequeño rincón del mundo. ¡Nuestro procesador estatal está vivo!

Celébralo volviendo a la prueba y eliminando ese volcado.

A continuación: Hagamos nuestro recurso más interesante añadiendo una relación con otro recurso de la API: una relación con el tesoro del dragón.
