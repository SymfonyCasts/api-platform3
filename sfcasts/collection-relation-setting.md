# Writable Collection via the PropertyAccessor

Coming soon..

To see what's going on here, let's head up to the mapper -
`UserApiToEntityMapper.php`. Okay, we're making this `patch()` request that will take
this data and put it onto the `UserApi`. Now we want to see what's happening with
that `UserApi` object when we're mapping it to the entity. And... ah! The reason
`dragonTreasures` isn't changing in the database is because we're not even mapping
that from the DTO to the entity. We left that as a `TODO` earlier. Down here, let's
`dump($dto)` so we can at least see what our DTO looks like after the request. Run
that again, and... whoa. Check this out! There are *still* two `dragonTreasures` in
the DTO and they're *still* the original two. This tells us that this field here is
being completely ignored. It *should* change to "1" and "3". The *reason* for that,
which some of you may have already guessed, is that, inside of `UserApi`, the
`$dragonTreasures` property *isn't* `writable`. It's pretty cool to see `writable:
false` doing its job and preventing that from being writable. If we spin over and try
it now, you'll see the difference. And... perfect! Lookit. We have *two* treasures,
but the IDs are "1" and "3".

Our `UserApi` is now updated correctly, but our test is still failing because we're
not actually *doing* anything with those DTOs. We need to set that back onto our user
`$entity`. In this case, we need to take an array of `DragonTreasureApi[]` objects
and map them to `DragonTreasure[]` objects so we can set that onto the `User` object.
Once again, we need our mapper. Head to the top... and at this point, this should be
pretty familiar. Say `private MicroMapperInterface $microMapper`... and back down
here... say `$dragonTreasureEntities = []`. We're going to keep it super simple this
time and use a good old fashioned `foreach`. We're going to loop over
`$dto->dragonTreasures as $dragonTreasureApi`. We're keeping our variables very clear
here so we can keep our API and entity objects straight. And then we'll say
`$dragonTreasureEntities[]`, and we'll append that array with
`$this->microMapper->map()`, passing our `$dragonTreasureApi`, which we'll map to
`DragonTreasure::class`. And as you may have already guessed, we're also going to
pass `MicroMapperInterface::MAX_DEPTH` set to `0`. Again, `0` is fine here because we
just need to make sure that the dragon treasure mapper just queries for the correct
`DragonTreasure` entity. If we were allowing *embedded* data to be passed, we'd want
to have a `MAX_DEPTH` of `1` so that the individual *properties* of each
`DragonTreasureApi` are mapped onto the `DragonTreasure`. That's not something we're
worried about right now. We just need to make sure that we have the right entity
object from the database. Down here, we're going to `dd($dragonTreasureEntities)`.
Cool. Let's try it out! And... *okay*. It looks good! We have `2`, `DragonTreasure`,
with `id: 1` that was queried from the database, and down here, we have
`DragonTreasure` with `id: 3`.

The *last* thing we need to do is set that onto the user `$entity`. We'll say
`$entity->set`... but... uh oh... we don't have a `setDragonTreasures()` method. And
that's by design! If you look inside of your `User` entity, there's a
`getDragonTreasures()` method, but there's no `setDragonTreasures()` method.
*Instead*, there's an `addDragonTreasure()` method and a `removeDragonTreasure()`
method. I won't dive too deeply into why we can't have a setter, but it has to do
with setting the *owning* side of the Doctrine relationship. The point is, we need to
call the *adders* and *removers*.

If you think about it, it's a little more complicated than that. What we *really*
need to do is look at which `$dragonTreasureEntities` we have here, which
`$dragonTreasureEntities` are already *on* this field (like 1 and 3), and then call
the correct adders and removers. In our specific case, we'll want to call the
`removeDragonTreasure()` method for this middle one and `addDragonTreasure()` for
this third one. So we almost need to create a *diff* between the *new* entities and
the *existing* `dragonTreasureEntities`, and then call the adders and removers
accordingly. That sounds... *annoying*... and kind of complicated. *Fortunately*,
Symfony already *has* something that can do that - a service called the "Property
Accessor".

Head up here... and add `private PropertyAccessorInterface $propertyAccessor`. This
is a cool service! Property Accessor is good at *setting properties*. It can detect
if a property is a setter, adder, or remover method, and it's pretty handy! Here,
let's say `$this->propertyAccessor->setValue()`, and we'll pass it the object that
we're setting data onto, which is our user `$entity`. We'll also pass it the property
path - `dragonTreasures` - and finally, the *value* - `$dragonTreasureEntities`. Down
here, let's `dd($entity)` so we can see what's happening.

Okay, when we run this... and scroll up... here's our `User` object, and look at
`DragonTreasure`! It has *two*: `id: 1` and `id:3`. It correctly updated the
`DragonTreasure` property! How the heck did it do that? By calling the adder and
remover methods. It's actually doing that *diff* of the *new* `dragonTreasures` and
the *existing* `dragonTreasures`, and calling the adder and remover method. I'll show
you! Down here, we'll add `dump('Removing treasure'.$treasure->getId())`. When we run
the test again... there it is - `Removing treasure 2`! It has detected that that one
is missing from the new entities so it calls the remover, and *life is good*. Let's
remove this `dump()`... as well as the other one over here. And if we run that
again... the test *passes*! We can see the final response after we fetch it where we
get `1` and `3` back. What happened to `2`? That was actually *deleted* from the
database *entirely*. Behind the scenes, its owner was set to `null`. *Then*, thanks
to `orphanRemoval`, any time the *owner* of one of these `dragonTreasures` is set to
`null`, it gets *deleted*. It's called an "orphan", and that's something we talked
about in a previous tutorial.

This is awesome! Our dragon treasure is now *writable*. Before we move on, we need to
clean up something in `testTreasuresCanBeRemoved()`. Let's remove the part where we
are *stealing* `$dragonTreasure3`. We'll get rid of that object there, the part where
we set it down here, change the length to `1`, and we'll just test *that one*. So now
this is truly just a test for *removing* a `DragonTreasure`. And... it *still*
passes! We can remove this `->dump()` as well.

Next: Let's look back at `testTreasuresCannotBeStolen()`. As it turns out, they *can*
be stolen. We are *so* close to getting this polished off. We're just missing a
custom validator that we created in a previous tutorial. We'll fix that, and when we
do, that validator is going to be *much* simpler in the new DTO system.
