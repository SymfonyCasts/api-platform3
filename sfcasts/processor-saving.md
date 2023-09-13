# Leveraging the Core Processor

Look at us go! In our state processor, we have *successfully* transformed the `UserApi`
into a `User` entity. So let's save it! We *could* inject the entity manager, persist
and flush... and call it a day. But I'd rather offload that work to the core
`PersistProcessor`. Search for that file and open it.

It does the simple persisting and flushing... but it *also* has some pretty complex
logic for `PUT` operations. We're not really using those, but the point is:
better to reuse this class than try to roll our own logic.

## Calling the Core PersistProcessor

*How* we do that should be familiar by this point. Add a
`private ProcessorInterface $persistProcessor`... and so Symfony knows *precisely*
which service we want, include the `#[Autowire()]` attribute, with `service` set
to `PersistProcessor` (in this case, there's only one to choose from) `::class`.

Very nice! Below, save with `$this->persistProcessor->process()` passing
`$entity`, `$operation`, `$uriVariables`, and `$context`... which are all the same
arguments we have up here.

Oh, and like before, when we generated this class, it generated `process()` with
a `void` return type. That's not exactly correct. You don't *have to* return anything
from state processors, but you *can*. And whatever you *do* return - in this
case, we'll return `$data` - will ultimately become the "thing" that is serialized
and returned back to the user. If you don't return anything, it will use
`$data`.

## Setting the id onto the DTO

Ok, I think this should work (Famous last words...).

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... it bombs. We're still getting a 400 error, and it's *still*
`Unable to generate an IRI for the item`.

So... what's going on? We map the `UserApi` to a new `User` object and *save* the new
`User`... which causes Doctrine to assign the new `id` to that entity object. *But*
we never take that new id and put it *back* onto our `UserApi`.

To fix this, after saving, add `$data->id = $entity->getId()`.

And if we try it now...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

it *still* fails... but we got further this time! The response looks good. It returned
a 201 status code with the new user info. It's *failing* on the part of the test
where it tries to *use* the password to log in. That's because our password
is currently set to... `TODO`. We'll fix that in a minute.

## Handling the Delete Operation

But *first*, when we set the `processor` on the top level `#[ApiResource]`, this
became the processor for *all* operations: `POST`, `PUT`, `PATCH`, *and*
`DELETE`. `POST`, `PUT`, and `PATCH` are all pretty much the same: save the object
to the database. But `DELETE` is different: we're not saving, we're *removing*.

To handle that, check `if ($operation instanceof DeleteOperationInterface)`.
Like with saving, deleting isn't hard... but it's still better to offload
this work to the core Doctrine remove processor. So, up here, copy the argument...
and inject *another* processor: `RemoveProcessor`... and rename this to
`$removeProcessor`.

Back down here, say `$this->removeProcessor->process()` and pass `$entity`,
`$operation`, `$uriVariables`, and `$context` just like the other processor.

A key thing to note is that we're going to `return null`. In the case of a `DELETE`
operation, we don't return *anything* in the response... which we accomplish by
returning `null` from here. I don't have a test set up for this, but
we'll take a leap of faith and assume it works. Ship it!

## Hashing the Password

Just one more problem to tackle: hashing the plain password. We've done this before,
so no biggie. Before we do too much here, open `UserApi`... and add a
`public ?string $password = null`... with a comment. This will *always* hold
null or the "plaintext" password if the user sends one. We're *never* going to
need to handle the *hashed* password in our API, so we don't need any space for
that... which is nice!

Back in the processor, `if ($dto->password)`, *then* we know we need to hash that and
set it on the user. If a *new* user is being created, this will always be set...
but when updating a user, we'll make this field optional. If it's not set,
do nothing so the user's current password stays.

To do the hashing, on top, add one more argument:
`private UserPasswordHasherInterface $userPasswordHasher`. Then back below,
`$entity->setPassword()` set to `$this->userPasswordHasher->hashPassword()`, passing
`$entity` (the `User` object) and the plain password: `$dto->password`.

*Phew*. Let's try the test again. And... it *fails*... with

> The annotation "@The" in property `UserApi::$password` was never imported.

So... that's me tripping on my keyboard and adding an extra `@`. Remove that...
then try again:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

It *passes*! Which means it fully-logged in using that password! *Though*, uh oh,
look at the dumped JSON response: this is after we `POST` to create the user. In
the JSON response, it includes the plaintext `password` property that the user just
set. *Whoops*!

## The Flow of a Write Request

Let's break this down. Our state provider is used for all `GET` operations
as well as the `PATCH` operation. And notice, we are *not* setting the `password`
*ever*. We don't want to return that field in the JSON, so we're, correctly, *not*
mapping it from our entity to our DTO. That's *good*!

But the `POST` operation is the *one* situation where the provider is never called.
This data is deserialized directly into a new `UserApi` object and that's passed
to our processor. *This* means that our DTO *does* have the plain password set on
it... And, ultimately, *that* DTO object is what is serialized and sent back to
the user.

This is a long way of saying that, in `UserApi`, this password is meant to be a
*write-only* field. The user should *never* be able to *read* this.
Next: let's talk about how we can do customizations like this inside of
`UserApi`, while avoiding the *complexity* of serialization groups.
