# Clase de usuario Dto

La forma más rápida de empezar a utilizar la API Platform es añadir estos atributos `#[ApiResource]`sobre tus clases de entidad. Esto se debe a que la API Platform te proporciona proveedores de estado gratuitos que consultan desde la base de datos (lo que incluye paginación y filtros) y procesadores de estado gratuitos que guardan cosas en la base de datos.

## ¿Usar DTOs o no?

Pero, como hemos visto con `DailyQuest`, eso no es necesario. Y si tu API empieza a parecer muy diferente de tus entidades -como si tuvieras campos en tu API que no existen en tu entidad o que se denominan de forma diferente- podría tener sentido separar tus clases de entidad y de recurso API.

Ahora mismo, nuestras entidades son recursos de la API... y eso ha añadido cierta complejidad. Por ejemplo, tenemos un campo personalizado `isMine` que se alimenta de esta propiedad`isOwnedByAuthenticatedUser`: una propiedad no persistente que rellenamos mediante un proveedor de estado. Y una de las cosas más notables es nuestro enorme uso de grupos de serialización. Tenemos que utilizar grupos de serialización, como `treasure:read`, para poder incluir las propiedades que queremos y evitar las que no queremos.

Esto nos ha ahorrado algo de tiempo... pero ha aumentado la complejidad. Así que vamos a volvernos locos y utilizar desde el principio una clase dedicada para nuestra API. A menudo se denomina "DTO", u "Objeto de Transferencia de Datos". Utilizaré mucho ese término, pero para nosotros sólo significa "la clase dedicada a nuestra API", como la clase `DailyQuest`.

## Eliminar las cosas de la API del usuario

Muy bien, amigos, ¡comienza la limpieza! Es hora de eliminar toda la suciedad relacionada con la API de nuestra prístina entidad `User`. Elimina el atributo `#[ApiResource()]`... los dos, filtros y validación. Puede que sigas queriendo restricciones de validación si estás utilizando tu entidad con el sistema de formularios... pero como no es nuestro caso, vamos a borrarlo. También voy a despejar todo lo relacionado con la serialización... y a cazar, con suerte, todo lo que esté oculto.

Vaya. Esta clase es mucho más pequeña ahora. Creo que eso es todo... las declaraciones `use`de la parte superior se ven bien... así que... ¡genial!

Vamos a eliminar también el procesador de estados para `User`, que realiza el hash de la contraseña simple. Vamos a volver a implementar muchas de las cosas que acabamos de eliminar, pero quiero empezar con un aspecto limpio de las cosas.

Muy bien, ve a consultar los documentos de la API. Nos hemos reducido a "Búsqueda" y "Tesoro". ¡Me encanta!

## Creación de la clase DTO / Dedicated ApiResource

Vamos a empezar como hicimos con `DailyQuest`. En el directorio `src/ApiResource/`, crea una nueva clase llamada `UserApi`... para indicar que es la clase de usuario de nuestra API. Dentro, añade `#[ApiResource]` encima.

[[[ code('56fc6ad442') ]]]

Hasta aquí, esto es como cualquier otro recurso personalizado de la API. Aparece en los documentos... y si intentamos la operación de recopilación `GET`, falla con un 404. Diablos, incluso nos falta la parte "ID" en la URL de las operaciones de los elementos.

Para solucionarlo, en `UserApi`, añade una propiedad `public ?int $id = null`... porque nuestros usuarios seguirán siendo identificados por su id de base de datos. Ah, y estoy utilizando una propiedad pública sólo para facilitarnos la vida... y porque esta clase seguirá siendo sencilla, así que no es gran cosa.

[[[ code('f222a7cb25') ]]]

En el momento en que hagamos esto... API Platform reconoce que `id` es el identificador, y nuestras operaciones se ven bien.

Ya que estamos aquí, vamos a modificar también el `shortName`. Este se llama `UserApi`, que es un nombre horrible - así que cámbialo: `shortName: 'User'`.

[[[ code('7592f4dcdc') ]]]

De repente... ¡esto empieza a parecerse a lo que teníamos antes!

Las grandes piezas que faltan, como en `DailyQuest`, son el proveedor de estado y el procesador de estado. Vamos a añadir el proveedor de estado siguiente.... pero con un giro que aprovecha una nueva función que nos va a ahorrar un montón de trabajo.
