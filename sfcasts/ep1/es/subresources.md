# Subrecursos

Tenemos dos formas distintas de obtener los tesoros dragón de un usuario. La primera, podríamos obtener el `User` y leer su propiedad `dragonTreasures`. La segunda es a través del filtro que hemos añadido hace un momento. En la API, eso parece `owner=/api/users/4` en la operación de recogida de tesoros `GET`.

Esta es mi forma habitual de obtener los datos... porque si quiero obtener tesoros, tiene sentido utilizar una ruta `treasures`. Además, si un usuario posee muchos tesoros, ¡eso nos dará paginación!

Pero a veces puedes optar por añadir una forma especial de obtener un recurso o una colección de recursos... casi como una URL de vanidad. Por ejemplo, imagina que, para obtener esta misma colección, queremos que el usuario pueda ir a`/api/users/4/treasures.jsonld`. Eso, por supuesto, no funciona. Pero se puede hacer. Esto se llama un subrecurso, y los subrecursos son mucho más agradables en la API Platform 3.

## Añadir un Subrecurso a través de otro ApiResource

Bien, pensemos. Esta ruta devolverá tesoros. Así que para añadir este subrecurso, tenemos que actualizar la clase `DragonTreasure`.

¿Cómo? Añadiendo un segundo atributo `ApiResource`. Ya tenemos este principal, así que ahora añade uno nuevo. Pero esta vez, controla la URL con una opción `uriTemplate` ajustada exactamente a lo que queremos: `/users/{user_id}` para la parte del comodín (veremos cómo se utiliza en un momento) y luego `/treasures`.

Ya está Bueno... añade también `.{_format}`. Esto es opcional, pero es la magia que nos permite "hacer trampas" y añadir este `.jsonld` al final de la URL.

[[[ code('dc8d25efc7') ]]]

A continuación, añade `operations`... porque no necesitamos los seis... en realidad sólo necesitamos uno. Entonces, digamos `[new GetCollection()]` porque devolveremos una colección de tesoros.

[[[ code('e69163ede0') ]]]

Vale, ¡vamos a ver qué ha hecho esto! Vuelve a la documentación y actualízala. De repente tenemos... ¡tres recursos y éste tiene la URL correcta!

Ah, y tenemos tres recursos porque, si recuerdas, hemos personalizado el`shortName`. Cópialo y pégalo en el nuevo `ApiResource` para que coincidan. Y para contentar a PhpStorm, los pondré en orden.

[[[ code('81664bdefe') ]]]

Ahora cuando actualicemos... ¡perfecto! ¡Eso es lo que queríamos!

## Comprender las uriVariables

Ahora tenemos una nueva operación para obtener tesoros. Pero, ¿funciona? Dice que recuperará una colección de recursos de tesoros, así que eso está bien. Pero... tenemos un problema. Piensa que tenemos que pasar el `id` de un `DragonTreasure`... ¡pero debería ser el id de un `User`! E incluso si pasamos algo, como `4`... y pulsamos "Ejecutar" ... ¡mira la URL! Ni siquiera ha utilizado el `4`: ¡sigue teniendo`{user_id}` en la URL! Así que, por supuesto, vuelve con un error 404.

El problema es que tenemos que ayudar a API Platform a entender qué significa `{user_id}`. Tenemos que decirle que ése es el id del usuario y que debe utilizarlo para consultar `WHERE owner_id` es igual al valor.

Para ello, añade una nueva opción llamada `uriVariables`. Aquí es donde describimos cualquier "comodín" de tu URL. Pasa `user_id` ajustado a un objeto `new Link()`. Hay varios... queremos el de `ApiPlatform\Metadata`.

[[[ code('7b22cfd797') ]]]

Este objeto necesita dos cosas. Primero, apuntar a la clase a la que se refiere `{user_id}`. Hazlo pasando una opción `fromClass` establecida en `User::class`.

[[[ code('0110937d99') ]]]

En segundo lugar, necesitamos definir qué propiedad de `User` apunta a `DragonTreasure`para que pueda averiguar cómo estructurar la consulta. Para ello, establece `fromProperty`en `treasures`. Así, dentro de `User`, estamos diciendo que esta propiedad describe la relación. Ah, pero lo he estropeado todo: la propiedad es `dragonTreasures`.

[[[ code('7598afa4de') ]]]

