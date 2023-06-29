# Normalizador personalizado

Copia el método de prueba - `testOwnerCanSeeIsPublishedField`. Acabamos de añadir algo de magia para que los usuarios administradores puedan ver la propiedad `isPublished`. Este método prueba nuestra próxima misión: que los propietarios de un `DragonTreasure` también puedan verlo.

Ejecútalo con:

```terminal
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Y... falla: esperaba que `null` fuera igual que `false`, porque el campo no se devuelve en absoluto.

Para solucionarlo, en `DragonTreasure`, añade un tercer grupo especial: `owner:read`:

[[[ code('329b3f6b0d') ]]]

¿Ves adónde queremos llegar con esto? Si somos propietarios de un `DragonTreasure`, añadiremos este grupo y entonces se incluirá el campo. Sin embargo, conseguir esto es complicado.

Como hablamos en el último vídeo, los grupos de normalización empiezan siendo estáticos: viven aquí arriba, en nuestra configuración. El constructor de contexto nos permite hacer que estos grupos sean dinámicos por petición. Así, si somos un usuario administrador, podemos añadir un grupo `admin:read`adicional, que se utilizará al serializar cada objeto para toda esta petición.

Pero en esta situación, necesitamos que el grupo sea dinámico por objeto. Imagina que devolvemos 10 `DragonTreasure`'s: puede que el usuario sólo sea propietario de uno de ellos, por lo que sólo ese `DragonTreasure` debería normalizarse utilizando este grupo extra.

## El trabajo de los normalizadores

Para manejar este nivel de control, necesitamos un normalizador personalizado. Los normalizadores son el núcleo del serializador de Symfony. Son responsables de convertir un dato -como un objeto `ApiResource` o un objeto `DateTime` que vive en una propiedad- en un valor escalar o de matriz. Creando un normalizador personalizado, ¡puedes hacer prácticamente cualquier cosa rara que quieras!

Busca tu terminal y ejecuta:

```terminal
php  bin/console debug:container --tag=serializer.normalizer
```

Esto me encanta: ¡nos muestra todos y cada uno de los normalizadores de nuestra aplicación! Podemos ver cosas que se encargan de normalizar UUIDs.... esto es lo que normaliza cualquiera de nuestros objetos `ApiResource` a `JSON-LD` y aquí hay uno para un `DateTime`. Hay un montón de cosas interesantes.

Nuestro objetivo es crear nuestro propio normalizador, decorar un normalizador central existente y, a continuación, añadir el grupo dinámico antes de que se llame a ese normalizador central.

## Crear la clase normalizador

Así que, ¡manos a la obra! En `src/` -en realidad no importa cómo organicemos las cosas- voy a crear un nuevo directorio llamado `Normalizer`. Permíteme colapsar algunas cosas... para que sea más fácil verlo. Dentro de eso, añade una nueva clase llamada, qué tal, `AddOwnerGroupsNormalizer`. Todos los normalizadores deben implementar`NormalizerInterface`... luego ve a "Código"->"Generar" o `Command`+`N` en un Mac y selecciona "Implementar métodos" para añadir los dos que necesitamos:

[[[ code('48fec8afe0') ]]]

Esto funciona así: en cuanto implementemos `NormalizerInterface`, cada vez que se normalice cualquier dato, se llamará a nuestro método `supportsNormalization()`. Allí, podemos decidir si sabemos o no normalizar esa cosa. Si devolvemos`true`, el serializador llamará entonces a `normalize()`, nos pasará esos datos, y entonces devolveremos la versión normalizada.

Y en realidad, para evitar algunos errores de desaprobación, abre la clase padre. El tipo de retorno es esta cosa loca de array. Cópialo... y añádelo como tipo de retorno. No hace falta que lo hagas -todo funcionaría sin ello-, pero recibirías un aviso de obsoleto en tus pruebas.

Abajo para `supportsNormalization()`, en Symfony 7, habrá un argumento `array $context`... y el método devolverá un `bool`:

[[[ code('bb1f7028c4') ]]]

## ¿Qué Servicio Decoramos?

Antes de rellenar esto o configurar la decoración, tenemos que pensar qué servicio del núcleo vamos a decorar. Ésta es mi idea: si sustituimos el servicio principal del núcleo`normalizer` por esta clase, podríamos añadir el grupo y luego llamar al normalizador decorado... para que todo funcione entonces como siempre, excepto que tiene el grupo extra.

De vuelta al terminal, Ejecuta:

```terminal
bin/console debug:container normalizer
```

Obtenemos un montón de resultados. Eso tiene sentido: hay un `normalizer` principal, pero luego el propio `normalizer` tiene montones de otros normalizadores dentro de él para manejar distintos tipos de datos. Entonces... ¿dónde está el normalizador de nivel superior? En realidad, ni siquiera está en esta lista: se llama `serializer`. Aunque, como veremos a continuación, ni siquiera eso es del todo correcto.
