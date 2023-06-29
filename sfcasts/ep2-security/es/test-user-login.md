# Probar la autenticación

Vamos a crear una prueba para publicar y crear un nuevo tesoro. Digamos`public function testPostToCreateTreasure()` que devuelve `void`. Y empezamos igual que antes: `$this->browser()->post('/api/treasures')`:

[[[ code('7c51086c7f') ]]]

En este caso necesitamos enviar datos. El segundo argumento de cualquiera de estos métodos`post()` o `get()` es una matriz de opciones, que puede incluir parámetros `headers`,`query` u otras cosas. Una clave es `json`, que puedes establecer en una matriz, que se codificará en JSON para ti. Empieza enviando JSON vacío... y luego`->assertStatus(422)`. Para ver cómo es la respuesta, añade `->dump()`:

[[[ code('c655b922ff') ]]]

¡Impresionante! Copia el nombre del método de prueba. Quiero centrarme sólo en esta prueba. Para ello, ejecuta:

```terminal
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Y... ¡oh! El código de estado de respuesta actual es 401, pero se esperaba 422.

## Volcado de respuestas fallidas en el navegador

Cuando una prueba falla con el navegador, guarda automáticamente la última respuesta en un archivo... lo cual es genial. De hecho, está en el directorio `var/`. En mi terminal, puedo mantener pulsado `Command` y hacer clic para abrirlo en el navegador. Eso está muy bien. Me verás hacer esto un montón de veces.

Vale, esto devuelve un código de estado 401. Por supuesto: ¡la ruta requiere autenticación! Nuestra aplicación tiene dos formas de autenticarse: mediante el formulario de acceso y la sesión o mediante un token de API. Vamos a probar ambas, empezando por el formulario de inicio de sesión.

## Iniciar sesión durante la prueba

Para iniciar sesión como usuario... ese usuario primero tiene que existir en la base de datos. Recuerda: al inicio de cada prueba, nuestra base de datos está vacía. Nuestro trabajo consiste en llenarla con lo que necesitemos.

Crea un usuario con `UserFactory::createOne(['password' => 'pass'])` para que sepamos cuál será la contraseña. A continuación, antes de hacer la petición POST para crear un tesoro, `->post()` a `/login` y envía `json` con `email` ajustado a`$user->getEmail()` -para utilizar cualquier dirección de correo electrónico aleatoria que Faker haya elegido- y luego `password` ajustado a `pass`. Para asegurarnos de que ha funcionado, `->assertStatus(204)`:

[[[ code('1427f28a47') ]]]

Ese es el código de estado que devolvemos tras una autenticación correcta.

¡Vamos a probarlo! Muévete y ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Pasa! ¡Obtenemos el código de estado 422 y vemos los mensajes de validación!

## Atajo para iniciar sesión: actingAs()

Así que... iniciar sesión es... ¡así de fácil! Y te recomiendo que hagas una prueba que envíe un POST específico a tu ruta de inicio de sesión, como acabamos de hacer, para asegurarte de que funciona correctamente.

Sin embargo, en el resto de mis pruebas... cuando simplemente necesito autenticarme para hacer el trabajo real, hay una forma más rápida de iniciar sesión. En lugar de hacer la petición POST, digamos `->actingAs($user)`:

[[[ code('6df44ba8eb') ]]]

Esta es una forma astuta de tomar el objeto `User` e introducirlo directamente en el sistema de seguridad de Symfony sin hacer ninguna petición. Es más fácil y más rápido. Y ahora, no nos importa en absoluto cuál es la contraseña, así que podemos simplificarlo.

Vamos a comprobarlo:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Sigue bien!

## Comprobando el éxito de la creación del tesoro

Hagamos otro `POST` aquí abajo. Sigue encadenando y añade `->post()`. En realidad... me da pereza. Copia el `->post()` existente... y úsalo. Pero esta vez, envía datos reales: Voy a teclear rápidamente algunos... estos pueden ser cualquier cosa. La última clave que necesitamos es `owner`. Ahora mismo, estamos obligados a enviar el `owner` cuando creamos un tesoro. Pronto lo haremos opcional: si no lo enviamos, lo hará por defecto quien esté autentificado. Pero por ahora, ponlo en `/api/users/`y luego en `$user->getId()`. Termina con `assertStatus(201)`:

[[[ code('5cbe0b0eb7') ]]]

Porque 201 es lo que devuelve la API cuando se crea un objeto.

Muy bien, a probar:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

¡Sigue pasando! ¡Estamos en racha! Añade un `->dump()` para ayudarnos a depurar y luego una comprobación de cordura: `->assertJsonMatches()` que `name` es `A shiny thing`:

[[[ code('823be418fd') ]]]

Cuando lo probemos

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

## Enviando la cabecera Accept: application/ld+json

Ninguna sorpresa: todo verde. Pero mira la respuesta volcada: ¡no es JSON-LD! Nos devuelve JSON estándar. Puedes verlo en la cabecera `Content-Type`: `'application/json'`, no `application/ld+json`, que es lo que esperaba.

Averigüemos qué está pasando y solucionémoslo globalmente personalizando el funcionamiento del Navegador en todo nuestro conjunto de pruebas.
