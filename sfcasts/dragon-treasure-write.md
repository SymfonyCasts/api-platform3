# Making DragonTreasureApi Writable

Let's get our *write* endpoints working for `DragonTreasureApi`! If you look
down here, we have a test called `testPostToCreateTreasure()`. That sounds like a
good one! Over in your terminal, run it:

```terminal
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

And... it goes kaboom! It ran a *few* tests... and they all say the same thing:

> No mapper found for `DragonTreasureApi` -> `DragonTreasure`

Ok, when we POST, it *deserializes* the JSON into a new `DragonTreasureApi` object
and then calls our processor. Our processor takes that API object and tries to use 
MicroMapper to map it to the `DragonTreasure` entity. Since we're *missing*
the mapper from `DragonTreasureApi` to `DragonTreasure`, kablooie!

## Creating the Mapper

We know the drill! In `src/Mapper/`, create a new `DragonTreasureApiToEntityMapper`.
Inside, implement `MapperInterface`, use `#[AsMapper()]` to say that
we are mapping `from: DragonTreasureApi::class`, `to: DragonTreasure::class`...
and add the two methods.

This will be very similar to our `UserApiToEntityMapper`. In `load()`, if we have
an ID, we want to *query* for that object. Add a constructor, with
`private DragonTreasureRepository $repository`. Down here, include the now-familiar
`$dto = $from`, and `assert` that `$dto` is an `instanceof DragonTreasureApi`.
To make life even easier, steal some code from our other mapper. Copy this...
and plop it over here. But Hit "Cancel" because we don't need that `use` statement...
and rename this to just `$entity`. So if the `$dto` has an `id`, it means we're
editing it and we want to find the existing one. *Else*, we're going to create a
`new DragonTreasure()`. And while it *shouldn't* happen, we have an `Exception` 
in case the treasure wasn't found.

One interesting thing about the `DragonTreasure` entity is that it has a
constructor argument: the *name*. And we *don't* have a `setName()` method: the
only way to set it is through the constructor. So, to transfer the `name` from the
`$dto` *onto* the entity, pass it to the constructor.

Two quick notes about this. Yes, this means that you can't *change* the name of
an existing treasure via the API. And that's expected: if we've written our
`DragonTreasure` without a `setName()` method, then we're intending for the name
to be set once and never changed. Second, this is the *one* case where we *do*
populate a *bit* of data inside `load()`. We normally save that work for `populate()`,
but it can't be avoided here, and that's ok.

Head down to `populate()` and start with the same code from `load()`. Also add
`$entity = $to`... and one more `assert()` that `$entity instanceof DragonTreasure`.
Just say `TODO` for a moment.

I want to make sure our mapper is at least being called. Earlier, when we ran the
test, it executed *three* tests that match the name. So let's make the method
a bit more unique. This is called `testPostToCreateTreasure()` and it uses the normal
login mechanism, so add `WithLogin` at the end. When we run the test with
the new name:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

A 500 error! Let's see what's going on. Okay, good! We got further!
It's *now* exploding when it hits the *database*. So it *is* trying to save,
and it's complaining because `owner_id` is null.

## Adding Validation Constraints

Reminder time: the `owner` field is *supposed* to be optional. If we *don't* pass
an owner, it should automatically be set to the authenticated user. We *had* code
for that before, and we'll re-add it in a moment.

But this failure is actually coming from *earlier*: from line 71, right here. This
test starts by checking our validation. It submits *no* JSON, and
makes sure that our validation constraints save the day. We don't *have* any
validation constraints, so instead of *failing* validation, it tries to save. Boo.

Let's re-add the constraints... this time to our API class. For `$name`,
`#[NotBlank]`, `$description`, `#[NotBlank]`, `$value` will be
`#[GreaterThanOrEqual(0)]` and `$coolFactor` will be `#[GreaterThanOrEqual(0)]`
and *also* `#[LessThanOrEqual(10)]`.

Try the test again.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

We're probably going to hit that same error, and... yep - 500 error. But look! Now
it's coming from line 78! That means we *are* getting the validation error status
code here. Then, below, when we POST valid data, it attempts to save it to the
database, but *can't* because, like we saw a second ago, the `owner_id` is *still*
null.

## Automatically Setting the Owner

This is one of the great things about these mapper objects. In
`DragonTreasureApiToEntityMapper`, *normally*, we're going to do things like
`$entity->setValue($dto->value)`: just transferring data from one to the other. But
we can *also* do custom things - like setting weird fields that require calculations
or... setting the owner to the currently-authenticated user.

Check it out: `if ($dto->owner)`, then we're going to set that onto the entity.
Well, we won't do it *yet*, just `dd()` for now. This is the case where we *do*
include the `owner` field in the JSON... and we'll talk more about that soon.

For the `else`, this is when the user does *not* send an `owner` field.
To set it to the currently authenticated user, on top, inject the `Security` service
onto a new property. Then back below, set `owner` to `$this->security->getUser()`.

Beautiful! We *are* still missing the other field setting... so if we try to run
the test... it will *still* hit a 500. *But*, if you check out the error, it's failing
because `description` is null. The `owner` *is* being set.

So let's fill in the other fields: `$entity->setDescription($dto->description)`,
`$entity->setCoolFactor($dto->coolFactor)`, and `$entity->setValue($dto->value)`.

Boring but clear work. Also include a `TODO` down for `published`. We'll talk more
about that shortly.

Ok, run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin
```

And... it *passes*. Woo! Try *all* the tests from `DragonTreasure`:

```terminal
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

And... ooo. We have several failures, related to missing headers, security,
validation, etc. Let's make this green next.
