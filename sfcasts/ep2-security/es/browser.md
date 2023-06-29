# Aserciones de la prueba JSON y siembra de la base de datos

Hagamos realidad esta prueba con datos y aserciones.

Hay dos formas principales de hacer aserciones con Browser. En primer lugar, viene con un montón de métodos incorporados para ayudarte, como `->assertJson()`. O... siempre puedes coger el JSON que vuelve de una ruta y comprobar las cosas utilizando las aserciones incorporadas de PHPUnit que conoces y adoras. Veremos ambas cosas.

Empecemos comprobando `->assertJson()`:

[[[ code('6d27846281') ]]]

Cuando lo ejecutemos:

```terminal-silent
symfony php bin/phpunit
```

¡Pasa! ¡Genial! Sabemos que esta respuesta debe tener una propiedad `hydra:totalItems`establecida en el número de resultados. Ahora mismo, nuestra base de datos está vacía... pero al menos podemos afirmar que coincide con cero.

Para ello, utiliza `->assertJsonMatches()`.

Se trata de un método especial de Browser que utiliza una sintaxis especial que nos permite leer diferentes partes del JSON. Profundizaremos en ello dentro de un minuto.

Pero éste es sencillo: afirma que `hydra:totalItems` es igual a `0`:

[[[ code('732b364ada') ]]]

Cuando intentamos esto

```terminal-silent
symfony php bin/phpunit
```

¡Falla! Pero con un gran error:

> `mtdowling/jmespath.php` es necesario para buscar JSON

## Hola JMESPath

Ah, ¡necesitamos instalarlo! Copia la línea `composer require`, busca tu terminal y ejecútalo:

```terminal-silent
composer require mtdowling/jmespath.php --dev
```

Esto de "JMESPath" es en realidad superguay: es un "lenguaje de consulta" para leer distintas partes de cualquier JSON. Por ejemplo, si éste es tu JSON y quieres leer la clave `a`, sólo tienes que decir `a`. Sencillo.

Pero también puedes hacer cosas más profundas, como `a.b.c.d`. O ponte más loco: coge el índice `1`, o coge `a.b.c`, luego el índice `0`, `.d`, el índice `1` y luego el índice `0`. Incluso puedes cortar la matriz de diferentes maneras. Básicamente... puedes volverte loco.

Pero no vamos a perder la cabeza con esto. Es una sintaxis práctica... pero si las cosas se ponen demasiado complejas, siempre podemos probar el JSON manualmente, cosa que haremos dentro de un rato.

De todos modos, ahora que tenemos la biblioteca instalada, volvamos a ejecutar la prueba.

```terminal-silent
symfony php bin/phpunit
```

¡Sigue fallando! Con un extraño error:

> Error de sintaxis en el carácter 5 `hydra:totalItems`.

Por desgracia, el `:` es un carácter especial dentro de JMESPath. Así que siempre que tengamos un `:`, tenemos que poner comillas alrededor de esa clave:

[[[ code('a1e5a46dba') ]]]

No es lo ideal, pero no es un gran inconveniente.

Ahora, cuando lo probamos

```terminal-silent
symfony php bin/phpunit
```

¡Pasa!

## Sembrar la base de datos

Pero... ésta no es una prueba muy interesante: sólo estamos afirmando que no obtenemos nada de vuelta... porque la base de datos está vacía. Para que nuestra prueba sea real, necesitamos datos: necesitamos sembrar la base de datos con datos al inicio de la prueba.

Afortunadamente, Foundry lo hace muy sencillo. Arriba, llama a`DragonTreasureFactory::createMany()` y creemos 5 tesoros. Ahora, abajo, afirma que obtenemos 5 resultados:

[[[ code('2ce69e5a6f') ]]]

Así de sencillo. Y, de hecho, déjame que vuelva a poner nuestro volcado para que podamos ver el resultado:

[[[ code('d6551be2e2') ]]]

Pruébalo ahora:

```terminal-silent
symfony php bin/phpunit
```

¡Pasa! Y si miras hacia arriba, ¡sí! ¡La respuesta tiene 5 tesoros! Caray, qué fácil.

A continuación: utilicemos JMESPath para afirmar algo más desafiante. Luego retrocederemos y veremos cómo podemos profundizar en Browser para darnos una flexibilidad -y simplicidad- infinitas a la hora de probar JSON.
