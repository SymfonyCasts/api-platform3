# Security Voter

Our security is getting crazy, which I don't like. I want my security logic to
be simple and centralized. The way to do that in Symfony is with a *voter*. Let's
go create one.

At the command line, run:

```terminal
php bin/console make:voter
```

Call it `DragonTreasureVoter`. It's pretty common to have one voter per *entity*
that you need security logic for. So, this voter will make all decisions related
to `DragonTreasure`: can the current user edit one, delete one, view one: whatever
we need.

Go open it up: `src/Security/Voter/DragonTreasureVoter.php`.

Before we talk about this class, let me show you how we'll *use* it. In
`DragonTreasure`, very simply, we're *still* going to use the `is_granted()` function.
But for the first argument, pass `EDIT`... which is just a string I'm making up:
you'll see how that's used in the voter. Then pass the object.

We normally pass `is_granted()` a single argument: a role! But you can *also* pass
it any random string like `EDIT`, as long as you have a voter set up to handle that.
If your voter needs some extra info to make its decision, you can pass that as
the second argument `is_granted()`.

On a high level, we're asking the security system whether or not the current user
is allowed to `EDIT` this `DragonTreasure` object. `DragonTreasureVoter` will
make that decision.

Copy this and paste it down for `securityPostDenormlaize`.

## How Voters Works

So here's the deal: anytime that `is_granted()` is called - from *anywhere*, not
just from API Platform - Symfony loops through a list of "voter" classes and tries
to figure out which voter knows how to make that decision. When we check for a
role, there's a an existing voter that knows how to handle those. In the case
of `EDIT`, there is *no* core voter that knows how to handle that. But we're going
to make `DragonTreasureVoter` able to handle it.

To determine who can handle an `isGranted` call, Symfony calls `supports()` on
each voter passing the same two arguments. For our case, `$atribute` will be
`EDIT` and `$subject` will be the `DragonTreasure` object.

MakeBundle generated a voter that handles checking if we can "edit" or "view"
a `DragonTreasure`. We don't need that "view" right now, so I'll delete it.
Then, below, change this to an instance of `DragonTreasure` and I'll retype the end
of that and hit tab to add the `use` statement... just to clean things up.

So if someone calls `isGranted()` and pass the string `EDIT` and a `DragonTreasure`
object, *we* know how to make that decision.

Oh, and it it doesn't matter, but I'm going to change this to `EDIT` instead of
`POST_EDIT`.

If we return `true` from `supports()`, Symfony then calls a `voteOnAttribute()`.
Very simply: we return `true` if the user should have access, `false` otherwise.

To start, just `return false`.

If we have things set up correctly, our voter will be called whenever we make a
PATCH request and access will *always* be denied. Before we try that, remove
the "view" case down here.

Ok, let's make sure our tests fail! Run:

```terminal
php bin/phpunit
```

And... yes! Two tests fail; both because access is denied. Our voter *is* being
called.

Back in the class, `voteOnAttribute()` is passed the attribute - `EDIT` - the
`$subject` - a `DragonTreasure` object and a `$token`, which is a wrapper around
the current `User` object. So we're first checking to make sure that the user is
*actually* authenticated.

After, `assert` that `$subject` is an instance of `DragonTreasure` because this method
should *only* ever be called when `supports()` return true.

I'm mostly writing this to help my editor know that `$subject` is a `DragonTreasure`:
`assert()` is a handy way to do that.

Our `switch` statement only has one `case` right now. And *this* is where our logic
will live. Very simply: if `$subject` - that's the `DragonTreasure` - `->getOwner()`
equals `$user`, then return `true`. Otherwise, it will hit the `break` and return
`false`. This isn't *all* the logic we need, but it's closer!

Try the tests now.

```terminal-silent
php bin/phpunit
```

Down to one failure!

What's next? Well, we don't have a test for it, but if we authenticate with an
API token, to edit a treasure, you need to have `ROLE_TREASURE_EDIT`, which you
can get via the token scope.

So, in the voter, we need to check if the user has that role. Add a `__construct()`
method and autowire `Security` - the one from SecurityBundle - `$security`. Then,
below, before we check the owner, add if not
`$this->security->isGranted('ROLE_TREASURE_EDIT')`, then *definitely* return `false`.

The last test that's failing is testing that an admin can patch to edit *any*
treasure. Yup, we need to allow admin users to do anything. Because we've already
injected the `Security` service, this is easy.

Let's pretend admin users will be able to do *anything*. So above the `switch`,
say if this `$this->security->isGranted('ROLE_ADMIN')`, then return true.

Moment of truth:

```terminal-silent
php bin/phpunit
```

We're back to green! And now our logic is centralized inside the voter. The `security`
expression is now *dead* simple and we got to write our logic in PHP.

Next: let's explore hiding certain fields in the response based on the user.
