# Writable Collection via the PropertyAccessor

To see what's going on here, head to the mapper - `UserApiToEntityMapper`. The
`patch()` request will take this data, populate it onto `UserApi`... then we map
it *back* to the entity in this mapper.

And... the reason the test is failing is pretty obvious: we're not mapping the
`dragonTreasures` property from the DTO to the entity!

Let's `dump($dto)` so we can see what it looks like after deserializing the
data.

Run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

And... whoa. The `dragonTreasures` in the DTO and *still* the original two. This
tells us that this field here is being completely ignored: it's *not* being
deserialized. And I bet you know the reason. Inside `UserApi`, the `$dragonTreasures`
property *isn't* `writable`. But it *is* pretty cool to see `writable: false` doing
its job.

When we run the test again, you'll see the difference.

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Yup! Still two treasures but the IDs are "1" and "3". So `UserApi` looks correct.

## Going from DragonTreasureApi -> DragonTreasure

Now, we need to take this array of `DragonTreasureApi` objects and map them to
`DragonTreasure` entity objects so we can set them onto the `User` entity. Once again,
we need our mapper!

You know the drill: add `private MicroMapperInterface $microMapper`... and back down
here... start with `$dragonTreasureEntities = []`. I'm going to keep this simple
and use a good old fashioned `foreach`. Loop over `$dto->dragonTreasures` as
`$dragonTreasureApi`. Then say `$dragonTreasureEntities[]` equals
`$this->microMapper->map()`, passing `$dragonTreasureApi` and `DragonTreasure::class`.
And as you may have already guessed, we're going to pass
`MicroMapperInterface::MAX_DEPTH` set to `0`.

`0` is fine in this case because we're just need to make sure that the dragon treasure
mapper queries for the correct `DragonTreasure` entity. If it has a relation,
like `owner`, we don't care if *that* object is correctly populated.
Down here, `dd($dragonTreasureEntities)`.

Let's try it out!

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

And... it looks good! We have 2 treasures with `id: 1`... and way down here
`id: 3`.

## Calling the Adder/Remover Methods

So all we need to do now is set that onto the `User` object. Say `$entity->set`...
but... uh oh. We don't have a `setDragonTreasures()` method! And that's on purpose!
Look inside the `User` entity. It has a `getDragonTreasures()` method, but
no `setDragonTreasures()`. *Instead*, it has an `addDragonTreasure()` method
and a `removeDragonTreasure()` method.

I won't dive too deeply into why we can't have a setter, but it relates to the
fact that we need to set the *owning* side of the Doctrine relationship. We talk
about this in our Doctrine relations tutorial.

The point is, if we *were* able to just call `->setDragonTreasures()`, it wouldn't
save correctly. We need to call the adder and remover methods.

And this is tricky! We need to look at `$dragonTreasureEntities`, compare that with
the *current* `dragonTreasures` property, then call the correct adders and removers.
In our case, we need to call `removeDragonTreasure()` for the middle one and
`addDragonTreasure()` for this third one.

Writing this code sounds... *annoying*... and complicated. *Fortunately*, Symfony
already *has* something that can do this! It's a service called the "Property Accessor".

Head up here... and add `private PropertyAccessorInterface $propertyAccessor`.
Property Accessor is good at *setting properties*. It can detect if a property is
public... or if it has a setter method... or even adder, or remover methods.
To use it, say `$this->propertyAccessor->setValue()` passing the object that we're
setting data onto - the `User` `$entity`, the property we're setting -
`dragonTreasures` - and finally, the *value* - `$dragonTreasureEntities`.

Down here, let's `dd($entity)` so we can see how it looks.

Deep breath. Try this:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

Ok, scroll up... to the `User` object. Look at `dragonTreasures`! It has *two*
items with `id: 1` and `id: 3`! It correctly updated the `DragonTreasure` property!
How the heck did it do that? By calling `addDragonTreasure()` for id 3 and
`removeDragonTreasure()` for id 2.

I an prove it. Down here, add `dump('Removing treasure'.$treasure->getId())`.

When we run the test again...

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

There it is! Removing treasure 2! Life is good. Remove this `dump()`... as well as
the other one over here.

Let's see some green. Run the test one last time... hopefully

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

It *passes*! The final response contains treasures `1` and `3`. What happened
to treasure `2`? It was actually *deleted* from the database *entirely*. Behind the
scenes, its owner was set to `null`. *Then*, thanks to `orphanRemoval`, any time
the *owner* of one of these `dragonTreasures` is set to `null`, it gets *deleted*.
That's something we talked about in a previous tutorial.

Before we move on, we need to clean up `testTreasuresCanBeRemoved()`. Remove the
part where we are *stealing* `$dragonTreasure3`. We'll get rid of that object there,
the part where we set it down here, change the length to `1`, and just test
*that one*. So now this truly is a test for *removing* a `DragonTreasure`.

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

And... it *still* passes! Celebrate by removing this `->dump()`.

Next: Let's look back at `testTreasuresCannotBeStolen()`. Right now, treasures *can*
be stolen, which is lame. Let's fix the validator for this... but also make it
a lot simpler, thanks to the DTO system.
