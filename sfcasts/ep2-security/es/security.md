# Denegar el acceso con la opción "seguridad

Acabamos de hablar mucho sobre la autenticación: es la forma de decirle a la API quiénes somos. Ahora pasamos a la autorización, que consiste en denegar el acceso a determinadas operaciones y otras cosas en función de quién eres.

## Utilizar access_control

Hay múltiples formas de controlar el acceso a algo. La más sencilla es en`config/packages/security.yaml`. Igual que en la seguridad normal de Symfony, aquí abajo tenemos una sección `access_control`:

[[[ code('a2d625bb58') ]]]

Si quieres bloquear un patrón de URL específico por un rol específico, utiliza`access_control`. Podrías usar esto, por ejemplo, para requerir que el usuario tenga un rol para usar cualquier cosa en tu API apuntando a URLs que empiecen por `/api`.

## Hola Opción "seguridad

En una aplicación web tradicional, utilizo `access_control` para varias cosas. Pero la mayoría de las veces pongo mis reglas de autorización dentro de los controladores. Pero... por supuesto, con API Platform, no tenemos controladores. Todo lo que tenemos son clases de recursos API, como `DragonTreasure`. Así que en lugar de poner las reglas de seguridad en los controladores, las adjuntaremos a nuestras operaciones.

Por ejemplo, hagamos que la petición POST para crear un nuevo `DragonTreasure` requiera que el usuario esté autenticado. Para ello, añadiremos una opción muy útil `security`. Establécela como una cadena y dentro de, digamos `is_granted()`, comillas dobles y luego`ROLE_TREASURE_CREATE`:

[[[ code('6bab6481ec') ]]]

Podríamos utilizar simplemente `ROLE_USER` si sólo quisiéramos asegurarnos de que el usuario ha iniciado sesión. Pero tenemos un sistema genial en el que, si utilizas un token de API para la autenticación, ese token tendrá ámbitos específicos. Uno de los posibles ámbitos se llama`SCOPE_TREASURE_CREATE`... que se asigna a `ROLE_TREASURE_CREATE`. Así que lo buscamos. Además, en `security.yaml`, a través de `role_hierarchy`, si inicias sesión a través del formulario de inicio de sesión, obtienes `ROLE_FULL_USER`... y entonces automáticamente también obtienes`ROLE_TREASURE_CREATE`.

En otras palabras, al utilizar `ROLE_TREASURE_CREATE`, se te concederá el acceso porque te has conectado a través del formulario de inicio de sesión o te has autenticado utilizando un token de API que tiene ese alcance.

Vamos a probarlo. Asegúrate de que has cerrado la sesión. Voy a actualizar. Sí, puedes ver en la barra de herramientas de depuración web que no he iniciado sesión... y Swagger no tiene actualmente un token de API.

Vamos a probar la ruta POST. Pruébalo... y... ejecuta con los datos del ejemplo. Y... ¡sí! ¡Un código de estado 401 con el tipo `hydra:error`!

## Más información sobre el atributo "seguridad

La opción `security` contiene en realidad una expresión que utiliza el lenguaje de expresión de Symfony. Y puedes ponerte muy elegante con ella. Aunque, vamos a intentar mantener las cosas simples. Y más adelante, aprenderemos cómo descargar reglas complejas a los votantes.

Añadamos algunas reglas más. `Put` y `Patch` son ediciones. Son especialmente interesantes porque, para utilizarlas, no sólo necesitamos estar conectados, sino que probablemente necesitemos ser el propietario de este `DragonTreasure`. No queremos que otras personas editen nuestras cosas.

Nos preocuparemos de la parte de la propiedad más adelante. Pero por ahora, al menos añadamos `security` con `is_granted()` y luego `ROLE_TREASURE_EDIT`:

[[[ code('af8f4a3858') ]]]

Una vez más, estoy utilizando el rol scope. Cópialo y duplícalo aquí abajo para `Patch`:

[[[ code('f2f97de93e') ]]]

Ah, y antes hemos eliminado la operación `Delete`. Añadámosla de nuevo con`security` configurada para buscar `ROLE_ADMIN`:

[[[ code('d12dd05079') ]]]

Si más adelante decidiéramos añadir un ámbito que permitiera a los tokens de la API eliminar tesoros, podríamos añadirlo y cambiar esto a `ROLE_TRESURE_DELETE`.

¡Asegurémonos de que esto funciona! Utiliza la ruta de recolección GET. Pruébalo. Esta operación no requiere autenticación... así que funciona bien. Y tenemos un tesoro con ID 1. Cierra esto, abre la operación PUT, pulsa "Probar", 1, "Ejecutar" y... ¡bien! ¡Aquí también obtenemos un 401!

## Añadir "seguridad" a toda una clase

Así que añadir la opción `security` a las operaciones individuales es probablemente lo más habitual. Pero también puedes añadirla al propio `ApiResource` para que se aplique a toda la clase. Por ejemplo, en `User`, probablemente queramos que todas las operaciones requieran autenticación... excepto `Post` para crear, porque así es como se registraría un nuevo usuario.

Así que aquí arriba, añade `security` y busca `ROLE_USER`... sólo para comprobar que estamos registrados:

[[[ code('1ec8cd9b2b') ]]]

Y como esta clase tiene un recurso secundario... y esto también nos permite obtener un usuario, asegúrate de añadir aquí también `security`:

[[[ code('ee66b2b5bd') ]]]

Vigila la seguridad si utilizas subrecursos.

Vale, ahora todas las operaciones en `User` requieren que estés conectado. Pero... no queremos eso para la operación `Post`. Para añadir flexibilidad, sube a la primera`ApiResource`, añade la opción `operations` y, muy rápido, enumera todas las operaciones normales, `new Get()`, `new GetCollection()`, `new Post()`, `new Put()`,`new Patch()` y `new Delete()`:

[[[ code('fa32d92a7c') ]]]

Ahora que las tenemos, podemos personalizarlas. Para `Post`, como queremos que no requiera autenticación, digamos que `security: 'is_granted()` pasa un rol especial falso llamado `PUBLIC_ACCESS`:

[[[ code('b31cf9c0e4') ]]]

Esto anulará la regla de seguridad que estamos pasando a nivel de recurso. Ah, y ya que estamos aquí, para `Put`, configura `security` para que busque `ROLE_USER_EDIT` ya que tenemos un rol de ámbito para editar usuarios. Repite eso aquí abajo para `Patch`:

[[[ code('842da233ab') ]]]

¡Me encanta! Actualiza toda la página. Lo que más nos interesa es la ruta `POST` usuarios. No estamos autentificados, así que pulsa "Probar" y dejaré los datos por defecto. "Ejecutar" y... ¡lo hemos clavado! Un estado 201. Eso sí permitía el acceso anónimo.

## Comprobación de las decisiones de seguridad

Ah, y superdivertido: si alguna vez quieres ver las decisiones de seguridad que se tomaron durante una petición, abre el perfilador de esa petición, baja a la sección "Seguridad" y luego a "Decisión de acceso". Para esta petición, el sistema de seguridad sólo tomó una decisión: era para `PUBLIC_ACCESS`, y estaba permitida.

Siguiente: nuestra API se está volviendo compleja... y sólo va a volverse más compleja. Es hora de dejar de probar nuestras rutas manualmente mediante Swagger y empezar a probarlas con pruebas automatizadas.
