# Campos condicionales por usuario: ApiProperty

Controlamos qué campos son legibles y escribibles mediante grupos de serialización. Pero, ¿y si tienes un campo que debe incluirse en la API... pero sólo para determinados usuarios? Lamentablemente, los grupos no pueden hacer ese tipo de magia por sí solos.

Por ejemplo, busca el campo `$isPublished` y hagamos que forme parte de nuestra API añadiendo los grupos `treasure:read` y `treasure:write`:

[[[ code('c28ce9037f') ]]]

Ahora si giramos y probamos las pruebas:

```terminal-silent
symfony php bin/phpunit
```

Esto hace que falle una prueba: `testGetCollectionOfTreasures` ve que se devuelve `isPublished`... y no lo espera.

Éste es el plan: colaremos el campo en nuestra API, pero sólo para usuarios administradores o propietarios de este `DragonTreasure`. ¿Cómo podemos conseguirlo?

## Hola ApiProperty

Bueno, ¡sorpresa! No solemos necesitarlo, pero podemos añadir un atributo `ApiProperty` encima de cualquier propiedad para ayudar a configurarla mejor. Tiene un montón de cosas, como una descripción que ayuda con tu documentación y muchos casos extremos. Incluso hay uno llamado `readable`. Si dijéramos `readable: false`:

[[[ code('be66f00679') ]]]

Entonces los grupos de serialización dirían que esto debería incluirse en la respuesta... pero entonces esto lo anularía. Observa: si probamos las pruebas:

```terminal-silent
symfony php bin/phpunit
```

Pasan porque el campo no está.

## La opción de la seguridad

Para nuestra misión, podemos aprovechar una opción superguay llamada `security`. Ponla en `is_granted("ROLE_ADMIN")`:

[[[ code('d62a931a94') ]]]

¡Eso es! Si esta expresión devuelve false, `isPublished` no se incluirá en la API: no se podrá leer ni escribir.

Y cuando ahora ejecutamos las pruebas:

```terminal-silent
symfony php bin/phpunit
```

Siguen pasando, lo que significa que no se devuelve `isPublished`. 

Ahora vamos a probar la ruta "feliz" en la que se devuelve este campo. Abre`DragonTreasureResourceTest`. Aquí está la prueba original: `testGetCollectionOfTreasures()`. Somos anónimos, así que `isPublished` no se devuelve.

Ahora desplázate hasta `testAdminCanPatchToEditTreasure()`. Cuando creemos`DragonTreasure`, asegurémonos de que siempre empieza por `isPublished => false`:

[[[ code('8b20a16c96') ]]]

Luego, aquí abajo, `assertJsonMatches('isPublished', false)` para comprobar que se devuelve el campo:

[[[ code('fc9330ddee') ]]]

Copia el nombre de la prueba, gira y añade `--filter` para ejecutar sólo esa prueba:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

Y... ¡pasa! El campo se devuelve cuando somos administradores.

## Devolver también isPublished para el propietario

¿Y si somos el propietario del tesoro? Copia la prueba... cámbiale el nombre a `testOwnerCanSeeIsPublishedField()`... y vamos a retocar algunas cosas. Cambia el nombre de `$admin` a `$user`, simplifícalo a `DragonTreasureFactory::createOne()`y asegúrate de que `owner` se establece en nuestro nuevo `$user`:

[[[ code('ed6dba3c3e') ]]]

Podríamos cambiar esto por una petición GET... pero PATCH está bien. En cualquiera de las dos situaciones, queremos asegurarnos de que se devuelve el campo `isPublished`.

Como aún no hemos implementado esto... asegurémonos de que falla. Copia el nombre del método y pruébalo:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

¡Fallo conseguido! ¡Y ya sabemos cómo solucionarlo! En la opción `security`, podríamos alinear la lógica con `or object.getOwner() === user`. Pero recuerda: ¡hemos creado el votante para que no tengamos que hacer locuras como ésa! En lugar de eso, di `is_granted()`, `EDIT` y luego `object`:

[[[ code('82eec19413') ]]]

Haz la prueba ahora:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

## El especial seguridadPostDenormalizar

¡Ya está! Ah, y no la he utilizado mucho, pero también existe la opción `securityPostDenormalize`. Al igual que con la opción `securityPostDenormalize` en cada operación, ésta se ejecuta después de que los nuevos datos se deserialicen en el objeto. Lo interesante es que si la expresión devuelve `false`, en realidad se revierten los datos del objeto.

Por ejemplo, supongamos que la propiedad `isPublished` comenzó como `false` y luego el usuario envió algo de JSON para cambiarla a `true`. Pero entonces, `securityPostDenormalize` devolvió`false`. En ese caso, API Platform revertirá la propiedad `isPublished` a su valor original: la cambiará de `false` a `true`. Ah, y por cierto, `securityPostDenormalize` no se ejecuta en las peticiones a `GET`: sólo ocurre cuando se están deserializando los datos. Así que asegúrate de poner tu lógica de seguridad principal en `security` y sólo utiliza `securityPostDenormalize` si lo necesitas.

Lo siguiente en nuestra lista de tareas: vamos a nivelar nuestras operaciones de usuario para hacer un hash de la contraseña antes de guardarla en la base de datos. Necesitaremos una nueva propiedad de contraseña simple no persistente para hacerlo.