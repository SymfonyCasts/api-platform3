# Decoración del normalizador y "Normalizer Aware" (consciente del normalizador)

Nuestra misión es clara: configurar nuestro normalizador para que decore el servicio normalizador del núcleo de Symfony, de modo que podamos añadir el grupo `owner:read` cuando sea necesario y, a continuación, llamar al normalizador decorado.

## Configuración para la decoración

¡Y ya conocemos la decoración! Añade `public function __construct()` con`private NormalizerInterface $normalizer`:

[[[ code('8f09b8488a') ]]]

Abajo en `normalize()`, añade un `dump()` luego `return $this->normalizer->normalize()`pasando `$object` `$format` , y `$context`. Para `supportsNormalization()`, haz lo mismo: llama a `supportsNormalization()` en la clase decorada y pasa los args:

[[[ code('5ac7677c6a') ]]]

Para completar la decoración, dirígete a la parte superior de la clase. Quitaré unas cuantas declaraciones antiguas `use`... y luego diré `#[AsDecorator]` pasando `serializer`, que ya he mencionado que es el id de servicio para el normalizador principal de nivel superior:

[[[ code('86157b284a') ]]]

¡Vale! Aún no hemos hecho ningún cambio... así que deberíamos seguir viendo la única prueba que falla. Pruébalo:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

¡Woh! ¡Una explosión! Guau.

> `ValidationExceptionListener::__construct()`: El argumento nº 1 (`$serializer`) debe ser
> de tipo `SerializerInterface`, `AddOwnerGroupsNormalizer` dado.

¿De acuerdo? Cuando añadimos `#[AsDecorator('serializer')]`, significa que nuestro servicio sustituye al servicio conocido como `serializer`. Así, todos los que dependen del servicio `serializer` pasarán ahora a nosotros... y luego el`serializer` original se pasa a nuestro constructor.

Entonces, ¿cuál es el problema? La decoración ya ha funcionado varias veces. El problema es que el servicio `serializer` de Symfony es... un poco grande. Implementa`NormalizerInterface`, ¡pero también `DenormalizerInterface`, `EncoderInterface`,`DecoderInterface` y `SerializerInterface`! Pero nuestro objeto sólo implementa uno de ellos. Y así, cuando nuestra clase se pasa a algo que espera un objeto con una de esas otras 4 interfaces, explota.

Si de verdad quisiéramos decorar el servicio `serializer`, tendríamos que implementar las cinco interfaces... lo cual es feo y demasiado. ¡Y no pasa nada!

## Decorar un normalizador de nivel inferior

En lugar de decorar el nivel superior `normalizer`, vamos a decorar un normalizador concreto: el que se encarga de normalizar los objetos `ApiResource` en`JSON-LD`. Éste es otro punto en el que puedes confiar en la documentación para que te dé el ID de servicio exacto que necesitas. Es `api_platform.jsonld.normalizer.item`:

[[[ code('a3303e22eb') ]]]

Vuelve a hacer la prueba: `testOwnerCanSeeIsPublishedField`

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

¡Sí! ¡Vemos nuestro volcado! Y... ¿un error 400? Déjame abrir la respuesta para que podamos verla. Extraño:

> El serializador inyectado debe ser una instancia de `NormalizerInterface`.

Y procede de lo más profundo del código del serializador de API Platform. Así que... decorar normalizadores no es un proceso muy amigable. Está bien documentado, pero es raro. Cuando decoras este normalizador específico, también tienes que implementar`SerializerAwareInterface`. Y eso va a requerir que tengas un método `setSerializer()`. Oh, déjame importar esa declaración `use`: No sé por qué no ha aparecido automáticamente:

[[[ code('8eb3bf4bb7') ]]]

Ya está.

Dentro, digamos, si `$this->normalizer` es un `instanceof SerializerAwareInterface`, entonces llama a `$this->normalizer->setSerializer($serializer)`:

[[[ code('302a2e580a') ]]]

Ni siquiera quiero entrar en los detalles de esto: lo que ocurre es que el normalizador que estamos decorando implementa otra interfaz... así que también tenemos que implementarla.

Intentémoslo de nuevo.

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Por último, tenemos el volcado y falla la aserción que esperamos... puesto que aún no hemos añadido el grupo. ¡Hagámoslo!

## Añadir el grupo dinámico

Recuerda el objetivo: si poseemos este `DragonTreasure`, queremos añadir el grupo `owner:read`. En el constructor, autocablea el servicio `Security` como una propiedad:

[[[ code('2e16840661') ]]]

Entonces, aquí abajo, si `$object` es un `instanceof DragonTreasure` -porque este método se llamará para todas nuestras clases de recursos API- y `$this->security->getUser()`es igual a `$object->getOwner()`, entonces llama a `$context['groups'][]` para añadir`owner:read`:

[[[ code('0bca035734') ]]]

¡Uf! Intenta esa prueba una vez más:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

¡Lo hemos conseguido! Ahora podemos devolver diferentes campos objeto por objeto.

## Decorar también el desnormalizador

Si quieres añadir también `owner:write` durante la desnormalización, tendrías que implementar una segunda interfaz. No voy a hacerlo entero... pero implementarías `DenormalizerInterface`, añadirías los dos métodos necesarios, llamarías al servicio decorado... y cambiarías el argumento para que fuera un tipo de unión de`NormalizerInterface` y `DenormalizerInterface`.

Por último, el servicio que estás decorando para la desnormalización es diferente: es`api_platform.serializer.normalizer.item`. Sin embargo, si quieres decorar tanto el normalizador como el desnormalizador en la misma clase, tendrías que eliminar`#[AsDecorator]` y mover la configuración de la decoración a `services.yaml`... porque un único servicio no puede decorar dos cosas a la vez. API Platform lo explica en sus documentos.

De acuerdo, voy a deshacer todo eso... y limitarme a añadir `owner:read`. A continuación: ahora que tenemos un normalizador personalizado, podemos hacer fácilmente locuras como añadir un campo totalmente personalizado a nuestra API que no existe en nuestra clase.
