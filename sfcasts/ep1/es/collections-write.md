# Añadir elementos a una propiedad de colección

Vamos a buscar un único usuario en nuestra API: Sé que existe uno con ID 2. Y ¡genial!

Como hemos aprendido antes, exponer una propiedad de relación de colección es como cualquier otro campo: simplemente asegúrate de que está en el grupo de serialización correcto. Y luego puedes ir más allá con los grupos de serialización para elegir entre hacer que se devuelva como una matriz de cadenas IRI o como una matriz de objetos incrustados, como tenemos ahora.

Nueva pregunta: ¿podríamos modificar también el `dragonTreasures` que posee un usuario desde una de las operaciones de usuario? La respuesta es, por supuesto, ¡sí! Y vamos a hacerlo de formas cada vez más locas.

## Hacer que el campo Colección sea escribible

Mira la ruta POST. Ahora mismo no vemos un campo `dragonTreasures` porque... el campo simplemente no es escribible: no está en el grupo correcto. Para remediarlo, sabemos qué hacer: añadir `user:write`.

[[[ code('961aab3c0d') ]]]

¡Muy fácil! Cuando actualizamos los documentos y comprobamos esa ruta... ya está:`dragonTreasures`. Y dice que este campo debe ser una matriz de cadenas: una matriz de cadenas IRI.

Intentemos crear un nuevo usuario. Rellenemos los campos `email` y `username`. A continuación, asignemos el nuevo usuario a algunos tesoros existentes. Vamos a colarnos en la ruta GET de colección de tesoros... e impresionante. Tenemos los ids 2, 3 y 4.

Aquí abajo, asigna `owner` a una matriz con `/api/treasures/2`, `/api/treasures/3`y `/api/treasures/4`.

Tiene sentido, ¿verdad? Si la API puede devolver `dragonTreasures` como una matriz de cadenas IRI, ¿por qué no podemos enviar una matriz de cadenas IRI? Cuando pulsamos Ejecutar... ¡efectivamente! ¡Funcionó perfectamente!

Y como cada tesoro sólo puede tener un propietario... ¡eso significa que, en cierto modo, robamos esos tesoros a otra persona! ¡Perdón!

## Los métodos sumador y eliminador de colecciones

Pero... espera un segundo, ¿cómo ha funcionado? Sabemos que cuando enviamos campos como`email`, `password`, y `username`, como son propiedades privadas, el serializador llama a los métodos setter. Cuando pasamos `username`, llama a`setUsername()`.

[[[ code('fbc23eb8f2') ]]]

Así que cuando pasamos `dragonTreasures`, debe llamar a `setDragonTreasures`, ¿no?

Pues, ¿adivina qué? ¡No tenemos un método `setDragonTreasures()`! Pero tenemos un método `addDragonTreasure()` y un método `removeDragonTreasure()`.

[[[ code('598a552058') ]]]

El serializador es muy inteligente. Ve que el nuevo objeto `User` no tiene`dragonTreasures`. Así que reconoce que cada uno de estos tres objetos son nuevos para este usuario y por eso llama a `addDragonTreasure()` una vez para cada uno.

Y la forma en que MakerBundle ha generado estos métodos es fundamental. Toma el nuevo `DragonTreasure` y establece que el `owner` sea este objeto. Esto es importante por la forma en que Doctrine maneja las relaciones: establecer el propietario establece lo que se denomina el lado "propietario" de la relación. Básicamente, sin esto, Doctrine no guardaría este cambio en la base de datos.

Las conclusiones son que, gracias a `addDragonTreasure()` y sus poderes mágicos, el `owner` del `DragonTreasure` se cambia de su antiguo propietario al nuevo `User`, y todo se guarda exactamente como queremos.

A continuación, vamos a ponernos más complejos permitiendo que se creen tesoros cuando estamos creando un nuevo `User`. También vamos a permitir que se eliminen tesoros de un `User`... para el improbable caso de que los enanos recuperen la montaña. Como si tal cosa.
