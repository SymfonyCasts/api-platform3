# Aserciones de prueba JSON avanzadas y flexibles

También podríamos querer probar que obtenemos los campos correctos en la respuesta para cada elemento. ¿Podemos hacerlo con JMESPath? ¡Claro que sí! El método `assertJsonMatches()` es realmente práctico. Y en realidad, si mantienes pulsado comando o control y haces clic en él, cuando llamamos a `assertJsonMatches()`, entre bastidores, llama a`$this->json()`. Esto crea un objeto `Json`... que tiene métodos aún más útiles. La propia instancia `Browser` nos da acceso a `assertJsonMatches()`. Pero si queremos utilizar cualquiera de sus otros métodos, tenemos que hacer un poco más de trabajo.

La primera forma de utilizar el objeto `Json` es a través del método `use()` del navegador. Pásale una llamada de retorno con un argumento `Json $json`:

[[[ code('db1f653785') ]]]

Esta es una característica mágica del navegador: lee la sugerencia de tipo del argumento y sabe que debe pasarnos el objeto `Json`. También podrías pasarle un objeto `CookieJar`,`Crawler` o algunas otras cosas.

La cuestión es que, como hemos indicado el argumento con `Json`, cogerá el objeto`Json` de la última respuesta y nos lo pasará. Vamos a utilizarlo para hacer algunos experimentos. Queremos comprobar cuáles son las claves del primer elemento dentro de `hydra:member`. Para ayudarnos a averiguar la expresión que necesitamos, vamos a utilizar un método llamado `search()`. Esto nos permite utilizar una expresión `JMESPath` y obtener el resultado. Haz las comillas dobles y luego `hydra:member` para ver lo que devuelve. Y... elimina el otro volcado:

[[[ code('ced732216b') ]]]

¡Vale! Ejecuta de nuevo la prueba:

```terminal-silent
symfony php bin/phpunit
```

Pasa... pero lo más importante, ¡mira el volcado! Es la matriz de 5 elementos. Ok... vamos a coger el índice `0`. Después de las comillas dobles `hydra:member`, añade`[0]`. A continuación, rodea todo con una función `keys()` de JMESPath:

[[[ code('7a62bc21e6') ]]]

Pruébalo ahora:

```terminal-silent
symfony php bin/phpunit
```

Qué bonito. Y probablemente sea una de las cosas más complejas que harás. Ahora que tenemos la ruta correcta, conviértela en una aserción. Puedes hacerlo estableciendo esto en una variable -como `$keys` - y utilizando una aserción normal. O puedes cambiar `search` por `assertMatches()` y pasarle un segundo argumento: la matriz de los campos esperados:

[[[ code('e0389f65ab') ]]]

¡Ya está! Pruébalo:

```terminal-silent
symfony php bin/phpunit
```

¡Pasa! Y sí, ahora podríamos eliminar el método `use()` y pasar esto a una llamada normal a `->assertJsonMatches()`.

## Hacer aserciones JSON normales

Por muy guay que sea esto de JMESPath, es otra cosa que hay que aprender y puede resultar complejo. ¿Cuál es la alternativa?

Asignar toda la cadena `$browser` a una nueva variable `$json` y luego añadir `->json()`al final. La mayoría de los métodos de `Browser` devuelven... un `Browser`, lo que nos permite hacer todo el encadenamiento divertido. Pero unos pocos, como `->json()` nos permiten "salir" del navegador para que podamos hacer algo personalizado.

Esto nos permite eliminar aquí la función `use()` y sustituir las aserciones por código PHPUnit más tradicional:

[[[ code('761a3f0758') ]]]

Podríamos seguir utilizando directamente el objeto `Json`... que pasa... o para eliminar toda fantasía, cambiar a `$this->assertSame()` que`$json->decoded()['hydra:member'][0]` - `array_keys()` alrededor de todo - coincide con nuestra matriz:

[[[ code('f2b6f026c3') ]]]

Y por supuesto... ¡que pase a!

Así pues, mucha potencia... pero también mucha flexibilidad para escribir pruebas como quieras.

A continuación, vamos a añadir pruebas para la autenticación: tanto para iniciar sesión a través de nuestro formulario de acceso como a través de un token de API.
