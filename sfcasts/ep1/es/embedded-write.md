# Escritura incrustada

Voy a probar la ruta GET one treasure endpoint... utilizando un identificador real. Perfecto. Debido a los cambios que acabamos de hacer, el campo `owner` está incrustado.

¿Y si cambiamos el propietario? Pan comido: siempre que el campo sea escribible... y el nuestro lo es. Ahora mismo el `owner` es el id 1. Utiliza la ruta PUT para actualizar el id 2. Para la carga útil, establece `owner` en `/api/users/3`.

Y... ¡a ejecutar! ¡Bah! Error de sintaxis. JSON está malhumorado. Elimina la coma, inténtalo de nuevo y... ¡sí! El `owner` vuelve como el IRI `/api/users/3`.

## Enviar datos incrustados a Update

¡Pero ahora quiero hacer algo salvaje! Este tesoro pertenece al usuario 3. Vamos a obtener sus datos. Abre la ruta GET un usuario, pruébala, introduce 3 y... ¡ahí está! El nombre de usuario es `burnout400`.

Éste es el objetivo: al actualizar un `DragonTreasure` -por tanto, al utilizar la ruta PUT a `/api/treasures/{id}` -, en lugar de cambiar de un propietario a otro, quiero cambiar el `username` del propietario existente. Algo así: en lugar de establecer`owner` a la cadena IRI, establecerla a un objeto con `username` asignado a algo nuevo.

¿Funcionaría? ¡Experimentemos! Pulsa Ejecutar y no funciona. Dice

> No se permiten documentos anidados para el atributo `owner`, utiliza en su lugar IRI.

## Permitir la incrustación de propiedades escribibles

Así que, a primera vista, parece que esto no está permitido: parece que aquí sólo puedes utilizar una cadena IRI. Pero, en realidad, sí está permitido. El problema es que el campo`username` no es escribible mediante esta operación.

Pensemos en esto. Estamos actualizando un `DragonTreasure`. Esto significa que API Platform está utilizando el grupo de serialización `treasure:write`. Ese grupo está por encima de la propiedad `owner`, por lo que podemos modificar el `owner`.

[[[ code('6f3c3ee5de') ]]]

Pero si queremos poder cambiar el `username` del propietario, entonces también tenemos que entrar en `User` y añadir ese grupo aquí.

[[[ code('caffaf1dfd') ]]]

Esto funciona exactamente igual que los campos incrustados cuando los leemos. Básicamente, como al menos un campo de `User` tiene el grupo `treasure:write`, ahora podemos enviar un objeto al campo `owner`.

## Objetos nuevos vs existentes en datos incrustados

Observa: enciéndelo de nuevo. Funciona... casi. Obtenemos un error 500:

> Se ha encontrado una nueva entidad a través de la relación `DragonTreasure.owner`,
> pero no se ha configurado para persistir en `cascade`.

Woh. Esto significa que el serializador vio nuestros datos, creó un nuevo objeto `User` y luego configuró el `username` en él. Doctrine falló porque nunca le dijimos que persistiera el nuevo objeto `User`.

Aunque... esa no es la cuestión: ¡la cuestión es que no queremos un nuevo `User`! Queremos coger al propietario existente y actualizar su `username`.

Por cierto, para que este ejemplo sea más realista, añadamos también un `name` a la carga útil para que podamos fingir que estamos actualizando realmente el tesoro... y decidamos actualizar también el `username` del propietario mientras estamos en el vecindario.

En cualquier caso: ¿cómo le decimos al serializador que utilice el propietario existente en lugar de crear uno nuevo? Añadiendo un campo `@id` configurado con el IRI del usuario: `/api/users/3`.

Ya está Cuando el serializador ve un objeto, si no tiene un `@id`, crea un objeto nuevo. Si tiene `@id`, encuentra ese objeto y le asigna cualquier dato.

Así pues, llega el momento de la verdad. Cuando lo probamos... por supuesto, otro error de sintaxis. ¡Ponte las pilas, Ryan! Después de arreglarlo... ¡perfecto! ¡Un código de estado 200! Aunque... realmente no podemos ver si actualizó el `username` aquí... ya que sólo muestra el propietario.

Utiliza la ruta GET one `User`... busca al usuario 3... ¡y comprueba esos dulces datos! Sí cambió el `username`.

Vale, me doy cuenta de que este ejemplo puede no haber sido el más realista, pero poder actualizar objetos relacionados tiene muchos casos de uso reales.

## Utilizar Persistir en cascada para crear un nuevo objeto

Volviendo a la petición de `PUT`, ¿qué pasaría si quisiéramos crear y guardar un nuevo objeto`User`? ¿Es posible? Pues sí

En primer lugar, tendríamos que añadir un `cascade: ['persist']` al atributo `treasure.owner``ORM\Column` . Esto es algo que veremos más adelante. Y en segundo lugar, tendríamos que asegurarnos de exponer todos los campos obligatorios como escribibles. Ahora mismo sólo `username` es escribible... así que no podríamos enviar `password` o `email`.

## La restricción válida

Antes de continuar, nos falta un pequeño, pero importante, detalle. Intentemos esta actualización una vez más con el `@id`. Pero establece `username` en una cadena vacía.

Recuerda que el campo `username` tiene un `NotBlank` encima, por lo que debería fallar la validación. Y sin embargo, cuando lo intentamos, ¡obtenemos un código de estado 200! Y si vamos a la ruta GET de un usuario... sí, ¡el `username` ahora está vacío! Eso es... un problema.

¿Cómo ha ocurrido? Por cómo funciona el sistema de validación de Symfony.

La entidad de nivel superior -el objeto que estamos modificando directamente- es `DragonTreasure`. Así que el sistema de validación mira `DragonTreasure` y ejecuta todas las restricciones de validación. Sin embargo, cuando llega a un objeto como la propiedad `owner`, se detiene. No sigue validando también ese objeto.

Si quieres que eso ocurra, tienes que añadir una restricción a esto llamada`Assert\Valid`.

[[[ code('8928ebc1be') ]]]

Ahora... en nuestra ruta PUT... si lo intentamos de nuevo, ¡sí! 422: `owner.username`, este valor no debe estar en blanco.

La posibilidad de actualizar un objeto incrustado es muy útil y potente. Pero el coste de esto es hacer que tu API sea cada vez más compleja. Así que, aunque puedes optar por hacer esto -y deberías hacerlo si es lo que quieres-, también podrías optar por obligar al cliente de la API a actualizar primero el tesoro... y luego hacer una segunda petición para actualizar el nombre de usuario del usuario... en lugar de permitirle que lo haga todo de lujo al mismo tiempo.

A continuación: veamos esta relación desde el otro lado. Cuando estamos actualizando un `User`, ¿podríamos actualizar también los tesoros que pertenecen a ese usuario? ¡Averigüémoslo!