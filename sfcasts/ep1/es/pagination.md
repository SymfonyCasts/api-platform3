# Paginación y accesorios de fundición

Vamos a empezar a hacer más cosas con nuestra API... ¡así que es hora de darle vida a esto con algunos data fixtures!

Para ello, me gusta utilizar Foundry junto con DoctrineFixturesBundle. Así que ejecuta

```terminal
composer require foundry orm-fixtures --dev
```

para instalar ambos como dependencias de `dev`. Cuando termine, ejecuta

```terminal
php bin/console make:factory
```

## Añadir la fábrica de Foundry

Si no has utilizado Foundry antes, para cada entidad, creas una clase de fábrica que sea realmente buena para crear esa entidad. Yo le daré a cero para generar la de`DragonTreasure`.

El resultado final es un nuevo archivo `src/Factory/DragonTreasureFactory.php`:

[[[ code('eedeb52468') ]]]

Esta clase es realmente buena creando objetos `DragonTreasure`. ¡Incluso tiene un montón de bonitos datos aleatorios listos para ser utilizados!

Para hacerlo aún más elegante, voy a pegar un poco de código que he dragonizado. Ah, y también necesitamos una constante `TREASURE_NAMES`... que también pegaré encima. Puedes coger todo esto del bloque de código de esta página.

[[[ code('2a817a6662') ]]]

Bien, esta clase ya está lista. Segundo paso: para crear realmente algunos accesorios, abre`src/DataFixtures/AppFixtures.php`. Borraré el método `load()`. Todo lo que necesitamos es: `DragonTreasureFactory::createMany(40)` para crear un buen botín de 40 tesoros:

[[[ code('7c677fd71a') ]]]

¡Probemos esto! De vuelta a tu terminal, ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Y... ¡parece que ha funcionado! Volvamos a nuestros documentos de la API, actualicemos... y probemos la ruta de recolección `GET`. Pulsa ejecutar.

## ¡Ya tenemos Paginación!

¡Qué guay! ¡Mira todos esos preciosos tesoros! Recuerda que hemos añadido 40. Pero si te fijas bien... aunque `IDs` no empiece por 1, podemos ver que aquí hay definitivamente menos de 40. La respuesta dice `hydra:totalItems: 40`, pero sólo muestra 25.

Aquí abajo, este `hydra:view` explica un poco por qué: ¡hay paginación integrada! Ahora mismo estamos viendo la página 1... y también podemos ver las URL de la última página y de la página siguiente.

Así que sí, las rutas API que devuelven una colección necesitan paginación... igual que un sitio web. Y con API Platform, simplemente funciona.

Para jugar con esto, vamos a `/api/treasures.jsonld`. Ésta es la página 1... y luego podemos añadir `?page=2` para ver la página 2. Es lo más fácil que haré en todo el día.

## Profundizando en la configuración de la API Platform

Ahora, si lo necesitas, puedes cambiar un montón de opciones de paginación. Veamos si podemos ajustar el número de elementos por página de 25 a 10.

Para empezar a indagar en la configuración, abre tu terminal y ejecuta:

```terminal
php bin/console debug:config api_platform
```

Hay muchas cosas que puedes configurar en API Platform. Y este comando nos muestra la configuración actual. Así, por ejemplo, puedes añadir un `title` y un `description` a tu API. Esto pasa a formar parte de la OpenAPI Spec... y así aparece en tu documentación.

Si buscas `pagination` -no queremos la que está bajo `graphql`... queremos la que está bajo `collection` - podemos ver varias opciones relacionadas con la paginación. Pero, de nuevo, esto nos está mostrando la configuración actual... no nos muestra necesariamente todas las claves posibles.

Para verlo, en lugar de `debug:config`, ejecuta:

```terminal
php bin/console config:dump api_platform
```

`debug:config` te muestra la configuración actual. `config:dump` te muestra un árbol completo de configuraciones posibles. Ahora... vemos `pagination_items_per_page`. ¡Eso parece lo que queremos!

Esto es realmente genial. Todas estas opciones viven bajo algo llamado`defaults`. Y son versiones en forma de serpiente de exactamente las mismas opciones que encontrarás dentro del atributo `ApiResource`. Establecer cualquiera de estas `defaults` en la configuración hace que ese sea el valor por defecto que se pasa a esa opción para cada `ApiResource`de tu sistema. Genial.

Así que, si quisiéramos cambiar los elementos por página globalmente, podríamos hacerlo con esta configuración. O, si queremos cambiarlo sólo para un recurso, podemos hacerlo sobre la clase.

## Personalizar el número máximo de elementos por página

Busca el atributo `ApiResource` y añade `paginationItemsPerPage` ajustado a 10:

[[[ code('f13c0bab76') ]]]

De nuevo, puedes ver que las opciones que ya tenemos... están incluidas en la configuración de `defaults`.

Muévete y vuelve a la página 1. Y... ¡voilà! Una lista mucho más corta. Además, ahora hay 4 páginas de tesoros en lugar de 2.

Ah, y para tu información: también puedes hacer que el usuario de tu API pueda determinar cuántos elementos mostrar por página mediante un parámetro de consulta. Consulta la documentación para saber cómo hacerlo.

Bien, ahora que tenemos un montón de datos, vamos a añadir la posibilidad de que nuestros usuarios de la API Dragón busquen y filtren entre los tesoros. Por ejemplo, tal vez un dragón esté buscando un tesoro de caramelos envueltos individualmente entre todo este botín. Eso a continuación.
