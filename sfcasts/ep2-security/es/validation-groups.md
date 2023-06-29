# Grupos de validación y formatos de parche

Ahora que la propiedad `plainPassword` es una parte legítima de nuestra API, añadamos algo de validación... ¡porque no puedes crear un nuevo usuario sin contraseña! Añade`Assert\NotBlank`:

[[[ code('3c58a50bac') ]]]

¡Pan comido! Bueno, eso acaba de crear un nuevo problema... pero avancemos a ciegas y finjamos que todo va bien.

Copia la primera prueba y pégala para crear un segundo método que nos asegure que podemos actualizar los usuarios. Llámalo `testPatchToUpdateUser()`. Este es sencillo: crea un nuevo usuario - `$user = UserFactory::createOne()`, añade `actingAs($user)` luego `->patch()`a `/api/users/` luego `$user->getId()` para editarnos a nosotros mismos.

Para el `json`, basta con enviar `username`, añadir `assertStatus(200)`.... entonces no necesitamos ninguna de estas otras cosas:

[[[ code('2255e56fb9') ]]]

Como recordatorio, arriba en la operación `Patch` para `User`... aquí está, estamos requiriendo que el usuario tenga `ROLE_USER_EDIT`. Como estamos entrando como usuario "completo", deberíamos tenerlo... y todo debería funcionar bien... famosas últimas palabras.

Ejecuta:

```terminal
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

## PATCH: El método HTTP más interesante del mundo

Y... ¡oh! 200 esperado, obtuvo 415. ¡Eso es nuevo! Haz clic para abrir la última respuesta... luego veré la fuente para que quede más claro. Interesante:

> No se admite el tipo de contenido: `application/json`. Los tipos MIME admitidos son
> `application/merge-patch+json`.

Desmenucemos esto. Estamos haciendo una petición a `PATCH`... y las peticiones a `PATCH` son bastante sencillas: enviamos un subconjunto de campos, y sólo se actualizan esos campos.

Resulta que el método HTTP `PATCH` puede ser mucho más interesante que esto. En la gran interwebs, hay formatos que compiten por el aspecto que deben tener los datos cuando se utiliza una petición PATCH, y cada formato significa algo diferente.

Actualmente, API Platform sólo admite uno de estos formatos: `application/merge-patch+json` este formato es... más o menos lo que esperas. Dice: si envías un único campo, sólo se modificará ese único campo. Pero también tiene otras reglas, como que podrías establecer `email` en `null`... y eso en realidad eliminaría el campo `email`. Eso no tiene mucho sentido en nuestra API, pero la cuestión es: el formato define reglas sobre el aspecto que debe tener tu JSON para una petición `PATCH` y lo que eso significa. Si quieres saber más, hay un [documento que lo describe todo](https://www.rfc-editor.org/rfc/rfc7386): es bastante breve y legible.

Así que, de momento, API Platform sólo admite un formato para las peticiones PATCH. Pero, en el futuro, podrían admitir más. Y así, cuando haces una petición`PATCH`, API Platform requiere que envíes una cabecera `Content-Type` establecida en `application/merge-patch+json`... de modo que le estás diciendo explícitamente a API Platform qué formato está utilizando tu JSON.

En otras palabras, para solucionar nuestro error, pasa una clave `headers` con `Content-Type` establecido en `application/merge-patch+json`:

[[[ code('d055fac9e6') ]]]

Inténtalo ahora:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

Sigue fallando, ¡pero ahora es un error de validación! Las conclusiones son sencillas: Las peticiones PATCH requieren esta cabecera `Content-Type`.

Pero, ¡espera! Hicimos un montón de peticiones a `PATCH` en `DragonTreasureResourceTest`y ¡funcionaron bien sin la cabecera! ¿Qué?

Eso... fue un poco por accidente. Dentro de `DragonTreasure`, en el primer tutorial... aquí está, añadimos una clave `formats` para poder añadir soporte CSV:

[[[ code('18f2b56f4b') ]]]

Resulta que, por algunas complejas razones internas, al añadir `formats`, eliminamos el requisito de necesitar esa cabecera. Así que nos estábamos "saliendo con la nuestra" al no fijar la cabecera en `DragonTreasureResourceTest`... aunque deberíamos fijarla. Quizá hubiera sido mejor establecer `formats` sólo en la operación `GetCollection`... ya que ése es el único punto en el que necesitamos CSV.

En fin, por eso antes no lo necesitábamos, pero ahora sí. Por cierto, si añadir esta cabecera cada vez que llamas a `->patch` te resulta molesto, ésta es otra situación en la que podrías añadir un método personalizado al navegador -como `->apiPatch()` - que funcionaría igual, pero añadiría esa cabecera automáticamente.

## Arreglar los grupos de validación

Vale, ¡volvamos a la prueba! Está fallando con un 422. Abre la respuesta de error. Ah, es de `plainPassword`: ¡este campo no debería estar en blanco!

La propiedad `plainPassword` no se persiste en la base de datos. Por tanto, siempre está vacía al inicio de una petición a la API. Cuando creamos un `User`, queremos absolutamente que este campo sea obligatorio. Pero cuando editamos un `User`, no necesitamos que este campo esté establecido. Pueden establecerlo para cambiar su contraseña, pero eso es opcional.

Este es el primer punto en el que necesitamos validación condicional: la validación debe producirse en una operación, pero no en otras. La forma de solucionarlo es con grupos de validación, que es muy similar a los grupos de serialización.

¡Busca la operación `Post` y pásale una nueva opción llamada`validationContext` con, lo has adivinado, `groups`! Colócalo en una matriz con un grupo llamado `Default` con D mayúscula. Luego inventa un segundo grupo:`postValidation`:

[[[ code('c95494bea0') ]]]

Cuando el validador valida un objeto, por defecto, valida todo lo que está en un grupo llamado `Default`. Y cada vez que tienes una restricción, por defecto esa restricción está en ese grupo `Default`. Así que lo que estamos diciendo aquí es

> Queremos validar todas las restricciones normales más cualquier restricción
> que estén en el grupo `postValidation`.

Ahora podemos coger ese `postValidation`, bajar a `plainPassword` y poner`groups` en `postValidation`:

[[[ code('3cd32444f9') ]]]

Eso elimina esta restricción del grupo `Default` y sólo la incluye en el grupo `postValidation`. Gracias a esto, otras operaciones como `Patch`no la ejecutarán, pero sí la operación `Post`.

Ejecuta ahora la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

¡Somos imparables! De hecho, ¡todas nuestras pruebas están pasando!

## Cuidado: PUT puede crear objetos

Pero ¡cuidado! En `User`, seguimos teniendo tanto `Put` como `Patch`. Aún no he jugado mucho con ello, pero el nuevo comportamiento `Put`, en teoría, sí admite la creación de objetos. Esto puede complicar las cosas: ¿necesitamos exigir la contraseña o no? Depende Ésta podría ser otra razón para eliminar la operación `Put` y simplificar las cosas. Así tenemos una operación para crear y otra para editar.

Siguiente: vamos a explorar la posibilidad de hacer que nuestros grupos de serialización sean dinámicos en función del usuario, lo que nos dará otra forma de incluir o no incluir campos en función de quién esté conectado. Y nos llevará a añadir campos superpersonalizados.
