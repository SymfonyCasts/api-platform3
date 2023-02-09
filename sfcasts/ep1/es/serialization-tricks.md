# Trucos de serialización

En cierto modo, hemos engañado al sistema para que permita un campo `textDescription` cuando enviamos datos. Esto es posible gracias a nuestro método `setTextDescription()`, que ejecuta`nl2br()` en la descripción que se envía a nuestra API. Esto significa que el usuario envía un campo `textDescription` cuando edita o crea un tesoro... pero recibe un campo `description` cuando lo lee.

[[[ code('a7af4c4e40') ]]]

Y eso está totalmente bien: está permitido tener diferentes campos de entrada frente a campos de salida. Pero sería un poco más guay si, en este caso, ambos se llamaran simplemente`description`.

## SerializedName: Controlar el nombre del campo

Entonces... ¿podemos controlar el nombre de un campo? Por supuesto que sí Lo hacemos, como ya habrás previsto, mediante otro maravilloso atributo. Éste se llama`SerializedName`. Pásale `description`:

[[[ code('a7af4c4e40') ]]]

Esto no cambiará cómo se lee el campo, pero si actualizamos los documentos... y miramos la ruta `PUT`... ¡sí! Ahora podemos enviar un campo llamado `description`.

## Argumentos del constructor

¿Qué pasa con los argumentos del constructor en nuestra entidad? Cuando hacemos una petición a `POST`, por ejemplo, sabemos que utiliza los métodos setter para escribir los datos en las propiedades.

Ahora prueba esto: busca `setName()` y elimínalo. Luego ve al constructor y añade allí un argumento`string $name` en su lugar. A continuación, di `$this->name = $name`.

[[[ code('2f2c83cd90') ]]]

Desde una perspectiva orientada a objetos, el campo puede pasarse cuando se crea el objeto, pero después es de sólo lectura. Diablos, si quisieras ponerte elegante, podrías añadir `readonly` a la propiedad.

Veamos qué aspecto tiene esto en nuestra documentación. Abre la ruta `POST`. ¡Parece que aún podemos enviar un campo `name`! Haz la prueba pulsando "Probar"... y añadamos un `Giant slinky` que ganamos a un gigante de la vida real en... una partida de póquer bastante tensa. Es bastante valioso, tiene un `coolFactor` de `8`, y dale un`description`. Veamos qué ocurre. Pulsa "Ejecutar" y... ¡ha funcionado! Y podemos ver en la respuesta que se ha fijado el `name`. ¿Cómo es posible?

Bueno, si bajas y miras la ruta `PUT`, verás que aquí también se anuncia `name`. Pero... sube a buscar el id del tesoro que acabamos de crear - para mí es el 4, pon el 4 aquí para editarlo... y luego envía sólo el campo nombre para cambiarlo. Y... ¡no cambió! Sí, igual que con nuestro código, una vez creado un `DragonTreasure`, el nombre no se puede cambiar.

Pero... ¿cómo hizo la petición a `POST` para establecer el nombre... si no hay ningún establecedor? La respuesta es que el serializador es lo suficientemente inteligente como para establecer los argumentos del constructor... si el nombre del argumento coincide con el nombre de la propiedad. Sí, el hecho de que el argumento se llame `name`y la propiedad también se llame `name` es lo que hace que esto funcione.

Observa: cambia el argumento a `treasureName` en ambos lugares:

[[[ code('2f2c83cd90') ]]]

Ahora, gira, actualiza y comprueba la ruta POST. El campo ha desaparecido. 
API Platform ve que tenemos un argumento `treasureName` que podría enviarse, pero como `treasureName` no corresponde a ninguna propiedad, ese campo no tiene ningún grupo de serialización. Así que no se utiliza. Lo cambiaré de nuevo a `name`:

[[[ code('2ed74f72ac') ]]]

Al utilizar `name`, mira la propiedad `name`, y lee sus grupos de serialización.

## Args del constructor opcionales frente a obligatorios

Sin embargo, sigue habiendo un problema con los argumentos del constructor que debes tener en cuenta. Actualiza la documentación.

¿Qué pasaría si nuestro usuario no pasara ningún `name`? Pulsa "Ejecutar" para averiguarlo. De acuerdo Obtenemos un error con un código de estado 400... pero no es un error muy bueno. Dice

> No se puede crear una instancia de `App\Entity\DragonTreasure` a partir de datos serializados
> porque su constructor requiere que el parámetro `name` esté presente.

Eso es... en realidad demasiado técnico. Lo que realmente queremos es permitir que la validación se encargue de esto... y hablaremos de la validación en breve. Pero para que la validación funcione, el serializador tiene que poder hacer su trabajo: tiene que poder instanciar el objeto:

[[[ code('089e7c4dc2') ]]]

Vale, inténtalo ahora... ¡mejor! Vale, es peor: un error 500, pero lo arreglaremos con la validación dentro de unos minutos. El caso es que el serializador ha sido capaz de crear nuestro objeto.

A continuación: Para ayudarnos mientras desarrollamos, vamos a añadir un rico conjunto de accesorios de datos. Luego jugaremos con una gran función que API Platform nos ofrece gratuitamente: la paginación
