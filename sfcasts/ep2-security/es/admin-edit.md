# Permitir que los usuarios administradores editen cualquier tesoro

Hemos configurado las cosas para que sólo el propietario de un tesoro pueda editarlo. Ahora ha llegado un nuevo requisito desde las alturas: los usuarios administradores deben poder editar cualquier tesoro. Eso significa que un usuario que tenga `ROLE_ADMIN`.

¡A la prueba-móvil! Añade un `public function testAdminCanPatchToEditTreasure()`. A continuación, crea un usuario administrador con `UserFactory::createOne()` pasando los roles a`ROLE_ADMIN`:

[[[ code('71a1c38905') ]]]

## Métodos de Estado de Foundry

Eso funcionará bien. Pero si necesitamos crear muchos usuarios admin en nuestras pruebas, podemos añadir un acceso directo a Foundry. Abre `UserFactory`. Vamos a crear algo llamado método "estado". En cualquier lugar de su interior, añade una función pública llamada, qué tal`withRoles()` que tenga un argumento `array $roles` y devuelva `self`, lo que hará que esto sea más cómodo cuando lo utilicemos. Entonces`return $this->addState(['roles' => $roles])`:

[[[ code('93990c5d77') ]]]

Lo que pasemos a `addState()` se convierte en parte de los datos que se utilizarán para hacer este usuario.

Para utilizar el método de estado, el código cambia a `UserFactory::new()`. En lugar de crear un objeto `User`, esto instanciará un nuevo `UserFactory`... y entonces podremos llamar a`withRoles()` y pasarle `ROLE_ADMIN`:

Así, estamos "elaborando" el aspecto que queremos que tenga el usuario. Cuando hayamos terminado, llamamos a`create()`:

[[[ code('bfe18fc81f') ]]]

`createOne()` es un método abreviado estático. Pero como tenemos una instancia de la fábrica, utiliza `create()`.

Pero podemos ir aún más lejos. De vuelta en `UserFactory`, añade otro método de estado llamado`asAdmin()` que devuelva `self`. Dentro devuelve `$this->withRoles(['ROLE_ADMIN'])`:

[[[ code('12e3a6ef85') ]]]

Gracias a eso, podemos simplificar a `UserFactory::new()->asAdmin()->create()`:

[[[ code('b8439f9a57') ]]]

¡Bien!

## Escribir la prueba

Ahora vamos a poner en marcha esta prueba. Crea un nuevo `$treasure` establecido en`DragonTreasureFactory::createOne()`:

[[[ code('a69c178479') ]]]

Como no estamos pasando un `owner`, esto creará un nuevo `User` en segundo plano y lo utilizará como `owner`. Esto significa que nuestro usuario administrador no será el propietario.

Ahora, `$this->browser()->actingAs($adminUser)` luego `->patch()` a`/api/treasures/`, `$treasure->getId()`, enviando `json` para actualizar `value` al mismo `12345`. `->assertStatus(200)` y `assertJsonMatches()`, `value`, `12345`:

[[[ code('e00e589c5d') ]]]

¡Genial! Copia el nombre del método. Vamos a probarlo:

```terminal
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

Y... ¡vale! Aún no lo hemos implementado, así que falla.

## Permitir a los administradores editar cualquier cosa

Entonces, ¿cómo permitimos que los administradores editen cualquier tesoro? Bueno, al principio es relativamente fácil porque tenemos el control total a través de la expresión `security`. Así que podemos añadir algo como `if is_granted("ROLE_ADMIN") OR` y luego poner paréntesis alrededor del otro caso de uso:

[[[ code('d8d9f1047d') ]]]

¡Asegurémonos de que funciona!

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

¡Un error 500! Veamos qué está pasando. Haz clic para abrir esto.

> Token "nombre" inesperado alrededor de la posición 26.

Así que... eso ha sido un accidente. Cambia `OR` por `or`. Y... mueve también esta nueva lógica a `securityPostDenormalize`:

[[[ code('18c68d88ed') ]]]

Luego vuelve a intentar la prueba:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

¡Lo tengo! Pero mi metedura de pata saca a relucir un gran punto: la expresión `security` se está volviendo demasiado compleja. Es tan legible como un script PERL de una sola línea... y no queremos cometer errores cuando se trata de seguridad.

Así que, a continuación, centralicemos esta lógica con un votante.
