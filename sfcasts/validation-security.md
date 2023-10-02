# DTO Validation & Security

Let's talk about *validation*. When we `->post()` to our endpoint, the internal
object will now be our `UserApi` object... which means *that's* what will be validated.
Watch. Send *no* fields to our `POST` request... and *run* that test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

It fails with a 500 error, and... i bet you can guess why. This says

> `User::setEmail()`: Argument #1 (`$email`) must be of type string

Coming from our state processor on line 59. Because there are *no* validation
constraints *at all* on `UserApi`, the `email` property remains `null`. Then,
over here on line 59, we try to transfer that null `email` onto our `$entity`. It
doesn't like that. And even if it *did*, it would eventually fail in the database
because the email is allowed to be null there.

We're missing *validation*. Fortunately, that's easy to add... once you know
that validation will happen on our `UserApi` class.

## Configuration the Operations

But before add some constraints, let's specify the `operations`... so we'll only
have the ones we need: `new Get()`, `new GetCollection()`, `new Post()`... we'll
add some config to *that* in a moment... as well as `new Patch()` and `new Delete()`.

Back when our `User` entity was the `#[ApiResource]`, our `Post()` operation had
an extra `validationContext` option with `groups` set to `Default` and
`postValidation`. We did this so that, when the `Post()` operation happened, it
would run *all* of the *normal* validators *plus* any that were in this
`postValidation` group. We'll see where this comes into play in a moment.

## Adding the Constraints

Ok, let's add some constraints: `$id` isn't even writable... we want `$email` to
be `#[NotBlank]`... and be an `#[Email]`. We want `$username` to be `#[NotBlank]`...
then `$password` is an interesting one. `$password` should be *allowed* to be blank
if we're doing a `PATCH` request to edit it... but *required* on a `POST` request.
To accomplish that, add `#[NotBlank]` but with a `groups` option set to
`postValidation`.

This constraint will only be run when we're validating the `postValidation` group...
which means it will only be run for the `Post()` operation.

Okay, that should be it! Run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... a beautiful 422 status code! That's the validation error, and *this* is
what we want!

## UniqueEntity constraint?

By the way, one of the other validation constraints we had before on the `User`
entity was `#[UniqueEntity]`. That prevents someone from creating *two* users
with the same `email` or `username`. I *don't* have that on `UserApi`, but we
*should*. The `#[UniqueEntity]` constraint, unfortunately, only works on *entities*...
so we'd need to create a custom validator to have that on `UserApi`. *We're* not
going to worry about that right, but I wanted to point it out.

Anyway, back over on the test, re-add the fields. Validation, check!

## Adding Security

The next thing we need to *re-add* - code that *used to* live on `User` - is
*security*. Up here on the API level, for the *entire* resource, let's
require `is_granted("ROLE_USER")`.

This means that we need to be logged in to use *any* of the operations for this
resource... *by default*. Then we *overrode* that. In the `Post()`, we definitely
can't be logged in yet because we're *registering* our user. Say,
`security` set to `is_granted("PUBLIC_ACCESS")` which is a special attribute that
will always pass.

Down here for `Patch()`, we had `security('is_granted("ROLE_USER_EDIT")')`.

In our app, we decided that you need to have this special tole to be able to
edit users.

Ok! Let's run *all* of the tests for `User`:

```terminal
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... *oh*. Not bad! Three out of four! The failure comes from
`testTreasuresCannotBeStolen()`. That doesn't sound good!

If we check that out... this is a really interesting test where we `->patch()` to
update a `$user`, and then try to set the `dragonTreasures` property to a treasure
that is owned by a *different* user. You can see that this `$dragonTreasure` is owned
by `$otherUser`... but we're currently updating `$user`.

What we're attempting to do is *steal* this `$dragonTreasure` from `$otherUser` and
make it part of `$user`. We're asserting that this is a 422 status code... because
we *previously*, we had a custom validator that prevented this.

Well, it still exists - it's this `TreasuresAllowedOwnerChangeValidator` - but it's
*not* being applied to `UserApi` and it needs to be *updated* to work with it.
We'll *do* this later - I just wanted to mention it now.

More importantly, right now, the `dragonTreasures` property isn't even *writable*!
In `UserApi`, above `$dragonTreasures`, we have `writable: false`. In a bit, we're
going to change that so that we *can* write `dragonTreasures` again. And when we
do that, we'll bring back that validator and make sure this test passes.

Next: If you look at the processor *or* the provider we created, these classes are
pretty generic. They could *almost* work for `UserApi` *and* a future
`DragonTreasureApi` class. The only part that's *specific* to `User` is the code
that maps *to* and *from* the `User` entity and the `UserApi` class.

If we could handle that mapping... in some system that lives *outside* of our
provider and processor... we *could* reuse these classes. Let's take this idea
to the next level next.
