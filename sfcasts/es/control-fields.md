# Controlar campos sin grupos

Cuando tu recurso API está en una entidad, los grupos de serialización son imprescindibles porque seguro que tendrás algunas propiedades que querrás mostrar o no mostrar. Pero los grupos de serialización añaden complejidad. Una de las grandes ventajas de tener una clase independiente para tu API es no necesitar grupos de serialización. Porque... el objetivo de tu clase API es representar tu API... así que, en teoría, querrás que todas las propiedades formen parte de tu API.

Pero, en el mundo real, eso no siempre es cierto. Y acabamos de encontrarnos con un caso:`password` debería ser un campo de sólo escritura. Intentemos reproducir parte de la complejidad que tenía originalmente nuestra entidad `User`, pero evitando los grupos de serialización.

En `UserResourceTest`, aquí abajo, eliminamos la propiedad `->dump()`... y después de`->assertStatus(201)`, afirmamos que la propiedad `password` no se devuelve. Para ello, podemos decir `->use(function(Json $json))`. La función `use()` proviene del navegador y hay algunos objetos diferentes -como `Json` - que puedes pedirle que te pase a través de la sugerencia de tipo. En este caso, el navegador toma el JSON de la última respuesta, lo pone en un objeto `Json` y nos lo pasa. Utilízalo diciendo `$json->assertMissing('password')`.

Si lo intentamos ahora

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Falla porque `password` sí existe.

## legible: falso

Bien, vamos a dar una vuelta por cómo podemos personalizar nuestros campos API sin grupos. Uno de los más fáciles (y, casualmente, mi favorito) es utilizar `#[ApiProperty()]`con `readable: false`.

Queremos que esto sea escribible, pero no legible.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Y... ¡eso arregla las cosas! Estupendo.

Repitámoslo para `id`... porque `id` es bastante inútil ya que tenemos `@id`. Cuando lo ejecutamos... falla porque se devuelve `id`. Así que ahora, copia... sólo la parte de `readable: false`... añade `#[ApiProperty]` encima de `id`, pega, y también añadiré `identifier: true`... sólo para ser explícito.

Y ahora...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Eso pasa.

## escribible: false

Sigamos. Copia el nombre de la siguiente prueba - `testPatchToUpdateUser` - y ejecútala:

```terminal
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

¡Pasa inmediatamente! Yupi! `->patch()` ya funciona. Para profundizar en otras formas en que podemos ocultar o mostrar campos, envía también un campo `flameThrowingDistance` en el JSON establecido en 999. Y aquí abajo, `->dump()` la respuesta.

Antes de probar esto, busca `EntityClassDtoStateProcessor`. Justo después de establecer el `id`, `dump($data)`. Esos dos volcados nos ayudarán a entender exactamente cómo funciona todo esto.

Ahora ejecuta la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

Y... impresionante. El primer volcado de arriba -del procesador de estado- muestra`flameThrowingDistance` 999, lo que significa que el campo es escribible. Y abajo, la respuesta devuelve 999, lo que significa que el campo también es legible. Sí... se trata de un campo normal y aburrido. Si el usuario envía el campo en JSON, ese nuevo valor se deserializa en el objeto.

Bien, ¡hora de experimentar! En `UserApi`, encima de la propiedad, empieza con las mismas`#[ApiProperty()]` y `readable: false`. Esto ya lo hemos visto.

Cuando ejecutamos la prueba, en la parte superior, el "999" se escribió en el `UserApi`, pero no aparece en la respuesta. Se puede escribir, pero no leer.

Si además pasamos `writable: false`... y volvemos a intentarlo. Arriba, el valor es sólo "10". El campo no es escribible, por lo que se ignoró el campo en el JSON. Tampoco aparece en la respuesta: no es legible ni escribible.

Las opciones de legible/escribible por sí solas probablemente resolverán la mayoría de las situaciones. Pero a continuación, vamos a aprender otros trucos y a ver por qué probablemente quieras asegurarte de que tu identificador no es escribible.
