# Recurso API de usuario

Tenemos una entidad `User`... pero aún no forma parte de nuestra API. ¿Cómo hacemos que forme parte de la API? Ah, ¡ya lo sabemos! Ve encima de la clase y añade el atributo `ApiResource`.

[[[ code('92949c6bd5') ]]]

Actualiza la documentación. ¡Fíjate! ¡Seis nuevas rutas para la clase `User`! Y gracias a nuestros fixtures, deberíamos poder ver los datos inmediatamente. Probemos la ruta de recogida. Ejecuta y... está vivo.

Aunque... es un poco raro que aparezcan campos como `roles` y `password`. Ah, nos preocuparemos de eso en un minuto.

## API Platform y UUIDs

Antes de seguir avanzando, quiero mencionar una cosa rápida sobre los UUID. Como puedes ver, estamos utilizando UUID autoincrementados para nuestra API: siempre es`/api/users/` y luego el id de la entidad. Pero puedes utilizar un `UUID` en su lugar. Y eso es algo que haremos en un futuro tutorial.

Pero... ¿por qué utilizar UUIDs? Bueno, a veces puede hacer la vida más fácil en JavaScript cuando se trabaja con frameworks frontales. De hecho, puedes generar el`UUID` en JavaScript y luego enviarlo a tu API al crear un nuevo recurso. Esto puede ser útil porque tu JavaScript conoce el identificador del recurso inmediatamente y puede actualizar el estado... en lugar de esperar a que termine la petición Ajax para obtener el nuevo identificador autoincrementado.

En cualquier caso, lo que quiero decir es que API Platform admite `UUIDs`. Podrías añadir una nueva columna UUID y decirle a API Platform que ese debe ser tu identificador. Ah, pero ten en cuenta que algunos motores de bases de datos -como MySQL- pueden tener un rendimiento deficiente si haces del UUID la clave primaria. En ese caso, mantén `id` como clave principal y añade una columna UUID adicional.

## Añadir los grupos de serialización

En cualquier caso, ¡volvamos a nuestro recurso `User`! Ahora mismo, devuelve demasiados campos. Afortunadamente, sabemos cómo solucionarlo. Arriba, en `ApiResource`, añade una clave`normalizationContext` con `groups` establecida en `user:read` para seguir el mismo patrón que utilizamos en `DragonTreasure`. Añade también `denormalizationContext`fijado en `user:write`.

[[[ code('815bd94371') ]]]

Ahora sólo tenemos que decorar los campos que queramos en la API. No necesitamos `id`... ya que siempre tenemos `@id`, que es más útil. Pero sí queremos `email`. Así que añade el atributo `#Groups()`, pulsa tabulador para añadir esa declaración `use` y pasa `user:read` y `user:write`.

[[[ code('6d252dc798') ]]]

Copia eso... y baja a `password`. Necesitamos que la contraseña sea escribible pero no legible. Así que añade `user:write`.

[[[ code('8e24ec96a7') ]]]

Esto todavía no es del todo correcto. El campo `password` debe contener la contraseña cifrada. Pero nuestros usuarios, por supuesto, enviarán las contraseñas en texto plano a través de la API cuando creen un usuario o actualicen su contraseña. Entonces haremos el hash. Eso es algo que resolveremos en un tutorial futuro, cuando hablemos más de seguridad, pero esto bastará por ahora.

Ah, y encima de `username`, añade también `user:read` y `user:write`.

[[[ code('51e0656fd4') ]]]

¡Genial! Actualiza los documentos... y abre la ruta de las colecciones para probarlo. El resultado... ¡exactamente lo que queríamos! Sólo vuelven `email` y `username`.

Y si creáramos un nuevo usuario... ¡sí! Los campos escribibles son `email`,`username`, y `password`.

## Añadir validación

Vale, ¿qué más nos falta? ¿Qué tal la validación? Si probamos la ruta POST con datos vacíos... obtendremos el desagradable error 500. ¡Hora de arreglarlo!

De nuevo en el archivo, empieza por encima de la clase para asegurarte de que tanto `email` como`username` son `unique`. Añade `UniqueEntity` pasando `fields` a `email`... e incluso podemos incluir un mensaje. Repite lo mismo... pero cambia `email`por `username`.

[[[ code('80dc514174') ]]]

A continuación, abajo en `email`, añade `NotBlank`... luego añadiré el `Assert` delante... y retocaré la declaración `use` para que funcione igual que la última vez.

[[[ code('be7c823ff0') ]]]

Bien. el correo electrónico necesita uno más - `Assert\Email` - y encima de `username`, añadir `NotBlank`.

[[[ code('0d0ce6f949') ]]]

Ahora mismo no me preocupa demasiado `password`... porque ya es un poco raro.

¡Vamos a probar esto! Desplázate hacia arriba y envía un campo `password`. Y... ¡sí! El simpático código de estado 422 con errores de validación. Prueba ahora con datos válidos: pasa un `email` y un `username`... aunque no estoy seguro de que este tipo sea realmente un dragón... quizá necesitemos un captcha.

Pulsa Ejecutar. Ya está ¡Código de estado 201 con `email` y `username` devueltos!

Nuestro recurso tiene validación, paginación y contiene una gran información! E incluso podríamos añadir filtros fácilmente. En otras palabras, ¡lo estamos machacando!

Y ahora llegamos a la parte realmente interesante. Tenemos que "relacionar" nuestros dos recursos para que cada tesoro pertenezca a un usuario. ¿Qué aspecto tiene eso en API Platform? Es superinteresante, y es lo siguiente.
