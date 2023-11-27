# Relaciones e IRI

Cuando intentamos crear un `DragonTreasure` con este `owner`, establecimos el campo con el id de la base de datos del propietario. Y nos dimos cuenta de que a API Platform no le gustaba eso. Decía "IRI esperado". Pero, ¿qué es un `IRI`?

Ya mencionamos este término una vez en el tutorial. Vuelve al punto final de la colección GET `/api/users`. Sabemos que cada recurso tiene un campo `@id` establecido en la URL donde puedes obtener ese recurso. Esto es el IRI o "Identificador Internacional de Recursos". Está pensado para ser un identificador único en toda tu API, como en todos los recursos.

Piénsalo: el número "1" no es un identificador único -podríamos tener un`DragonTreasure` con ese id y un `User`. Pero el IRI es único. Y, además, una URL es mucho más manejable que un número entero.

Así que cuando queramos establecer una propiedad de relación, tendremos que utilizar también el IRI, como`/api/users/1`.

Cuando pulsamos Ejecutar, ¡funciona! Un código de estado `201`. En el JSON devuelto, no es de extrañar, el campo `owner` también aparece como un IRI.

Las conclusiones de todo esto son deliciosamente sencillas. Las relaciones son campos normales... pero las obtenemos y establecemos a través de su cadena IRI. Es una forma muy bonita y limpia de manejarlo.

## Añadir un campo de relación Colección dragonTreasures

Bien, hablemos del otro lado de esta relación. Actualiza toda la página y ve a la ruta `GET` one user endpoint. Inténtalo con un identificador de usuario real, como el 1 para mí. Y... ahí están los datos.

Así que la pregunta que me hago ahora es: ¿podríamos añadir un campo `dragonTreasures` que muestre todos los tesoros que posee este usuario?

Bueno, vamos a pensarlo. Sabemos que el serializador funciona cogiendo todas las propiedades accesibles de un objeto que están en el grupo de normalización. Y... tenemos una propiedad `dragonTreasures` en `User`.

[[[ code('d9e44f867d') ]]]

Así que... ¡debería funcionar! Para exponer el campo a la API, añádelo al grupo de serialización `user:read`. Más adelante, hablaremos de cómo podemos escribir en un campo de colección... pero por ahora, basta con hacerlo legible.

[[[ code('0e4763871e') ]]]

Vale Actualiza... y mira la misma ruta `GET`. Aquí abajo, ¡genial! Muestra un nuevo campo `dragonTreasures` en la respuesta del ejemplo. Vamos a probarlo: utiliza el mismo id, pulsa "Ejecutar" y... ¡oh, estupendo: devuelve una matriz de cadenas IRI! ¡Me encanta! Y, por supuesto, si necesitamos más información sobre ellas, podemos hacer una petición a cualquiera de estas URL para obtener todos los detalles brillantes.

Y para ser realmente extravagante, podrías utilizar Vulcain para que los usuarios puedan "precargar" esas relaciones... lo que significa que el servidor enviará los datos directamente al cliente.

Pero aunque esto es genial, me lleva a una pregunta: ¿y si necesitar los datos de`DragonTreasure` para un usuario es tan habitual que, para evitar peticiones adicionales, queremos incrustar los datos aquí mismo, como objetos JSON en lugar de cadenas IRI?

¿Podemos hacerlo? Por supuesto que sí. Averigüemos cómo a continuación.
