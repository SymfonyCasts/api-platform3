# Crear tu primer ApiResource

Estamos a punto de construir una API para la importantísima tarea de permitir que los dragones muestren su tesoro. Ahora mismo, nuestro proyecto no tiene ni una sola entidad de base de datos... pero vamos a necesitar una para almacenar todo ese tesoro.

## Generar nuestra primera entidad

Busca tu terminal y ejecuta primero

```terminal
composer require maker --dev
```

para instalar Maker Bundle. Luego ejecuta:

```terminal
php bin/console make:entity
```

¡Perfecto! Llamemos a nuestra entidad `DragonTreasure`. Entonces nos hace una pregunta que quizá no hayas visto antes: `Mark this class as an API platform resource`? Pregunta porque la API Platform está instalada. Di `no` porque vamos a hacer este paso manualmente dentro de un momento.

Bien, empecemos a añadir propiedades. Empieza con `name` como cadena, con una Longitud por defecto de 255, y haz que no sea anulable. Después, añade `description` con un tipo `text`, y haz que no sea anulable. También necesitamos un `value`, como... cuánto vale el tesoro. Eso será un `integer` no anulable. Y simplemente debemos tener un `coolFactor`: los dragones necesitan especificar lo impresionante que es este tesoro. Eso será un número del 1 al 10, así que que sea un `integer` no anulable. Luego, `createdAt` `datetime_immutable` que no sea anulable... y por último, añade una propiedad `isPublished`, que será de tipo `boolean`, también no anulable. Pulsa "intro" para terminar.

¡Uf! Hasta ahora no hay nada muy especial. Esto ha creado dos clases: `DragonTreasureRepository` (de la que no nos vamos a preocupar), y la propia entidad `DragonTreasure` con `$id`, `$name`, `$description`, `$value`, etc junto con los métodos getter y setter. Maravillosamente aburrido. Sin embargo, hay un pequeño error en esta versión de MakerBundle. Ha generado un método `isIsPublished()`. Cambiémoslo por `getIsPublished()`.

## Configurar la base de datos

Muy bien, ya tenemos nuestra entidad. Ahora necesitamos una migración para su tabla... ¡pero eso puede ser un poco difícil ya que aún no tenemos configurada nuestra base de datos! Voy a utilizar Docker para esto. La receta de DoctrineBundle nos dio un bonito archivo `docker-compose.yml` que arranca Postgres, así que... ¡vamos a usarlo! Ve a tu terminal y ejecuta:

```terminal
docker-compose up -d
```

Si no quieres utilizar Docker, siéntete libre de arrancar tu propio motor de base de datos y luego, en `.env` o `.env.local`, configura DATABASE_URL. Como estoy utilizando Docker además del binario `symfony`, no necesito configurar nada. El servidor web Symfony verá automáticamente la base de datos de Docker y configurará la variable de entorno `DATABASE_URL` por mí.

Bien, para hacer la migración, ejecuta:

```terminal
symfony console make:migration
```

Este `symfony console` es igual que `./bin/console` excepto que inyecta la variable de entorno `DATABASE_URL` para que el comando pueda hablar con la base de datos Docker. ¡Perfecto! Gira y comprueba el nuevo archivo de migración... sólo para asegurarte de que no contiene ninguna sorpresa extraña. ¡Tiene buena pinta! Así que vuelve a girar y ejecuta esto con:

```terminal
symfony console doctrine:migrations:migrate
```

¡Listo!

## Exponiendo nuestro primer recurso API

Ahora tenemos una entidad y una tabla de base de datos. Pero si vas y actualizas la documentación... todavía no hay nada. Lo que tenemos que hacer es decirle a la API Platform que exponga nuestra entidad `DragonTreasure` como un recurso API. Para ello, ve encima de la clase y añade un nuevo atributo llamado `ApiResource`. Pulsa "tab" para añadir la declaración `use`.

¡Listo! En cuanto hagamos eso... y actualicemos... ¡guau! ¡La documentación está viva! Ahora muestra que tenemos seis rutas diferentes: Uno para recuperar todos los recursos `DragonTreasure`, uno para recuperar un `DragonTreasure` individual, uno para crear un `DragonTreasure`, dos que editan un `DragonTreasure` y uno para eliminarlo. Y esto es algo más que documentación. Estas rutas funcionan.

Ve y haz clic en "Probar", y luego en "Ejecutar". En realidad no devuelve nada porque nuestra base de datos está vacía, pero nos da un código de estado 200 con algo de JSON vacío. En breve hablaremos de todas las demás claves extravagantes de la respuesta.

Pero quiero mencionar una cosa. Como acabamos de ver, la forma más sencilla de crear un conjunto de rutas API es añadir este atributo `ApiResource` sobre tu clase de entidad. Pero en realidad puedes añadir este atributo sobre cualquier clase: no sólo entidades. Es algo de lo que hablaremos en un futuro tutorial: puede ser una buena forma de separar el aspecto de tu API del de tu entidad, especialmente en las API más grandes. Pero, de nuevo, eso es para más adelante. Ahora mismo, utilizar `ApiResource` sobre nuestra entidad va a funcionar de maravilla.

Descubramos un poco más esta genial documentación interactiva. ¿De dónde ha salido esto? ¿Cómo es que nuestra aplicación tiene por arte de magia un montón de rutas nuevas? ¿Y de verdad les gustan los tacos a los dragones? ¡Averigüémoslo a continuación!