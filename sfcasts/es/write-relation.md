# Campos de relación escribibles

Abre `DragonTreasureResourceTest` y consulta`testPostToCreateTreasureWithLogin()`. Hemos hablado mucho sobre cómo hacer que nuestros recursos puedan devolver campos de relación. El truco principal es simplemente rellenar esos campos desde dentro de nuestro mapeador de datos. Luego, API Platform se encarga de transformarlos en IRI.

Una cosa de la que no hemos hablado es de poder escribir en uno de esos campos de relación.

## Escribir en la Propiedad del propietario

Cuando utilizamos esta ruta `post()`, no necesitamos enviar un campo `owner`. Eso es porque, anidado en `DragonTreasureApiToEntityMapper`, tenemos código que dice

> Si no se envía un `owner` en el JSON, establécelo automáticamente
> al usuario autenticado actualmente.

Pero, se te permite enviar la propiedad `owner` y establecerla a ti mismo. Intentémoslo. Establece `owner` a `'/api/users/'.$user->getId()`.

[[[ code('2c2f58c421') ]]]

## Cómo se deserializan los campos de relación

Cuando lo hagamos, debería aparecer esta parte de nuestro código. ¡Puestos de combate! Ejecuta:`symfony php bin/phpunit` y ejecuta sólo esta prueba:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

¡Perfecto! Golpea y vuelca un objeto `UserApi`. Esto es genial. En realidad, vuelca todo el `$dto` para que podamos ver las cosas con más detalle.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

Fantástico. Cuando enviamos estos datos JSON, el serializador deserializa todo esto en un objeto `DragonTreasureApi`. Esta cadena va a la propiedad `name`, esta cadena va a la propiedad `description`, y así sucesivamente. Aquí vemos que: cadena... cadena... 1.000... y 5. Súper sencillo.

Pero ocurre algo especial cuando el campo que envías es una relación, lo que significa que la propiedad contiene un objeto que es un `#[ApiResource]`. Concretamente, ¡esta cadena IRI se transforma en un objeto `UserApi`! Pero... ¿cómo y quién lo hace? La respuesta es: un poco de trabajo en equipo entre el sistema serializador y el proveedor de estado.

Hasta ahora, por lo que sabemos, la única vez que se utiliza el proveedor de estado es cuando obtenemos un recurso... como si obtenemos un usuario aquí o aquí, o si `PATCH` o`DELETE` un usuario. En todos esos casos, API Platform aprovecha el proveedor de estado de usuario para encontrar uno o varios usuarios.

Pero hay otro punto en el que se utiliza un proveedor de estado: cuando alguien envía JSON que contiene una cadena IRI en un campo de relación.

Durante el proceso de deserialización, el serializador toma esta cadena IRI, comprueba que corresponde a un objeto `UserApi` y llama a su proveedor de estado para cargarlo. Lo que devuelva ese proveedor de estado se establecerá en última instancia en la propiedad `owner` de `DragonTreasureApi`. Esta magia siempre ha existido... pero me encanta entender la mecánica que hay detrás. ¡Alerta de empollón!

## Mapear el campo de relación

De todos modos, en nuestro mapeador, nuestro trabajo es bastante sencillo. Sabemos que `$dto->owner`es un objeto `UserApi`. Y lo que necesitamos en última instancia es una entidad `User`. Así que, una vez más, utilizaremos el sistema de mapeo para pasar de `UserApi` a `User`. Aquí arriba, inyecta un `MicroMapperInterface $microMapper`.

[[[ code('50bfe463ab') ]]]

Y abajo, digamos `$entity->setOwner()`... pero utiliza `$this->microMapper->map()` para ir de `$dto->owner` a `User::class`. Y recuerda, cada vez que mapeemos una relación, debemos añadir también un `MAX_DEPTH`. Establece `MicroMapperInterface::MAX_DEPTH` en `0`.

[[[ code('138319e07f') ]]]

Utilizar `0` es suficiente porque eso hará que nuestro mapeador consulte el objeto `User`... sólo que no continuará y rellenará los datos de las propiedades individuales de `UserApi` a`User.`. Sólo necesitaríamos hacer eso si permitiéramos que `owner` fuera un objeto incrustado, como crear uno nuevo sobre la marcha.... o si hiciéramos algo loco como añadir `@id` para cargar un usuario... y luego modificar ese usuario de golpe. Cosas locas, probablemente no realistas, de las que ya hemos hablado en tutoriales anteriores.

E incluso si un usuario intentara esto ahora mismo, API Platform no lo permitiría porque sólo se pueden escribir datos incrustados en un campo si hemos configurado los grupos de serialización para ello.

De todos modos, lo único que nos preocupa es asegurarnos de que estamos cargando el objeto entidad `User` correcto. Ejecuta de nuevo la prueba y...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

¡Está bien! ¡Ya podemos escribir el campo `owner`!

Siguiente paso: Vamos a centrarnos en hacer que se pueda escribir en el campo `dragonTreasures` de `User`. Éste es un campo de relación... pero como es una colección, necesitará un truco adicional.
