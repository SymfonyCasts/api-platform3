# State Processors: Hashing the User Password

When the user creates a user, they send a `password` field, which gets set onto
the `plainPassword` property. Now, we need to hash that password before the `User`
is saved. Like we showed when working with Foundry, hashing a password is pretty easy.
We grab the `UserPasswordHashInterface` service, then call a method on that.

But to do this, we need a "hook" in API platform so that we can run some code after
our data has been deserialized onto the `User` object, but before it's saved.

In our tutorial about API platform 2, we used a Doctrine listener for this,
which would still work. Though, it does have a few negatives, like being super
magical - it's hard to debug if it doesn't work - and it you need to do some weird
stuff to make sure it runs when *editing* a password on a user.

## Hello State Processors

Fortunately, In API platform 3, we have a *new* tool that I want to take advantage
of. It's called a state processor. And actually, our `User` class is *already* using
a state processor!

Find the API Platform 2 to 3 upgrade guide... search for processor. Let's see...
here we go. It has a section called providers and processors. We're going to talk
about providers later.

According to this, if you have an `ApiResource` class that is an *entity* - like
in our App - then, for example, your `Put` operation already uses a state processor
called `PersistProcessor`! The `Post` operation also uses that, and `Delete` has
one called `RemoveProcessor`.

State processors are cool. After the data the user sends is deserialized onto the
object, we... need to do something! Most of the time, that "something" is: save
the object to the database. And that's *precisely* what `PersistProcessor` does!
Yea, our entity changes save to the database entirely thanks to that built-in
state processor!

## Creating the Custom State Processor

So here's the plan: we're going to hook into the state processor system and add
our own *custom* code. Step one, run a new command from API Platform:

```terminal
php bin/console make:state-processor
```

Let's call it `UserHashPasswordProcessor`. Perfect.

Spin over, go into `src/`, open the new `State` directory and check out
`UserHashPasswordProcessor`. It's delightfully simple: API platform will call
this method, pass us data, tell us what operation is happening... and a few other
things. Then... we just do whatever we want.

Activating this processor is simple in theory. We could go to the `Post` operation,
add a `processor` option and set it to our service id: `UserHashPasswordProcessor::class`.

Unfortunately... if we did that, it would *replace* the `PersistProcessor` that's
already on there. And... we don't want that: we want the existing `PersistProcessor`
to run... and then *our* new processor to run. But each operation can only have
*one* processor.

## Setting up Decoration

But no worries: we can do this by *decorating* `PersistProcessor`. Decoration
always follows the same pattern. First, add a constructor that accept an argument
that has the same interface as our class: `private ProcessorInteface` and I'll
call it `$innerProcessor`.

Step 2: after I add a `dump()` to see if this is working, call the decorated
service method: `$this->innerProcessor->process()` passing `$data`, `$operation`,
`$uriVariables` and..., yes `$context`.

Love it: our *class* is now set up for decoration. *Now* we need to tell Symfony
to *use* it. Internally, the `PersistProcessor` from API Platform is a service.
We're going to tell Symfony that whenever anything needs that `PersistProcessor`
service, it should be passed *our* service instead... but also that it should
pass *us* the original `PersistProcessor`.

To do that, add `#[AsDecorator()]` and pass the id of the service. You can usually
find this in the documentation, or you can use the `debug:container` command to
search for it. It's `api_platform.doctrine.orm.state.persist_processor`.

Decoration done! We're not *doing* anything yet, but let's go see if it hits our
dump! Run the test:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... there it is! It's still a 500, but it *is* using our processor!

## Adding the Hashing Logic

*Now* we can get to work. Because of how we did the service decoration, our new
processor will be called whenever *any* entity is being processed... whether it's
a `User`, `DragonTreasure` or something else. So, start by checking if `$data` is
an `instanceof User`... *and* if `$data->getPlainPassword()`... because if we're
editing a user, and no `password` is sent, no need for us to do anything.

By the way, the official documentation for decorating state processors is slightly
different: it's more complex to me, but the end result is a processor that's only
called for one entity, not all of them.

To hash the password, add a second argument to the constructor:
`private UserHashPasswordProcessor` called `$userPasswordHasher`. Below,
`$data->setPassword()` set to `$this->userPasswordHasher->hashPassword()` passing
it the `User`, which is `$data` and the plain password, which is
`$data->getPlainPassword()`.

And this all happens before we call the *inner* processor that actually saves the
object.

Let's try this thing! Run that test:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Victory! After creating a user in our API, you *can* then use it to log in.

Oh, and it's minor, but once you have a `plainPassword` property, inside of `User`,
there's a method called `eraseCredentials()`. Uncomment
`$this->plainPassword = null`.

This just makes sure that if the object is serialized into the session, the
sensitive `plainPassword` is cleared first.

Next: let's fix some issues validation issues via `validationGroups` and discover
something special about the `Patch` operation.
