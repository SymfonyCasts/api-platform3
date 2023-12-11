# State Processors: Hashing the User Password

When an API client creates a user, they send a `password` field, which gets set onto
the `plainPassword` property. Now, we need to hash that password *before* the `User`
is saved to the database. Like we showed when working with Foundry, hashing a
password is simple: grab the `UserPasswordHasherInterface` service then call a
method on it:

[[[ code('1aec54425f') ]]]

But to pull this off, we need a "hook" in API platform: we need some way to run
code after our data is deserialized onto the `User` object, but before it's saved.

In our tutorial about API platform 2, we used a Doctrine listener for this,
which would still work. Though, it does some negatives, like being super
magical - it's hard to debug if it doesn't work - and you need to do some weird
stuff to make sure it runs when *editing* a user's password.

## Hello State Processors

Fortunately, In API platform 3, we have a shiny new tool that we can leverage.
It's called a state processor. And actually, our `User` class is *already* using
a state processor!

Find the [API Platform 2 to 3 upgrade guide](https://api-platform.com/docs/core/upgrade-guide/)...
and search for processor. Let's see... here we go. It has a section called
*providers and processors*. We'll talk about providers later.

According to this, if you have an `ApiResource` class that is an *entity* - like
in our app - then, for example, your `Put` operation already uses a state processor
called `PersistProcessor`! The `Post` operation also uses that, and `Delete` has
one called `RemoveProcessor`.

State processors are cool. After the sent data is deserialized onto the
object, we... need to do something! Most of the time, that "something" is: save
the object to the database. And that's *precisely* what `PersistProcessor` does!
Yea, our entity changes are saved to the database *entirely* thanks to that
built-in state processor!

## Creating the Custom State Processor

So here's the plan: we're going to hook into the state processor system and add
our own. Step one, run a new command from API Platform:

```terminal
php ./bin/console make:state-processor
```

Let's call it `UserHashPasswordProcessor`. Perfect.

Spin over, go into `src/`, open the new `State/` directory and check out
`UserHashPasswordStateProcessor`:

[[[ code('36f009d47d') ]]]

It's delightfully simple: API platform will call this method, pass us data,
tell us which operation is happening... and a few other things. Then...
we just do whatever we want. Send emails, save things to the database,
or RickRoll someone watching a screencast!

Activating this processor is simple in theory. We could go to the `Post` operation,
add a `processor` option and set it to our service id: `UserHashPasswordStateProcessor::class`.

Unfortunately... if we did that, it would *replace* the `PersistProcessor` that
it's using now. And... we don't want that: we want our new processor to run...
and *then* also the existing `PersistProcessor`. But... each operation can only
have *one* processor.

## Setting up Decoration

No worries! We can do this by *decorating* `PersistProcessor`. Decoration
always follows the same pattern. First, add a constructor that accept an argument
with the same interface as our class: `private ProcessorInterface` and I'll
call it `$innerProcessor`:

[[[ code('e1a66c86ba') ]]]

After I add a `dump()` to see if this is working, we'll do step 2: call the decorated
service method: `$this->innerProcessor->process()` passing `$data`, `$operation`,
`$uriVariables` and... yes, `$context`:

***TIP
In API Platform 3.2 and higher, you should `return $this->innerProcessor->process()`. This
is also a safe thing to do in 3.0 & 3.1.
***

[[[ code('3345ce9992') ]]]

Love it: our *class* is set up for decoration. *Now* we need to tell Symfony
to *use* it. Internally, `PersistProcessor` from API Platform is a service.
We're going to tell Symfony that whenever *anything* needs that `PersistProcessor`
service, it should be passed *our* service instead... but also that Symfony should
pass *us* the *original* `PersistProcessor`.

To do that, add `#[AsDecorator()]` and pass the id of the service. You can usually
find this in the documentation, or you can use the `debug:container` command to
search for it. The docs say it's `api_platform.doctrine.orm.state.persist_processor`:

[[[ code('c7e6df9fc6') ]]]

Decoration done! We're not *doing* anything yet, but let's see if it hits our
dump! Run the test:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... there it is! It's still a 500, but it *is* using our processor!

## Adding the Hashing Logic

*Now* we can get to work. Because of how we did the service decoration, our new
processor will be called whenever *any* entity is processed... whether it's
a `User`, `DragonTreasure` or something else. So, start by checking if `$data` is
an `instanceof User`... *and* if `$data->getPlainPassword()`... because if we're
editing a user, and no `password` is sent, no need for us to do anything:

[[[ code('fced58ad22') ]]]

By the way, the official documentation for decorating state processors is slightly
different. It looks more complex to me, but the end result is a processor that's
only called for one entity, not all of them.

To hash the password, add a second argument to the constructor:
`private UserPasswordHasherInterface` called `$userPasswordHasher`:

[[[ code('b912b016f3') ]]]

Below, say `$data->setPassword()` set to `$this->userPasswordHasher->hashPassword()`
passing it the `User`, which is `$data` and the plain password: `$data->getPlainPassword()`:

[[[ code('a2724928e9') ]]]

And this all happens before we call the *inner* processor that actually saves the
object.

Let's try this thing! Run that test:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Victory! After creating a user in our API, we *can* then log in as that user.

## User.eraseCredentials()

Oh, and it's minor, but once you have a `plainPassword` property, inside of `User`,
there's a method called `eraseCredentials()`. Uncomment `$this->plainPassword = null`:

[[[ code('7f537e47fd') ]]]

This makes sure that if the object is serialized into the session, the
sensitive `plainPassword` is cleared first.

Next: let's fix some validation issues via `validationGroups` and discover
something special about the `Patch` operation.
