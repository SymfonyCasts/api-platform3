# Prueba de Usuario + Contraseña Simple

Tenemos un bonito `DragonTreasureResourceTest`, así que vamos a hacer un bootstrap para Usuario.

## Crear la prueba de usuario

Crea una nueva clase PHP llamada, qué tal, `UserResourceTest`. Haz que extienda nuestra clase personalizada `ApiTestCase`, entonces sólo necesitamos `use ResetDatabase`:

[[[ code('b369ad30a9') ]]]

No necesitamos `HasBrowser` porque eso ya está hecho en la clase base.

Empieza con `public function testPostToCreateUser()`:

[[[ code('1b64258c5d') ]]]

Haz una petición `->post()` a `/api/users`, añade algo de `json` con `email` y`password`, y `assertStatus(201)`.

Y ahora que hemos creado el nuevo usuario, ¡vamos a probar si podemos iniciar sesión con sus credenciales! Haz otra petición `->post()` a`/login`, pasa también algo de `json` - copia los `email` y `password` de arriba - y luego `assertSuccessful()`:

[[[ code('ee7b25b647') ]]]

Vamos a probar: `symfony php bin/phpunit` y ejecuta todo el archivo`tests/Functional/UserResourceTest.php`:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Y... ¡ok! Un código de estado 422, pero 201 esperado. Veamos: esto significa que algo ha ido mal al crear el usuario. Abramos la última respuesta. ¡Ah! Culpa mía: olvidé pasar el campo obligatorio `username`: ¡estamos fallando en la validación!

Pasa `username`... puesto a cualquier cosa:

[[[ code('205e2a6f79') ]]]

Inténtalo de nuevo:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Esto es lo que quería:

> Esperaba un código de estado correcto, pero obtuve 401.

Así que el fallo está aquí abajo. Pudimos crear el usuario... pero cuando intentamos iniciar sesión, falló. Si estuviste con nosotros en el [episodio uno](https://symfonycasts.com/screencast/api-platform), ¡tal vez recuerdes por qué! Nunca configuramos nuestra API para hacer hash de la contraseña.

Compruébalo: dentro de `User`, sí hicimos que `password` formara parte de nuestra API. El usuario envía la contraseña en texto plano que desea... y nosotros la guardamos directamente en la base de datos. Eso es un gran problema de seguridad... y hace imposible iniciar sesión como este usuario, porque Symfony espera que la propiedad `password` contenga una contraseña con hash.

## Configurar el campo plainPassword

Así que nuestro objetivo está claro: permitir al usuario enviar una contraseña sin formato, pero luego hashearla antes de almacenarla en la base de datos. Para ello, en lugar de almacenar temporalmente la contraseña en texto plano en la propiedad `password`, vamos a crear una propiedad totalmente nueva: `private ?string $plainPassword = null`:

[[[ code('8b66503faa') ]]]

Ésta no se almacenará en la base de datos: es sólo un lugar temporal para guardar la contraseña en texto plano antes de que le apliquemos el hash y la establezcamos en la propiedad real `password`.

Abajo del todo, iré a "Código"->"Generar", o `Command`+`N` en un Mac, y generaré un "Getter y setter" para esto. Limpiemos esto un poco: acepta sólo una cadena, y el PHPDoc es redundante:

[[[ code('23c26e6fc6') ]]]

A continuación, desplázate hasta la parte superior y encuentra `password`. Elimínalo por completo de nuestra API:

[[[ code('583934746d') ]]]

En su lugar, expone `plainPassword`... pero utiliza `SerializedName` para que se llame`password`:

[[[ code('fb55688523') ]]]

Obviamente, aún no hemos terminado... y si ejecutas las pruebas:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

¡Las cosas van peor! Un error 500 debido a una violación de no nulo. Estamos enviando `password`, que se almacena en `plainPassword`... y luego no hacemos absolutamente nada con él. Así que la propiedad real `password` permanece nula y explota cuando llega a la base de datos.

Así que aquí está la pregunta del millón: ¿cómo podemos hacer hash de la propiedad `plainPassword`? O, en términos más sencillos, ¿cómo podemos ejecutar código en API Platform después de que los datos se deserialicen pero antes de que se guarden en la base de datos? La respuesta es: procesadores de estado. Vamos a sumergirnos en este potente concepto a continuación.
