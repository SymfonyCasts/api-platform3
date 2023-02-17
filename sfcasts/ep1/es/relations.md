# Relacionar recursos

En nuestra aplicación, cada `DragonTreasure` debe pertenecer a un único dragón... o `User`en nuestro sistema. Para configurar esto, olvídate por un momento de la API y modelémoslo en la base de datos.

## Añadir la relación ManyToOne

Dirígete a tu terminal y ejecuta:

```terminal
php bin/console make:entity
```

Modifiquemos la entidad `DragonTreasure` para añadir una propiedad `owner`... y entonces ésta será una relación `ManyToOne`. Si no estás seguro de qué relación necesitas, siempre puedes escribir `relation` y obtendrás un pequeño asistente.

Será una relación con `User`... y te preguntará si la nueva propiedad `owner` puede ser nula en la base de datos. Cada `DragonTreasure` debe tener un propietario... así que di "no". A continuación: ¿queremos mapear el otro lado de la relación? Básicamente, ¿queremos tener la posibilidad de decir `$user->getDragonTreasures()` en nuestro código? Voy a decir "sí" a esto. Y puede que respondas "sí" por dos razones. O bien porque poder decir `$user->getDragonTreasures()` sería útil en tu código o, como veremos un poco más adelante, porque quieres poder obtener un`User` en tu API y ver al instante qué tesoros tiene.

De todos modos, la propiedad - `dragonTreasures` dentro de `User` es fine.... y, por último, para `orphanRemoval`, di que no. También hablaremos de eso más adelante.

Y... ¡listo! Pulsa intro para salir.

Así que esto no tiene nada que ver con la API Platform. Nuestra entidad `DragonTreasure` tiene ahora una nueva propiedad `owner` con los métodos `getOwner()` y `setOwner()`. 

[[[ code('9a8f09be4c') ]]]

Y en `User` tenemos una nueva propiedad `dragonTreasures`, que es un `OneToMany` de vuelta a`DragonTreasure`. En la parte inferior, se ha generado `getDragonTreasures()`,`addDragonTreasure()`, y `removeDragonTreasure()`. Cosas muy estándar.

[[[ code('5de6cf009c') ]]]

Vamos a crear una migración para esto:

```terminal
symfony console make:migration
```

Haremos nuestra doble comprobación estándar para asegurarnos de que la migración no está intentando minar bitcoin. Sí, aquí todo son aburridas consultas SQL. 

[[[ code('dedb502552') ]]]

Ejecútala con:

```terminal
symfony console doctrine:migrations:migrate
```

## Reiniciar la base de datos

Y nos explota en la cara. ¡Grosero! Pero... no debería sorprenderte demasiado. Ya tenemos unos 40 registros `DragonTreasure` en nuestra base de datos. Así que cuando la migración intenta añadir la columna `owner_id` a la tabla -que no permite nulos-, nuestra base de datos se queda perpleja: no tiene ni idea de qué valor poner para esos tesoros existentes.

Si nuestra aplicación ya estuviera en producción, tendríamos que trabajar un poco más para solucionar esto, de lo que hablamos en nuestro tutorial de Doctrine. Pero como esto no está en producción, podemos hacer trampas y simplemente apagar y volver a encender la base de datos. Para ello ejecuta:

```terminal
symfony console doctrine:database:drop --force
```

Luego:

```terminal
symfony console doctrine:database:create
```

Y la migración, que debería funcionar ahora que nuestra base de datos está vacía.

```terminal
symfony console doctrine:migrations:migrate
```

## Configurar las Fijaciones

Por último, vuelve a añadir algunos datos con:

```terminal
symfony console doctrine:fixtures:load
```

Y oh, ¡esto falla por la misma razón! Está intentando crear Tesoros Dragón sin propietario. Para solucionarlo, hay dos opciones. En `DragonTreasureFactory`, añade un nuevo campo `owner` a `getDefaults()` configurado como `UserFactory::new()`.

[[[ code('5e89f3ad8c') ]]]

No voy a entrar en los detalles de Foundry -y Foundry tiene una documentación estupenda sobre cómo trabajar con relaciones-, pero esto creará un nuevo `User` cada vez que cree un nuevo `DragonTreasure`... y luego los relacionará. Así que está bien tenerlo por defecto.

Pero en `AppFixtures`, anulemos eso para hacer algo más guay. Desplaza la llamada a`DragonTreasureFactory` después de `UserFactory`... y pasa un segundo argumento, que es una forma de anular los valores por defecto. Pasando una llamada de retorno, cada vez que se cree un`DragonTreasure` -es decir, 40 veces- se llamará a este método y podremos devolver datos únicos que utilizaremos para anular los valores por defecto de ese tesoro. Devuelve`owner` ajustado a `User::factory()->random()`.

[[[ code('e87c62ef4b') ]]]

Eso encontrará un objeto `User` aleatorio y lo establecerá como `owner`. Así tendremos 40`DragonTreasure`s cada uno acaparado aleatoriamente por uno de estos 10 `User`s.

¡Vamos a probarlo! Ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Esta vez... ¡éxito!

## Exponer el "propietario" en la API

Vale, ahora `DragonTreasure` tiene una nueva propiedad de relación `owner`... y `User`tiene una nueva propiedad de relación `dragonTreasures`.

¿Aparecerá... esa nueva propiedad `owner` en la API? Prueba con la ruta GET del tesoro. Y... ¡el nuevo campo no aparece! Eso tiene sentido! La propiedad `owner` no está dentro del grupo de normalización.

Así que si queremos exponer la propiedad `owner` en la API, como cualquier otro campo, tenemos que añadirle grupos. Copia los grupos de `coolFactor`... y pégalos aquí.

[[[ code('554977e73d') ]]]

Esto hace que la propiedad sea legible y escribible. Y sí, más adelante aprenderemos a establecer la propiedad `owner` automáticamente para que el usuario de la API no tenga que enviarlo manualmente. Pero por ahora, hacer que el cliente de la API envíe el campo `owner` funcionará de maravilla.

En cualquier caso, ¿qué aspecto tiene esta nueva propiedad `owner`? Pulsa "Ejecutar" y... ¡guau! ¡La propiedad `owner` se establece en una URL! Bueno, en realidad, el IRI de `User`.

Esto me encanta. Cuando empecé a trabajar con la API Platform, pensaba que las propiedades de relación utilizarían simplemente el id del objeto. Como `owner: 1`. Pero esto es mucho más útil... porque le dice a nuestro cliente API exactamente cómo puede obtener más información sobre este usuario: ¡sólo tiene que seguir la URL!

## Escribir una propiedad de relación

Así que, por defecto, una relación se devuelve como una URL. Pero, ¿qué aspecto tiene establecer un campo de relación? Actualiza la página, abre la ruta POST, inténtalo, y pegaré todos los campos excepto `owner`. ¿Qué utilizamos para `owner`? ¡No lo sé! Probemos a ponerle un id, como `1`.

Momento de la verdad. Pulsa ejecutar. Veamos... ¡un código de estado 400! Y comprueba el error:

> IRI esperado o documento anidado para el atributo `owner`, entero dado.

Así que pasé el `ID` del propietario y... eso no le gusta. ¿Qué debemos poner aquí? Pues el IRI, ¡por supuesto! Averigüemos más sobre eso a continuación.
