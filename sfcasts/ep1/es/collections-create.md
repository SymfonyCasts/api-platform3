# Crear objetos incrustados

¿Es posible crear un `DragonTreasure` totalmente nuevo cuando creamos un usuario? Como... ¿en lugar de enviar el IRI de un tesoro existente, enviamos un objeto?

¡Vamos a intentarlo! Primero, cambiaré esto por un correo electrónico y un nombre de usuario únicos. Después, para`dragonTreasures`, borra esos IRI y, en su lugar, pasa un objeto JSON con los campos que sabemos que son obligatorios. ¡Nuestro nuevo usuario dragón acaba de conseguir una copia de GoldenEye para N64! Legendario. Añade un `description`... y un `value`.

En teoría, ¡este cuerpo JSON tiene sentido! ¿Pero funciona? Pulsa "Ejecutar" y... ¡no! Bueno, todavía no. ¡Pero conocemos este error!

> No se permiten documentos anidados para el atributo `dragonTreasures`. Utiliza IRI en su lugar.

## Cómo hacer que dragonTreasures acepte objetos JSON

Dentro de `User`, si nos desplazamos hacia arriba, la propiedad `$dragonTreasures` es escribible porque tiene `user:write`. 

[[[ code('e683c98090') ]]]

Pero no podemos enviar un objeto para esta propiedad porque no hemos añadido `user:write` a ninguno de los campos dentro de `DragonTreasure`. Arreglemos eso.

Queremos poder enviar `$name`, así que añade `user:write`... Me saltaré `$description`pero haré lo mismo con `$value`. Ahora busca `setTextDescription()` que es la descripción real. Añade `user:write` aquí también.

[[[ code('4a50c1c7c7') ]]]

Vale, en teoría, ahora deberíamos poder enviar un objeto incrustado. Si nos dirigimos y lo intentamos de nuevo... ¡obtenemos un error 500!

> Se ha encontrado una nueva entidad a través de la relación `User#dragonTreasures`

## Persistencia de una relación de entidad en cascada

¡Esto es genial! Ya sabemos que cuando envías un objeto incrustado, si incluyes`@id`, el serializador recuperará primero ese objeto y luego lo actualizará. Pero si no tienes `@id`, creará un objeto totalmente nuevo. Ahora mismo, está creando un objeto nuevo,... pero nada le ha dicho al gestor de entidades que lo persista. Por eso obtenemos este error.

Para solucionarlo, necesitamos persistir en cascada esta propiedad. En `User`, en la opción`OneToMany` para `$dragonTreasures`, añade una opción `cascade` establecida en `['persist']`.

[[[ code('5138b52b1c') ]]]

Esto significa que si estamos guardando un objeto `User`, debería persistir mágicamente cualquier `$dragonTreasures` que haya dentro. Y si lo probamos ahora... ¡funciona! Es increíble! Y aparentemente, nuestro nuevo tesoro `id` es `43`.

Abramos una nueva pestaña del navegador y naveguemos hasta esa URL... más `.json`... en realidad, hagamos `.jsonld`. ¡Estupendo! Vemos que el `owner` está establecido para el nuevo usuario que acabamos de crear.

## ¿Cómo se estableció el propietario? De nuevo: Los métodos inteligentes

Pero... ¡aguanta! No enviamos el campo `owner` en los datos del tesoro... entonces, ¿cómo se estableció ese campo? Bueno, en primer lugar, tiene sentido que no enviáramos un campo `owner` para el nuevo `DragonTreasure`... ¡ya que el usuario que será su propietario ni siquiera existía todavía! Vale, entonces, ¿pero quién estableció el `owner`?

Entre bastidores, el serializador crea primero un nuevo objeto `User`. Después, crea un nuevo objeto `DragonTreasure`. Finalmente, ve que el nuevo `DragonTreasure`aún no está asignado al `User`, y llama a `addDragonTreasure()`. Cuando lo hace, el código de aquí abajo establece el `owner`: tal y como vimos antes. Así que nuestro código bien escrito se está ocupando de todos esos detalles por nosotros.

[[[ code('9d186c36cf') ]]]

## Añadir la restricción válida

De todos modos, quizá recuerdes de antes que en cuanto permitimos que un campo de relación envíe datos incrustados... tenemos que añadir una cosita. No lo haré, pero si enviáramos un campo `name` vacío, se crearía un `DragonTreasure`... con un`name` vacío, aunque, por aquí, si nos desplazamos hasta la propiedad `name`, ¡es obligatorio! Recuerda: cuando el sistema valide el objeto `User`, se detendrá en`$dragonTreasures`. No validará también esos objetos. Si quieres validarlos, añade `#[Assert\Valid]`.

[[[ code('daf001e866') ]]]

Ahora que tengo esto, para comprobar que funciona, pulsa "Ejecutar" y... ¡genial! Obtenemos un código de estado 422 que nos indica que `name` no debería estar vacío. Voy a volver a ponerlo.

## Enviar objetos incrustados y cadenas IRI al mismo tiempo

Ahora sabemos que podemos enviar cadenas IRI u objetos incrustados para una propiedad de relación, suponiendo que hayamos configurado los grupos de serialización para permitirlo. E incluso podemos mezclarlos.

Digamos que queremos crear un nuevo objeto `DragonTreasure`, pero también vamos a robar, tomar prestado, un tesoro de otro dragón. Esto está totalmente permitido. ¡Mira! Cuando pulsamos "Ejecutar"... obtenemos un código de estado 201. Esto devuelve los identificadores de tesoro `44` (que es el nuevo) y `7`, que es el que acabamos de robar.

Vale, ya sólo nos queda un capítulo sobre el manejo de las relaciones. Veamos cómo podemos quitar un tesoro a un usuario para eliminar ese tesoro. Eso a continuación.
