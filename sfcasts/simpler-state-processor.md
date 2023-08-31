# Simper State Processor

Publishing a `DragonTreasure` is easy: make a `Patch` request to the treasure endpoint
with `isPublished` set to true and... celebration! But... what if, when a
`DragonTreasure` is published, we need to run some custom code - maybe trigger
some notifications on the site.

One option is to create a custom operation - like maybe `POST /api/treasures/5/publish`.
You *can* do that - and it might be fun to look at in a future tutorial. But I
don't think it's necessary: we can keep that simple `Patch` request and *still* run
the code that we want. How? By using a state processor and *detecting* that the
change.

Let's start by creating a test that publishes a treasure. At the bottom, copy this
last test, paste, then rename it `testPublishTreasure`. It starts the same:
we have a user that owns a treasure... and start with `isPublished` `false` Down here,
log in as that user, make a `->patch()` request to `/api/treasures/` that id and
send `isPublished: true`. This should be a 200 status code... and then
`->assertJsonMatches()` that `isPublished` is `true`.

Simple enough! Copy that test name, spin over and run it:

```terminal
symfony php bin/phpunit --filter=testPublishTreasure
```

Uh oh - it fails: expected `false` to be the same as `true`. That's from the last
line: the JSON still has `isPublished` false. Maybe... the field isn't writable?
Check the groups above that property. Ah: in a previous tutorial, we made this field
writable by *admin* users, but not normal users. Add `treasure:write`.

That means anybody that has access to the `Patch` operation can write to this field...
which in reality, thanks to the `security` on that operation... and a custom voter
we created, is just admin users and the owner.

Try the test now:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Got it! To run some code when the treasure is published, we need a state processor.
And we actually have one for `DdragonTreasure`, which we originally created to
set the owner to the currently authenticated user. So... should we jam that same
code here or create a second processor?

It's up to you but I like to have *one* processor per resource class. It just makes
my life simpler. Let's rename this class to just `DragonTreasureStateProcessor`.

## Changing How Our State Processor Decorates

In the last tutorial, we learned that there are *two* ways to add a custom state
provider or processor into the system. The first is the way we did it a few minutes
ago with the state provider: create a normal boring service... then set the `provider`
option on your class to point to it.

The *other* way - which we did in the last tutorial for this class - is to
*decorate* the decorate the core processor. Here, we decorated the `PersistProcessor`
from Doctrine... which means that whenever *any* API resource is saved, when it
tries to use the core `PersistProcessor`, *our* service is called instead. This
was easy to set up because all we needed was `#[AsDecorator]` and... bam! Our
service started being called for *all* our resources. That is *also* why we need
this extra code to see *which* object is being saved.

Both ways are fine. Bit for consistency with the provider, let's refactor this to
use the *other* method. This is 3 steps. First, remove `#[AsDecorator]`. Suddenly,
instead of overriding a core service, this is a normal, boring service that *nobody*
is using at the moment. Second, because we're no longer decorating a core service,
Symfony won't know what to pass for `$innerProcessor`. Break this onto multiple
lines... then use the `#[Autowire]` trick to point to the core `PersistProcessor`.
And I'll clean up the old `use` statement.

Step 3 is to tell API Platform when to use this process. In `DragonTreasure`, we
want this to be used for both our `Post` and `Patch` operations. Set
`processor` to `DragonTreasureStateProcessor::class`... and repeat down here for
`Patch`.

Done! API Platform will call our processor... and it contains the core `PersistProcessor`
so we can make it do the *real* work. Re-run the test to give us infinite confidence:

```terminal-silent
symfony php bin/phpunit
```

That feels *great*.

And the nice thing about doing the processor with *This* method is that you don't
need this conditional code anymore: this will *always* be a `DragonTreasure`. To
help my editor and prove it, `assert()` that `$data` is an `instanceof`
`DragonTreasure`. My editor is already yellin

> Hey this code down here isn't needed anymore dude!

So, remove that too. Now that we've refactored our state processor, let's get
back to the task at hand: running custom code when a treasure becomes published.