Vale, vuelve y actualiza. Debajo de la ruta... ¡sí! Dice "Identificador de usuario". Volvamos a poner `4`, le damos a "Ejecutar" y... ya está. ¡Ahí están los cinco tesoros de este usuario!

Y en la otra pestaña del navegador... si refrescamos... ¡funciona!

## Cómo se hace la consulta

Entre bastidores, gracias a `Link`, API Platform realiza básicamente la siguiente consulta:

> SELECT * FROM dragon_treasure WHERE owner_id =

lo que pasemos por `{user_id}`. Sabe cómo hacer esa consulta mirando la relación Doctrine y averiguando qué columna utilizar. Es superinteligente.

De hecho, podemos verlo en el perfilador. Ve a `/_profiler`, haz clic en nuestra petición... y, aquí abajo, vemos 2 consultas... que son básicamente iguales: la 2ª se utiliza para el "total de elementos" para la paginación.

Si haces clic en "Ver consulta formateada" en la consulta principal... ¡es aún más compleja de lo que esperaba! Tiene un `INNER JOIN`... pero básicamente está seleccionando todos los datos de tesoros de dragones donde `owner_id` = el ID de ese usuario.

## ¿Qué pasa con toProperty?

Por cierto, si echas un vistazo a la documentación, también hay una forma de configurar todo esto a través del otro lado de la relación: diciendo `toProperty: 'owner'`.

Esto sigue funcionando... y funciona exactamente igual. Pero yo recomiendo seguir con`fromProperty`, que es coherente y, creo, más claro. El `toProperty` sólo es necesario si no has mapeado el lado inverso de una relación... como si no hubiera una propiedad `dragonTreasures` en `User`. A menos que te encuentres en esa situación, quédate con `fromProperty`.

## ¡No olvides la normalizaciónContexto!

Todo esto funciona muy bien, excepto por un pequeño problema. Si vuelves a mirar los datos, ¡muestra los campos equivocados! Lo devuelve todo, como `id` y`isPublished`.

Se supone que no deben incluirse gracias a nuestros grupos de normalización. Pero como no hemos especificado ningún grupo de normalización en el nuevo `ApiResource`, el serializador lo devuelve todo.

Para solucionarlo, copia el `normalizationContext` y pégalo aquí abajo. No tenemos que preocuparnos por `denormalizationContext` porque no tenemos ninguna operación que haga ninguna desnormalización.

[[[ code('b5e1afb5bb') ]]]

Si refrescamos ahora... ¡lo tenemos!

## Una única ruta de subrecursos

Vamos a añadir un subrecurso más para ver un caso ligeramente distinto. Primero te mostraré la URL que quiero. Tenemos un tesoro con el ID `11`. Esto significa que podemos ir a `/api/treasures/11.jsonld` para verlo. Ahora quiero poder añadir `/owner` al final para obtener el usuario al que pertenece este tesoro. Ahora mismo, eso no funciona .... así que ¡manos a la obra!

Como el recurso que se devolverá es un `User`, esa es la clase que necesita el nuevo Recurso API.

Sobre ella, añade `#[ApiResource()]` con `uriTemplate` ajustado a`/treasures/{treasure_id}` para el comodín (aunque puede llamarse como quieras), seguido de `/owner.{_format}`.

[[[ code('afc3ca23c9') ]]]

A continuación, pasa `uriVariables` con `treasure_id` establecido en `new Link()` - el de`ApiPlatform\Metadata`. Dentro, fija `fromClass` a `DragonTreasure::class`. Y como la propiedad dentro de `DragonTreasure` que hace referencia a esta relación es`owner`, añade `fromProperty: 'owner'`.

[[[ code('6e2a5a0e44') ]]]

También sabemos que vamos a necesitar el `normalizationContext`... así que cópialo... y pégalo aquí. Por último, sólo queremos una operación: una operación `GET` para devolver un único `User`. Así que añade `operations` ajustado a `[new Get()]`.

[[[ code('faf3b7ba68') ]]]

¡Ya está! Vuelve a la documentación, actualízala y echa un vistazo en "Usuario". ¡Sí! ¡Tenemos una nueva operación! E incluso ve que el comodín es un "identificador DragonTreasure".

Si actualizamos la otra pestaña... ¡funciona!

Vale, equipo, he mentido al decir que éste era el último tema porque... ¡es hora del tema extra! A continuación: vamos a crear automáticamente un área de administración basada en React a partir de nuestros documentos de la API. Vaya.
