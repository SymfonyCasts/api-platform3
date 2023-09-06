# Decorar el proveedor de estado principal

Para rellenar la propiedad no persistente de nuestra entidad, utilizaremos un proveedor de estado personalizado. Crea uno con:

```terminal
php bin/console make:state-provider
```

Llamémoslo `DragonTreasureStateProvider`.

Gira y abre esto en `src/State/`. Vale, implementa un `ProviderInterface`que requiere un método: `provide()`. Nuestro trabajo consiste en devolver el objeto `DragonTreasure`para la petición actual de la API, que en nuestra prueba es una petición `Patch`.

[[[ code('a94cbd9265') ]]]

Antes de pensar en hacerlo, `dd($operation)` para que podamos ver si se ejecuta. Cuando probamos la prueba... la respuesta es que no se llama. Obtenemos el mismo error que antes.

[[[ code('ffd675f987') ]]]

Así pues, crear un proveedor de estado e implementar `ProviderInterface` no es suficiente para que se utilice nuestra clase. ¡Y esto es genial! Podemos controlar esto recurso por recurso... o incluso operación por operación.

En `DragonTreasure`, muy arriba, dentro del atributo `ApiResource`, añade`provider` y luego el ID del servicio, que es la clase en nuestro caso:`DragonTreasureStateProvider::class`.

[[[ code('2aee6ff31a') ]]]

Así que ahora, siempre que API Platform necesite "cargar" un tesoro dragón, llamará a nuestro proveedor. Y nuestra prueba es un ejemplo perfecto. Cuando hagamos una petición a `PATCH`, lo primero que hará la API Platform será pedir al proveedor de estado que cargue este tesoro. Luego lo actualizará utilizando el JSON.

Observa, cuando ahora ejecutemos la prueba

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

¡Llegamos al vertedero!

## Decorar el proveedor

Pero... No quiero hacer todo el trabajo de consultar la base de datos en busca de los tesoros del dragón... ¡porque ya existe un proveedor de entidades básico que hace todo eso! Así que ¡utilicémoslo!

Añade un constructor... oh y por ahora lo mantendré en `dd()`. Añade un argumento privado `ProviderInterface $itemProvider`.

[[[ code('8ee92e9fd2') ]]]

Como recordatorio: las operaciones `Get` uno, `Patch`, `Put` y `Delete` utilizan todas `ItemProvider`, que sabe consultar un único elemento. Como nuestra prueba utiliza`Patch`, vamos a centrarnos primero en utilizar ese proveedor.

Si ejecutamos la prueba ahora, falla. El error es

> No se puede autoconectar el servicio `DragonTreasureStateProvider`: argumento `itemProvider`
> hace referencia a `ProviderInterface`, pero no existe tal servicio.

A menudo en Symfony, si hacemos una sugerencia de tipo a una interfaz, Symfony nos pasará lo que necesitamos. Pero en el caso de `ProviderInterface`, hay múltiples servicios que implementan esto - incluyendo el núcleo `ItemProvider` y `CollectionProvider`.

Esto significa que tenemos que decirle a Symfony cuál queremos. Hazlo con el práctico atributo`#[Autowire]` con `service` ajustado a `ItemProvider::class` - asegúrate de obtener el de `ORM`.

[[[ code('f12b609e3b') ]]]

Y ¡sí! Es un identificador de servicio válido. También hay un identificador de servicio más difícil de recordar, pero API Platform proporciona un alias de servicio para que podamos utilizarlo. ¡Encantador!

Vale, ¡a probar! ¡Sí! Hemos llegado al vertedero, lo que significa que se ha inyectado el proveedor de elementos. Así que ahora, somos peligrosos. `$treasure` es igual a `$this->itemProvider->provide()`pasando los 3 args.

[[[ code('753f552fb8') ]]]

En este punto, `$treasure` será `null` o un objeto valioso `DragonTreasure`. Si no es una instancia de `DragonTreasure`, devuelve null.

Pero si es un tesoro, ¡ya está! Llama a `setIsOwnedByAuthenticatedUser()`y codifica por ahora verdadero. Luego devuelve `$treasure`.

[[[ code('db24c047cb') ]]]

Vale, ¡a probar!

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

¡Shazam! ¡Estamos en verde! Así que vamos a establecer ese valor de verdad. Esto es bastante fácil: añade un argumento`private Security`... y asegúrate de que el primer arg tiene una coma.

Entonces esto es cierto si `$this->security->getUser()` es igual a `$treasure->getOwner()`.

[[[ code('36d726dc54') ]]]

Y... entonces... la prueba sigue pasando. ¡Campo personalizado conseguido! Y, lo más importante, está documentado dentro de nuestra API.

Sin embargo, acabamos de romper nuestra ruta `GetCollection`. Vamos a arreglarlo a continuación.
