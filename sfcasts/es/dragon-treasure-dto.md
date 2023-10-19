# ¡Rápido! Crear un DTO DragonTreasure

Es hora de convertir nuestro `DragonTreasure` ApiResource en una clase DTO propiamente dicha. Empezaremos borrando un montón de cosas: todo lo relacionado con API Platform en `DragonTreasure`... para empezar de cero. Volveremos a añadir lo que necesitemos poco a poco. Adiós a las cosas del filtro... a los validadores... a todas las cosas del grupo de serialización... y luego podremos hacer algo de limpieza en nuestras propiedades. Teníamos aquí un código bastante complejo... y aunque no lo añadiremos todo de nuevo, añadiremos las cosas más importantes.

Deja que me desplace hacia abajo para asegurarme de que lo tenemos todo. Sí, ¡eso debería ser todo! Ahora tenemos una buena y aburrida clase de entidad. En `src/ApiPlatform/`, vamos a eliminar también `AdminGroupsContextBuilder`. Esta era una forma compleja de hacer que los campos pudieran ser leídos o escritos por nuestro administrador... pero vamos a solucionarlo con la seguridad de`ApiProperty`. Deshazte también del normalizador personalizado... que añadía un campo y un grupo extra. Y por último, elimina las clases personalizadas`DragonTreasureStateProvider` y `DragonTreasureStateProcessor`.

## Las extensiones de consulta se siguen llamando

Pero conservamos una cosa: `DragonTreasureIsPublishedExtension`. Como el nuevo sistema seguirá utilizando el núcleo de Doctrine `CollectionProvider`, estas extensiones de consulta seguirán funcionando y se seguirán llamando. Es una cosa menos de la que tenemos que preocuparnos.

Ve y actualiza la documentación. De acuerdo Sólo `Quest` y `User`. Aunque, puede que notes algunas cosas de `DragonTreasure` aquí abajo... porque `UserApi` tiene una relación con la entidad `DragonTreasure`. Así que, aunque `DragonTreasure` no sea un recurso API, API Platform sigue intentando documentar qué es ese campo en `User`. En realidad, no importa, porque vamos a arreglar eso y a utilizar completamente clases API en todas partes

## Crear la clase DTO

En `src/ApiResource/`, crea la nueva clase: `DragonTreasureApi`. A continuación, en`UserApi`, roba parte del código básico de nuestro `#[ApiResource]`... pégalo aquí y, por ahora, elimina `operations`. También podemos deshacernos de estas declaraciones `use`. ¡Perfecto!

Utilizaremos un `shortName` - `Treasure` - le daremos a este `10` elementos por página, y eliminaremos la línea `security`. Lo más importante es que tenemos `provider` y`processor` (tal y como están aquí), y `stateOptions`, que apuntará a`DragonTreasure::class`.

También coge la propiedad `$id`. Como antes, en realidad no queremos que esto forme parte de nuestra API, así que es `readable: false` y `writable: false`. Aquí abajo, añade`public ?string $name = null`.

¡Buen comienzo! Tenemos una clase pequeñita y... ¡qué demonios, vamos a probarla! Actualiza los documentos. ¡Sí! ¡Nuestras operaciones Tesoro están aquí! Si probamos la ruta de recogida... obtenemos:

> No se ha encontrado ningún mapeador para `DragonTreasure` -&gt `DragonTreasureApi`

## Añadir la clase mapeadora

¡Fantástico! El único trabajo real que tenemos que hacer es implementar esos mapeadores, ¡así que vamos allá!

En el directorio `src/Mapper/`, crea una clase llamada`DragonTreasureEntityToApiMapper`. Ya lo hemos hecho antes: implementa `MapperInterface`y añade el atributo `#[AsMapper()]`. Vamos a `from: DragonTreasure::class``to: DragonTreasureApi::class` .

Y así de fácil, el micro mapeador sabe que debe utilizar esto. Genera los dos métodos de la interfaz: `load()` y `populate()`. Por cordura, añade `$entity = $from`, y `assert()` que `$entity` es un`instanceof DragonTreasure`.

Aquí abajo, crea el objeto DTO con `$dto = new DragonTreasureApi()`. Y recuerda, el trabajo de `load()` es crear el objeto y ponerle un identificador si lo tiene. Así que añade `$dto->id = $entity->getId()`. Por último, `return $dto`.

Para `populate()`, roba unas líneas de arriba que establecen la variable `$entity`... luego di también `$dto = $to`, y añade una más `assert()` que `$dto` es un`instanceof DragonTreasureApi`.

