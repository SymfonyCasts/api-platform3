# Validating how Values Change

We still have a massive problem making sure treasures don't end up stolen!
We just covered the main case: if you make a POST or a PUT
request to a treasure endpoint, thanks to our new validation, we make sure you assign
the owner to yourself, unless you're an admin. Yay!

But in our API, when POSTing or PATCHing to a *user* endpoint, you are allowed to
send a `dragonTreasures` field. This, unfortunately allows treasures to be stolen.
Simply send a `PATCH` request to modify your *own* `User` record... then set the
`dragonTreasures` field to an array containing the IRI strings of some treasures
that you do *not* own. Whoops!

The easiest solution would be to... make the field *not* writable. So, inside of
`User`, for `dragonTreasures`, we would keep this *readable*, but remove the
write group. That would force everyone to use the `/api/treasures` endpoints to
manage their treasures.

## The Trickiness of this Problem

If you *do* want to keep the writable `dragonTreasures` field... you can, but
this problem *is* tricky to solve.

Let's think: if you send a `dragonTreasures` field that contains the IRI of a
treasure you do *not* own, that should trigger a validation error. Ok... so maybe
we add a validation constraint above this property? The problem is that, by the time
that validation runs, the treasures sent over in the JSON have *already* been set
onto this `dragonTreasures` property. And importantly, the `owner` on those
treasures has already been updated to *this* `User`!

Remember: when the serializer sees a `DragonTreasure` that is not already owned
by this user, it will call `addDragonTreasure()`... which then calls `setOwner($this)`.
So, by the time validation runs, it's going to look like we *are* the owner of the
treasure... even though we originally weren't!

## Using Previous Data?

What can we do? Well, API Platform *does* have a concept of "previous data".
API Platform *clones* the data before deserializing the new JSON onto it, which
means it *is* possible to get what the `User` object *originally* looked like.

Unfortunately, that clone is *shallow*, meaning that it clones scalar fields - like
`username` - but any objects - like the `DragonTreasure` objects are *not* cloned.
There's no way via API Platform to see what they originally looked like.

## Testing for the Bug

So, we *are* going to solve this with validation... but with the help of a special
class from Doctrine called the `UnitOfWork`.

Alrighty, let's whip up a test to shine a light on this pesky bug. Inside
`tests/Functional/`,  open `UserResourceTest`. Copy the previous test, paste, and
call it `testTreasuresCannotBeStolen()`. Create a second user with
`UserFactory::createOne()`... and we need a `DragonTreasure` that we're going to
try to steal. Assign its `owner` to `$otherUser`:

[[[ code('ebf845a5f4') ]]]

Let's do this! We log in as `$user`, update ourselves - which is allowed -
then, for the JSON, sure, maybe we still send `username`... but we also send
`dragonTreasures` set to an array with `/api/treasures/` and
`$dragonTreasure->getId()`.

At the bottom, assert that this returns a 422:

[[[ code('c8465cbf77') ]]]

Ok! Copy the method name. We're expecting this to fail:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

And... it does! Status code 200, which means we *are* allowing treasure
to be stolen! Gasp!

## Creating the Validator

Ok, let's cook up a new validator class:

```terminal
php ./bin/console make:validator
```

Call it `TreasuresAllowedOwnerChange`.

Go use this immediately. Above the `dragonTreasures` property, add
`#[TreasuresAllowedOwnerChange]`:

[[[ code('a64b9af672') ]]]

Next, over in `src/Validator/`, open up the validator class. We'll do some basic
cleanup: use the `assert()` function to assert that `$constraint` is an instance
of `TreasuresAllowedOwnerChange`. And also assert that `value` is an instance of
`Collection` from Doctrine:

[[[ code('5aded32ea9') ]]]

We know that this will be used above this property... so it will be some sort
of collection of `DragonTreasures`.

## Enter UnitOfWork

But... this will be the collection of `DragonTreasure` objects *after* they've
been modified. We need to ask Doctrine what each `DragonTreasure` looked like
when it was *originally* queried from the database. To do that, we need to grab an
internal object from Doctrine called the `UnitOfWork`.

On top, add a constructor, autowire `EntityManagerInterface $entityManager`... and
make that's a private property:

[[[ code('efa324fbbe') ]]]

Below, grab the unit of work with
`$unitOfWork = $this->entityManager->getUnitOfWork()`:

[[[ code('a23a860f57') ]]]

This is a powerful object that keeps track of *how* entity objects are changing
and is responsible for knowing which objects need to be inserted, updated or
deleted from the database when the entity manger flushes.

Next, `foreach` over `$value` - which will be a collection - `as $dragonTreasure`.
To help my editor, I'll assert that `$dragonTreasure` is an instance of
`DragonTreasure`. And *now*, get the original data:
`$originalData = $unitOfWork->getOriginalEntityData($dragonTreasure)`.

Pretty sweet right? Let's `dd($dragonTreasure)` and `$originalData` so we can
see what they look like:

[[[ code('64058ab5d3') ]]]

Go test go:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Yes! It hit the dump! And this is cool! The first part is the *updated*
`DragonTreasure` object and its owner has ID 1. It's not super obvious, but `$user`
will be id 1 and `$otherUser` will be id 2. So the owner was originally
ID 2, but yeah: user id 1 has stolen it! Below this, we see the *original* data
as an array. And its owner was ID 2!

This info makes us dangerous. Back inside our validator, say
`$originalOwnerId` = `originalData['owner_id']`. And to be super clear, set
`$newOwnerId` to `$dragonTreasure->getOwner()->getId()`.

If these don't match, we have a problem. Well actually, if we don't have an
`$originalOwnerId`, we're creating a *new* `DragonTreasure` and that's ok.
So if there is no `$originalOwnerId` or the `$originalOwnerId` is
equal to the `$newOwnerId`, we're good!

Else... there's some plundering happening! Move the `$violationBuilder` up,
but remove `setParameter()`:

[[[ code('523944607a') ]]]

That's it!

Oh, but I never customized the error message. In the `Constraint` class, give
the `$message` property a better default message:

[[[ code('90869e58e8') ]]]

All right team, moment of truth! Run that test:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Nailed it! Treasure stealing is officially off the table. Oh, and though I didn't do
it, we could also inject the `Security` service to allow admin users to do
whatever they want.

Up next: when we create a `DragonTreasure`, we *must* send the `owner` field.
Let's finally make that optional. If we don't pass the `owner`, we'll set it to
the currently authenticated user. To do that, we need to hook into API platform's
"saving" process one more time.
