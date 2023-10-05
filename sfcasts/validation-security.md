# DTO Validation & Security

Let's talk about *validation*! When we `->post()` to our endpoint, the internal
object will be our `UserApi` object... which means *that's* what will be validated.
Watch. Send *no* fields to the `POST` request... and *run* that test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Oh uh: 500 error! And... I bet you can guess why. It says:

> `User::setEmail()`: Argument #1 (`$email`) must be of type string

Coming from our state processor on line 59. Because there are *no* validation
constraints *at all* on `UserApi`, the `email` property remains `null`. Then,
over here on line 59, we try to transfer that null `email` onto our entity. It
doesn't like that, there's a short fist fight, and we see this error. And even
if it *did* accept a null value, it would eventually fail in the database
because the email isn't allowed to be null *there*.

We're missing *validation*. Fortunately, it's easy to add... once you know
that validation will happen on the `UserApi` object, not the entity.

## Configuration the Operations

But before we run wild and add constraints, let's specify the `operations`... so we
only have the ones we need: `new Get()`, `new GetCollection()`, `new Post()`... we'll
add some config to *that* in a moment... as well as `new Patch()` and `new Delete()`.

Back when our `User` *entity* was the `#[ApiResource]`, the `Post()` operation had
an extra `validationContext` option with `groups` set to `Default` and
`postValidation`. Thanks to that, when the `Post()` operation happened, it
would run all the *normal* validators *plus* any that were in this
`postValidation` group. We'll see *why* we need that in a moment.

## Adding the Constraints

Ok, constraint time! `$id` isn't even writable... we want `$email` to
be `#[NotBlank]`... and be an `#[Email]`. We want `$username` to be `#[NotBlank]`...
then `$password` is an interesting one. `$password` should be *allowed* to be blank
if we're doing a `PATCH` request to edit it... but *required* on a `POST` request.
To accomplish that, add `#[NotBlank]` but with a `groups` option set to
`postValidation`.

This constraint will only be run when we're validating the `postValidation` group...
which means it will only be run for the `Post()` operation.

Okay, that should do it! Run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... a beautiful 422 status code!

## UniqueEntity constraint?

By the way, one of the other validation constraints we had before on the `User`
entity was `#[UniqueEntity]`. That prevented someone from creating *two* users
with the same `email` or `username`. I *don't* have that on `UserApi`, but we
*should*. The `#[UniqueEntity]` constraint, unfortunately, only works on *entities*...
so we'd need to create a custom validator to have that on `UserApi`. *We're* not
going to worry about that right, but I wanted to point it out.

Anyway, back over on the test, re-add the fields. Validation, check!

## Adding Security

The next thing we need to *re-add* - code that *used to* live on `User` - is
*security*. Up here on the API level, for the *entire* resource,
require `is_granted("ROLE_USER")`.

This means that we need to be logged in to use *any* of the operations for this
resource... *by default*. Then we *overrode* that. In `Post()`, we definitely
can't be logged in yet because we're *registering* our user. Say,
`security` set to `is_granted("PUBLIC_ACCESS")` which is a special attribute that
will always pass.

Down here for `Patch()`, we had `security('is_granted("ROLE_USER_EDIT")')`.

In our app, we decided that you need to have this special tole to be able to
edit users.

Ok! Let's run *all* the tests for `User`:

```terminal
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... *oh*. Not bad! Three out of four! The failure comes from
`testTreasuresCannotBeStolen()`. That doesn't sound good!

If we check that out... this is a interesting test: we `->patch()` to
update a `$user`, and then try to set the `dragonTreasures` property to a treasure
that is owned by a *different* user. You can see that this `$dragonTreasure` is owned
by `$otherUser`... but we're currently updating `$user`.

What we're attempting to do is *steal* this `$dragonTreasure` from `$otherUser` and
make it part of `$user`. Dragons do *not* appreciate being robbed, so we're
asserting that this is a 422 status code... because *previously*, we had a
custom validator that prevented this.

Well, it still exists - it's this `TreasuresAllowedOwnerChangeValidator` - but
it's not being applied to `UserApi`... and it needs to be *updated* to work with
it. We'll *do* this later.

More importantly right now, the `dragonTreasures` property isn't even *writable*!
In `UserApi`, above `$dragonTreasures`, we have `writable: false`. In a bit, we're
going to change that so that we *can* write `dragonTreasures` again. And when we
do, we'll bring back that validator and make sure this test passes.

Next: If you look at the processor *or* the provider we created, these classes are
pretty generic. They could *almost* work for `UserApi` *and* a future
`DragonTreasureApi` class... and *any* other DTO class we create that's tied to
an entity. The only part that's *specific* to `User` is the code that maps *to*
and *from* the `User` entity and the `UserApi` class.

If we could handle that mapping... in some system that lives *outside* our
provider and processor... we *could* reuse them. Let's make this a reality
next!