La única propiedad que tenemos en nuestro DTO ahora mismo es `name`, así que todo lo que necesitamos es`$dto->name = $entity->getName()`. Al final, `return $dto`.

Y, ¡gente! Acabamos de crear una clase que mapea desde la entidad al DTO... y nuestro proveedor de estado utiliza internamente el micro mapeador... así que creo que esto debería... ¡simplemente funcionar!

Y... ¡funciona! ¡Vaya! Con sólo la clase Recurso API y este único mapeador, ya tenemos una clase Recurso API personalizada potenciada por la base de datos. ¡Guau!

## Añadir un campo de relación

Ahora las cosas se ponen interesantes. Cada entidad `DragonTreasure` tiene un propietario, que es una relación con la entidad `User`. En nuestra API, vamos a tener la misma relación. Pero en lugar de ser una relación de `DragonTreasureApi` a un objeto de entidad`User`, será a un objeto `UserApi`.

¡Compruébalo! Digamos `public ?UserApi $owner = null`.

Vamos a rellenarlo en el mapeador. Aquí abajo, digamos `$dto->owner =`... pero... espera un segundo. Esto no es tan sencillo como decir `$entity->getOwner()`, porque ése es un objeto de entidad de usuario. ¡Necesitamos un objeto `UserApi`! ¿Se te ocurre algo que sea realmente bueno convirtiendo una entidad `User` en `UserApi`? Así es, ¡MicroMapper!

Aquí arriba, inyecta `private MicroMapperInterface $microMapper`... y, aquí abajo, di `$dto->owner = $this->microMapper->map()` para mapear de`$entity->getOwner()` -el objeto entidad `User` - a `UserApi::class`.

¿No es genial? Una cosa que debes tener en cuenta es que si, en tu sistema,`$entity->getOwner()` puede ser `null`, debes codificar para ello. Por ejemplo, si tienes un propietario, llama al mapeador; si no, simplemente establece `owner` en `null`... o no lo establezcas en absoluto. En nuestro caso, siempre vamos a tener un propietario, así que esto debería ser seguro.

Vamos a probarlo Actualiza y... oooh. Tenemos un campo `owner` y es un IRI. ¿Por qué aparece como un IRI? Porque API Platform reconoce que el objeto `UserApi` es un recurso API. ¿Y cómo muestra los recursos API que son relaciones? Exacto Los muestra como un IRI. Así que eso es exactamente lo que queríamos ver.

## Añadir más campos

Rellenemos el resto de campos que necesitamos: Lo haré superrápido. Uno de los campos que voy a añadir es `$shortDescription`. Antes era un campo personalizado... pero ahora será más sencillo. Otro campo personalizado que teníamos era `$isMine`, que también será simplemente una propiedad normal.

En nuestro mapeador, vamos a configurarlo todo. Pasaré rápidamente por las partes aburridas. Pero `$shortDescription` es un poco interesante. Antes, en`DragonTreasure`, teníamos un método `getShortDescription()` y eso se exponía directamente como campo de la API.

Con la nueva configuración, es una propiedad normal como cualquier otra, y nos encargamos de establecer los datos personalizados en nuestro mapeador: `$shortDescription` es igual a`$entity->getShortDescription()`. Por último, para `$dto->isMine`, lo codificamos temporalmente como `true`.

Vamos a comprobarlo Actualiza y... ¡qué bonito!

En `tests/Functional/`, tenemos `DragonTreasureResourceTest`. Aquí, tenemos`testGetCollectionOfTreasures()`, que comprueba que sólo vemos elementos publicados. Si nuestra extensión de consulta sigue funcionando, esto pasará. Esto también comprueba que vemos las claves correctas.

Veamos si funciona:

```terminal
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Funciona. Alucinante.

## Rellenar el extraño campo isMine

Antes de terminar, vamos a arreglar el código `true` de `isMine`. Esto es fácil, pero demuestra lo agradable que es trabajar con campos personalizados. En nuestro mapeador, éste es un servicio, así que podemos inyectar otros servicios como el de `$security`. Luego, podemos rellenarlo con los datos que queramos. Así que `isMine`es verdadero si `$this->security->getUser()` es igual a `DragonTreasure`, `getOwner()`(que es un objeto de entidad `User` ).

Prueba la prueba una vez más para asegurarte de que funciona, y... funciona. ¡Guau!

Lo siguiente: Quiero profundizar en las relaciones en nuestra API potenciada por DTO. Porque, si no tienes cuidado, ¡podemos llegar a la temida recursividad infinita!
