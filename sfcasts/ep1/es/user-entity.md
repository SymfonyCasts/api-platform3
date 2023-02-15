# Crear una entidad de usuario

No hablaremos de seguridad en este tutorial. Pero aun así, necesitamos el concepto de usuario... porque cada tesoro de la base de datos será propiedad de un usuario... o, en realidad, de un dragón. Más adelante, utilizaremos esto para permitir a los usuarios de la API ver qué tesoros pertenecen a qué usuario y un montón de cosas más.

## make:user

Así pues, vamos a crear esa clase `User`. Busca tu terminal y ejecuta:

```terminal
php bin/console make:user
```

Podríamos utilizar `make:entity`, pero `make:user` configurará un poco las cosas de seguridad que necesitaremos en un tutorial futuro. Llamemos a la clase `User`, sí, vamos a almacenarlos en la base de datos, y establezcamos `email` como campo identificador principal.

A continuación nos pregunta si necesitamos hash y comprobar las contraseñas de los usuarios. Si en tu sistema se va a almacenar la versión hash de las contraseñas de los usuarios, di que sí. Si tus usuarios no van a tener contraseñas -o algún sistema externo comprueba las contraseñas- responde que no. Di que sí a esto.

Esto no hizo mucho... ¡en el buen sentido! Nos dio una entidad `User`, la clase repositorio... y una pequeña actualización de `config/packages/security.yaml`. Sí, sólo configura el proveedor de usuarios: nada especial. Y, de nuevo, hablaremos de ello en un futuro tutorial.

## Añadir una propiedad de nombre de usuario

Vale, dentro del directorio `src/Entity/`, tenemos nuestra nueva clase de entidad `User` con las propiedades`id`, `email` y `password`... y los getters y setters a continuación. Nada del otro mundo. Esto implementa dos interfaces que necesitamos para la seguridad... pero no son importantes ahora.

[[[ code('0d971142a0') ]]]

Ah, pero quiero añadir un campo más a esta clase: un `username` que podamos mostrar en la API.

Así que, vuelve a tu terminal y esta vez ejecuta:

```terminal
php bin/console make:entity
```

Actualiza la clase `User`, añade una propiedad `username`, la longitud de `255` es buena, no nula... y listo. Pulsa enter una vez más para salir.

Vuelve a la clase... ¡perfecto! Ahí está el nuevo campo. Ya que estamos aquí, añade`unique: true` para que sea único en la base de datos.

[[[ code('fafceb175b') ]]]

¡Entidad terminada! Hagamos una migración para ella. De vuelta en el terminal ejecuta:

```terminal
symfony console make:migration
```

Y abre el nuevo archivo de migración. Sin sorpresas: crea la tabla`user`:

[[[ code('0ae474f637') ]]]

Ciérralo y ejecútalo con:

```terminal
symfony console doctrine:migrations:migrate
```

## Añadir la fábrica y los accesorios

¡Estupendo! Aunque, creo que nuestra nueva entidad se merece unas jugosas fijaciones de datos. Utilicemos Foundry como hicimos para `DragonTreasure`. Empieza ejecutando

```terminal
php bin/console make:factory
```

para generar la fábrica de `User`.

Como antes, en el directorio `src/Factory/`, tenemos una nueva clase - `UserFactory` - que es realmente buena para crear objetos `User`. Lo principal que tenemos que retocar es `getDefaults()` para que los datos sean aún mejores. Voy a pegar nuevos contenidos para toda la clase, que puedes copiar del bloque de código de esta página.

[[[ code('af90bf3450') ]]]

Esto actualiza `getDefaults()` para que tenga un poco más de chispa y establece `password`en `password`. Lo sé, creativo. También aprovecho un gancho de `afterInstantiation` para hacer hash de esa contraseña.

Por último, para crear realmente algunos accesorios, abre `AppFixtures`. Aquí es bastante sencillo: `UserFactory::createMany()` y vamos a crear 10.

[[[ code('79b774e6f6') ]]]

Veamos si ha funcionado Gira y ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

¡Sin errores!

Comprobación de estado: tenemos una entidad `User` y hemos creado una migración para ella. Diablos, ¡incluso hemos cargado algunos schweet data fixtures! Pero todavía no forma parte de nuestra API. Si actualizas la documentación, todavía sólo aparece `Treasure`.

Hagamos que forme parte de nuestra API a continuación.
