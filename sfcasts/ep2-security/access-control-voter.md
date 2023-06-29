# Security Voter

Our security is turning into a madhouse, which I don't like. I want my security
logic to be simple and centralized. The way to do that in Symfony is with a *voter*.
Let's go create one.

At the command line, run:

```terminal
php ./bin/console make:voter
```

Call it `DragonTreasureVoter`. It's pretty common to have one voter per *entity*
that you need security logic for. So this voter will make all decisions related
to `DragonTreasure`: can the current user edit one, delete one, view one: whatever
we eventually need.

Go open it up: `src/Security/Voter/DragonTreasureVoter.php`:

[[[ code('7292be53c6') ]]]

Before we talk about this class, let me show you how we'll *use* it. In
`DragonTreasure`, we're *still* going to use the `is_granted()` function.
But for the first argument, pass `EDIT`... which is just a string I'm making up:
you'll see how that's used in the voter. Then pass `object`:

[[[ code('9a8585b8fc') ]]]

We normally pass `is_granted()` a single argument: a role! But you can *also* pass
it any random string like `EDIT`... as long as you have a voter set up to handle
that.  If your voter needs some extra info to make its decision, you can pass that
as the second argument.

On a high level, we're asking the security system whether or not the current user
is allowed to `EDIT` this `DragonTreasure` object. `DragonTreasureVoter` will
make that decision.

Copy this and paste it down for `securityPostDenormalize`:

[[[ code('c9c6106770') ]]]

## How Voters Works

So here's the deal: anytime that `is_granted()` is called - from *anywhere*, not
just from API Platform - Symfony loops through a list of "voter" classes and tries
to figure out which one knows how to make that decision. When we check for a
role, there's an existing voter that knows how to handle that. In the case
of `EDIT`, there is *no* core voter that knows how to handle that. So we'll
make `DragonTreasureVoter` able to handle it.

To determine who can handle an `isGranted` call, Symfony calls `supports()` on
each voter passing the same two arguments. For our case, `$attribute` will be
`EDIT` and `$subject` will be the `DragonTreasure` object:

[[[ code('66bcad11c0') ]]]

MakeBundle generated a voter that handles checking if we can "edit" or "view"
a `DragonTreasure`. We don't need that "view" right now, so I'll delete it.
Below, change this to an instance of `DragonTreasure` and I'll retype the end
and hit tab to add the `use` statement... just to clean things up:

[[[ code('3061682697') ]]]

So if someone calls `isGranted()` and passes the string `EDIT` and a `DragonTreasure`
object, *we* know how to make that decision.

Oh, and I need to change the constant value to `EDIT` to match the `EDIT` string
we're passing to `is_granted()`.

If we return `true` from `supports()`, Symfony will then call `voteOnAttribute()`.
Very simply: we return `true` if the user should have access, `false` otherwise.

To start, just `return false`:

[[[ code('7b72c7feb7') ]]]

If we've played our cards right, our voter will swoop in like an overactive
superhero every time we make a PATCH request and slam the access door shut.
Before we try test that theory, remove the "view" case down here:

[[[ code('b783f0106f') ]]]

Ok, let's make sure our tests fail! Run:

```terminal
symfony php bin/phpunit
```

And... yes! Two tests fail: both because access is denied. Our voter *is* being
called.

## Adding the Voter Logic

Back in the class, `voteOnAttribute()` is passed the attribute - `EDIT` - the
`$subject` - a `DragonTreasure` object and a `$token`, which is a wrapper around
the current `User` object. So we're first checking to make sure that the user is
*actually* authenticated.

After that, `assert()` that `$subject` is an instance of `DragonTreasure` because
this method  should *only* ever be called when `supports()` return `true`:

[[[ code('a7f395b935') ]]]

I'm mostly writing this to help my editor know that `$subject` is a `DragonTreasure`:
`assert()` is a handy way to do that.

The `switch` statement only has one `case` right now. And *this* is where our logic
will live. Very simply: if `$subject` - that's the `DragonTreasure` - `->getOwner()`
equals `$user`, then return `true`. Otherwise, it will hit the `break` and return
`false`:

[[[ code('b8196c0110') ]]]

This isn't *all* the logic we need, but it's a good start!

Try the tests now:

```terminal-silent
symfony php bin/phpunit
```

Down to one failure!

## Checking for Roles in the Voter

What's next? Well, we don't have a test for it, but if we authenticate with an
API token, in order to edit a treasure, you need to `ROLE_TREASURE_EDIT`, which
you can get via the token scope.

So, in the voter, we need to check if the user has that role. Add a `__construct()`
method and autowire `Security` - the one from SecurityBundle - `$security`:

[[[ code('e475fdfaf0') ]]]

Then, below, before we check the owner, if not
`$this->security->isGranted('ROLE_TREASURE_EDIT')`, then *definitely* return
`false`:

[[[ code('971ba34699') ]]]

The last test that's failing is testing that an admin can patch to edit *any*
treasure. Because we've already injected the `Security` service, this is easy.

Let's pretend admin users will be able to do *anything*. So above the `switch`,
if `$this->security->isGranted('ROLE_ADMIN')`, then return `true`:

[[[ code('e3e67e67cf') ]]]

Moment of truth:

```terminal-silent
symfony php bin/phpunit
```

Voilà! Our logic has found a cozy home inside the voter, the `security`
expression is now so simple it's almost scary, and we got to write our logic in
PHP.

Next: let's explore hiding certain fields in the response based on the user.
