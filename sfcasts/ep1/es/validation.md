# Validación

Próximamente...

Hay muchas formas de que los usuarios de nuestra API estropeen las cosas, como una J O incorrecta o datos incorrectos, como introducir un número negativo en el campo de valor. Así que vamos a comprobar cómo funcionan las API con algunas de ellas en este momento. Así que voy a darle a probar en la ruta post y vamos a añadir invalid. Vamos a enviar algunos mal js o, voy a quitar esa coma allí mismo. Ejecutar e impresionante 400 errores. Así que eso es lo que queremos. Un cuatro. Cualquier error que empiece por cuatro significa que el cliente, el usuario de su API ha cometido un error. Así que 400 significa petición errónea. Y puedes ver aquí abajo que el tipo es error hidroco y que tiene un error ocurrido y un error syntex. Y esta traza sólo se muestra en el entorno de depuración. Así que esto no se mostraría en producción. Así que eso es bastante impresionante. Eso ya se maneja fuera de la caja. Vamos a probar algo diferente. Probemos a enviar un json vacío, como si nos hubiéramos olvidado de enviar alguno de nuestros campos. Eso es un aire 500 no tan genial internamente API Platform crea nuestro objeto tesoro dragón pero no le pone ningún dato y como que explota cuando llega a la base de datos porque algunas de las columnas están nu

Y, por supuesto, esperábamos esto. Lo que nos falta es validación y añadir validación a nuestra API es como añadir validación en cualquier parte de Symphony. Es muy sencillo. Así, por ejemplo, encontramos la propiedad nombre, queremos que nombre sea obligatorio. Así que voy a añadir el no en blanco decir no en blanco y pulsa tabulador para añadir que usted declaración. ¿Y sabes qué? Vamos a hacer también un oh y en realidad está bien, pero voy a buscar ese not blank aquí arriba. Y cambiar esto a asert. Así es como se suelen hacer las cosas dentro de Symphony. Y diré assert barra no en blanco. Y luego mi abajo, vamos a añadir uno más. Voy a decir longitud y diremos que el nombre debe tener al menos dos caracteres a lo largo de un máximo, eh, de 50 caracteres. Y aquí está el mensaje máximo. Describe tu botín en 50 caracteres o menos. Genial. Así que probemos ahora cogeré ese mismo json vacío, le daré a ejecutar y genial Una respuesta 4 22, que es un código de respuesta muy común que básicamente significa error de validación. Y fíjate en este tipo, es una lista de violación de restricciones. Se trata de un formato especial J S O N L D. Puede que no lo recuerdes, pero antes lo vimos documentado en la documentación de JSUN LD. Así que voy a ir a esa barra api barra do

Barra api barra docs punto jsun LD y a ti para buscar una violación de restricción. Ahí lo tienes. Así que en realidad hay clases integradas como violación de restricciones y lista de violaciones de restricciones integradas en nuestra A P I junto con nuestro recurso de tesorería. Y puedes ver cuál es su estructura. Una lista de violaciones de restricciones es, básicamente, una colección de violaciones de restricciones y describe las propiedades de las violaciones de restricciones. Y podemos verlas aquí. Es bastante impresionante. Y hay una propiedad de violaciones y muestra la ruta de la propiedad y luego tiene el mensaje debajo. Muy bien, vamos a añadir algunas cosas más. Así que vamos a, vamos a añadir por encima de la propiedad descripción. Añadiremos no en blanco y encima del valor añadiremos mayor o igual que cero. Así que tiene que ser, no puede ser negativo. Y por último, factor guay utilizaremos mayor o igual que cero. Y luego añadiremos un segundo de esos. Cámbialo a menor o igual que 10. Así que algo entre cero y 10. Y ya que estamos aquí, no necesitamos hacer esto, pero voy a inicializar el valor a cero y el factor de enfriamiento a cero. Así que si no estaba establecido, podemos simplemente, hace que esos campos no sean necesarios en la api. Se inicializarán a cero si no están configurados.

Ahora voy a volver y probar esa misma ruta, ver esa hermosa validación e incluso podemos activar un poco más añadiendo un factor de enfriamiento de 11. Sí, a nuestro sistema definitivamente no le gusta eso. Muy bien, hay una última forma de que falle la validación. Es pasando el tipo incorrecto. Así que el factor de enfriamiento 11 fallará nuestras reglas de validación, pero ¿y si en lugar de eso le pasamos una cadena? Una que hubiéramos ejecutado, vale, 400 códigos de estado. Eso está bien. Falla con un código de estado de nivel 400. No es un error de validación, tiene un tipo diferente, pero le dice al usuario lo que ha pasado. El tipo de factor guay debe ser una cadena INT dada. Así que el punto no es válido. Jason se encarga. Los tipos malos están solucionados

En el caso de los tipos incorrectos, el sistema ve este tipo INT en el factor de enfriamiento establecido. Y lo rechaza con este error de aquí. Así que de lo único que tenemos que preocuparnos en nuestra aplicación es de escribir un buen código que utilice correctamente los pines de tipo y B, añadiendo nuestra validación. Añadiendo validación para nuestras reglas de negocio, como que el valor debe ser mayor que cero o que la descripción es obligatoria. La API Platform se encargará del resto. Muy bien, a continuación, nuestra API sólo tiene un recurso en este momento es nuestro tesoro dragón. Vamos a añadir un segundo recurso, un recurso de usuario, para que podamos vincular qué usuario posee qué tesoro en la api.