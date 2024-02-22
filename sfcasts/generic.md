# Reusable Entity->Dto Provider & Processor

Our `UserAPI` is now a *fully functional* API resource class! We've got our
`EntityToDtoStateProvider`, which calls the core state provider from Doctrine, and
*that* gives us all the good stuff, like querying, filtering, and pagination. Then,
down here, we leverage the MicroMapper system to convert the `$entity` objects into
`UserApi` objects.

And we do the *same* thing in the processor. We use MicroMapper to go *from*
`UserApi` *to* our `User` entity... then call the core Doctrine state processor to
let *it* do the saving or deleting. I love that!

Our *dream* is to create a `DragonTreasureApi` and repeat *all* of
this magic. And if we can make these processor and provider classes *completely*
generic... that's going to be *super* easy. So let's do it!

## Making the Provider Generic

Start in the provider. If you search for "user", there's only one spot: where we
tell MicroMapper which class to convert our `$entity` into. Can... we fetch this
*dynamically*? Up here, our provider receives the `$operation` and `$context`.
Let's dump *both* of these.

Since this is in our *provider*... we can just go refresh the Collection endpoint
and... *boom*! This is a `GetCollection` operation... and check it out. The operation
object stores the ApiResource *class* that it's attached to!

So over here, it's simple: `$resourceClass = $operation->getClass()`.
Now that we've got that, down here, make it an argument - `string $resourceClass` -
and pass *that* instead. Finally, we need to add `$resourceClass` as the argument
when we call `mapEntityToDto()` there... *and* right there. Remove the `use` statement
we don't need anymore and... just like that... it *still* works!

## Making the Processor Generic

We're on a roll! Head to the *processor* and search for "user". Ah, we have the
*same* problem except, this time, we need the `User` entity class.

Ok! Up on top, `dd($operation)`. And for this, we need to run one of our tests:

```terminal
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... got it! We see the `Post` operation... and the class is, of course,
`UserApi`. But this time we need the `User` class. Remember:
in `UserApi`, we use `stateOptions` to say that `UserApi` is tied to the `User`
entity. And now, we can *read* this info from the operation. If we scroll down a
bit... there it is: the `stateOptions` property with the `Options` object,
and `entityClass` inside.

Cool! Back in the processor, towards the top... remove the `dd()` and start
with `$stateOptions = $operation->getStateOptions()`. Then, to help my editor (and
also in case I misconfigure something), `assert($stateOptions instanceof Options)`
(the one from Doctrine ORM).

You *can* use *different* `Options` classes for `$stateOptions`...
like if you're getting data from ElasticSearch, but *we* know we're using *this*
one from Doctrine. Below, say `$entityClass = $stateOptions->getEntityClass()`.

And... we don't need this `assert()` down here, then pass `$entityClass` to
`mapDtoToEntity()`. Finally, use that with `string $entityClass`... and also pass
it here.

When we search for "user" now... we can get rid of the two `use` statements...
and... we're clean! It's generic! Try the test!

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

That's it! We're ready! We have a reusable provider and processor! *Next*,
let's create a `DragonTreasureApi` class, repeat this magic, and see how quickly
we can get things to fall into place!
