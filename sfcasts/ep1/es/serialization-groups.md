# Grupos de serialización: Elección de campos

Ahora mismo, que un campo de nuestra clase sea legible o escribible en la API está totalmente determinado por si esa propiedad es legible o escribible en nuestra clase (básicamente, si tiene o no un método getter o setter). Pero, ¿y si necesitas un getter o setter... pero no quieres que ese campo esté expuesto en la API? Para eso, tenemos dos opciones.

## ¿Una clase DTO?

Opción número uno: crear una clase DTO para el recurso API. Esto lo dejaremos para otro día... en un futuro tutorial. Pero, en pocas palabras, consiste en crear una clase dedicada para tu API `DragonTreasure`... y luego trasladar a ella el atributo`ApiResource`. La clave es que diseñes la nueva clase para que se parezca exactamente a tu API... porque modelar tu API será su único trabajo. Lleva un poco más de trabajo configurar las cosas, pero la ventaja es que entonces tendrás una clase dedicada a tu API. ¡Listo!

## Hola Grupos de Serialización

La segunda solución, y la que vamos a utilizar, son los grupos de serialización. Compruébalo. Sobre el atributo `ApiResource`, añade una nueva opción llamada`normalizationContext`. Si recuerdas, la "normalización" es el proceso de pasar de un objeto a una matriz, como cuando haces una petición a `GET` para leer un tesoro. El `normalizationContext` son básicamente opciones que se pasan al serializador durante ese proceso. Y la opción más importante es `groups`. Establécela en un grupo llamado `treasure:read`:

[[[ code('7093028e4d') ]]]

Hablaremos de lo que hace esto en un minuto. Pero puedes ver el patrón que estoy utilizando para el grupo: el nombre de la clase (podría ser `dragon_treasure` si quisiéramos) y luego `:read`... porque la normalización significa que estamos leyendo esta clase. Puedes nombrar estos grupos como quieras: éste es mi patrón.

Entonces... ¿qué hace eso? ¡Vamos a averiguarlo! Actualiza la documentación... y, para hacerte la vida más fácil, ve a la URL: `/api/dragon_treasures.jsonld`. ¡Uy! Ahora sólo está`treasures.jsonld`. Ya está. Y... ¡no se devuelve absolutamente nada! Vale, tenemos los campos de la hidra, pero este `hydra:member` contiene la matriz de tesoros. Devuelve un tesoro... pero aparte de `@id` y `@type`... ¡no hay campos reales!

## Cómo funcionan los grupos de serialización

Esto es lo que ocurre. En cuanto añadamos un `normalizationContext` con un grupo, cuando se normalice nuestro objeto, el serializador sólo incluirá las propiedades que tengan ese grupo. Y como no hemos añadido ningún grupo a nuestras propiedades, no devuelve nada.

¿Cómo añadimos grupos? ¡Con otro atributo! Encima de la propiedad `$name`, digamos`#[Groups]`, pulsa "tab" para añadir su declaración `use` y luego `treasure:read`. Repite esto encima del campo `$description`... porque queremos que sea legible... y luego el campo `$value`... y finalmente `$coolFactor`:

[[[ code('d97dbaf7a2') ]]]

Buen comienzo. Muévete y actualiza la ruta. Ahora... ¡ya está! Vemos `name`,`description`, `value`, y `coolFactor`.

## DenormlizaciónContexto: Control de los grupos escribibles

Ahora tenemos control sobre qué campos son legibles... y podemos hacer lo mismo para elegir qué campos deben ser escribibles en la API. Eso se llama "desnormalización", y apuesto a que adivinas lo que vamos a hacer. Copia`normalizationContext`, pégalo, cámbialo por `denormalizationContext`... y utiliza`treasure:write`:

[[[ code('6cd21e5fb1') ]]]

Ahora dirígete a la propiedad `$name` y añade `treasure:write`. Voy a saltarme`$description` (recuerda que antes borramos nuestro método `setDescription()`a propósito)... pero añade esto a `$value`... y `$coolFactor`:

[[[ code('50c5182dc1') ]]]

Oh, ¡está enfadado conmigo! En cuanto pasemos varios grupos, tenemos que convertirlo en un array. Añade algo de `[]` alrededor de esas tres propiedades. Mucho más contento.

Para comprobar si esto es A-OK, refresca la documentación... abre la ruta `PUT` y... ¡genial! Vemos `name`, `value`, y `coolFactor`, que son actualmente los únicos campos que se pueden escribir en nuestra API.

## Añadir grupos a los métodos

Sin embargo, nos faltan algunas cosas. Antes hicimos un método `getPlunderedAtAgo()`... 

[[[ code('d5521f7bac') ]]]

y queremos que se incluya cuando leamos nuestro recurso. Ahora mismo, si comprobamos la ruta, no está ahí.

Para solucionarlo, también podemos añadir grupos a los métodos anteriores. Digamos`#[Groups(['treasure:read'])]`:

[[[ code('71791d8a66') ]]]

Y cuando vayamos a comprobarlo... voilà, aparece.

Busquemos también el método `setTextDescription()`... y hagamos lo mismo:`#[Groups([treasure:write])]`:

[[[ code('18514dde07') ]]]

¡Genial! Si volvemos a la documentación, el campo no está actualmente allí... pero cuando actualizamos... y volvemos a comprobar la ruta `PUT`... `textDescription`
¡ha vuelto!

## Volver a añadir métodos

Oye, ¡ahora podemos volver a añadir cualquiera de los métodos getter o setter que eliminamos antes! Por ejemplo, quizá sí necesite un método `setDescription()` en mi código para algo. Copia`setName()` para dar pereza, pega y cambia "nombre" por "descripción" en algunos sitios.

¡Ya está! Y aunque hemos recuperado ese definidor, cuando miramos la ruta `PUT`, `description` no aparece. Tenemos un control total sobre nuestros campos gracias a los grupos de desnormalización. Haz lo mismo con `setPlunderedAt()`... porque a veces es útil -especialmente en las fijaciones de datos- poder establecer esto manualmente.

Y... ¡listo!

## Añadir valores de campo predeterminados

Ya sabemos que obtener un recurso funciona. Ahora vamos a ver si podemos crear un nuevo recurso. Haz clic en la ruta `POST`, pulsa "Probar", y... rellenemos algunos datos sobre nuestro nuevo tesoro, que es, por supuesto, un `Giant jar of pickles`. Es muy valioso y tiene un `coolFactor` de `10`. También añadiré una descripción... aunque este tarro de pepinillos habla por sí solo.

Cuando intentamos esto... vaya... obtenemos un error 500:

> Se ha producido una excepción al ejecutar una consulta: Violación no nula, `null`
> valor en la columna `isPublished`.

Hemos reducido nuestra API a sólo los campos que queremos que se puedan escribir... pero aún hay una propiedad que debe establecerse en la base de datos. Desplázate hacia arriba y busca`isPublished`. Sí, actualmente está por defecto en `null`. Cámbialo a `= false`... y ahora la propiedad nunca será `null`.

Si lo probamos... ¡el `Giant jar of pickles` se almacena en la base de datos! ¡Funciona!

A continuación: vamos a explorar algunos trucos más de serialización que nos darán aún más control.
