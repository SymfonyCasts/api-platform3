# User Test + Plain Password

We have a pretty nice `DragonTreasureResourceTest`, so let's bootstrap one for
User.

## Bootstrapping the User Test

Create a new PHP class called, how about, `UserResourceTest`. Make it extend our
custom `ApiTestCase`, then we just need to `use ResetDatabase`:

***TIP
To use Foundry factories in a test, also add a `use Factories;` trait to the top of your test class.
Things worked without that in this case, but in the future, you'll likely get an error.
***

[[[ code('b369ad30a9') ]]]

We don't need `HasBrowser` because that's already done in the base class.

Start with `public function testPostToCreateUser()`:

[[[ code('1b64258c5d') ]]]

Make a `->post()` request to `/api/users`, toss in some `json` with `email` and
`password`, and `assertStatus(201)`.

And now that we've created the new user, let's jump right in and test if
we can log in with their credentials! Make another `->post()` request to
`/login`, *also* pass some `json` - copy the `email` and `password` from
above - then `assertSuccessful()`:

[[[ code('ee7b25b647') ]]]

Let's give this a go: `symfony php bin/phpunit` and run the entire
`tests/Functional/UserResourceTest.php` file:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... ok! A 422 status code, but 201 expected. Let's see: this means something went
wrong creating the user. Let's pop open the last response. Ah! My bad: I forgot
to pass the required `username` field: we're failing validation!

Pass `username`... set to anything:

[[[ code('205e2a6f79') ]]]

Try that again:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

*That's* what I wanted:

> Expected successful status code, but got 401.

So the failure is down here. We *were* able to create the user... but when we tried
to log in, it failed. If you were with us for [episode one](https://symfonycasts.com/screencast/api-platform),
you might remember why! We never set up our API to *hash* the password.

Check it out: inside `User`, we *did* make `password` part of our API. The user
sends the plain-text password they want... then we're saving that directly into
the database. That's a *huge* security problem... and it makes it impossible to
log in as this user, because Symfony expects the `password` property to hold a
*hashed* password.

## Setting up the plainPassword Field

So our goal is clear: allow the user to send a *plain* password, but then hash
it before it's stored in the database. To do this, instead of temporarily storing
the plain-text password on the `password` property, let's create a totally *new*
property: `private ?string $plainPassword = null`:

[[[ code('8b66503faa') ]]]

This will *not* be stored in the database: it's just a temporary spot to hold the
plain password before we hash it and set that on the *real* `password` property.

Down at the bottom, I'll go to "Code"->"Generate", or `Command`+`N` on a Mac, and
generate a "Getter and setter" for this. Let's clean this up a bit: accept only
a string, and the PHPDoc is redundant:

[[[ code('23c26e6fc6') ]]]

Next, scroll all the way to the top and find `password`. *Remove* this from our
API entirely:

[[[ code('583934746d') ]]]

Instead, expose `plainPassword`... but use `SerializedName` so it's called
`password`:

[[[ code('fb55688523') ]]]

So we're obviously not done yet... and if you run the tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Things are worse! A 500 error because of a not null violation.
We're sending `password`, that's stored on `plainPassword`... then we're doing
absolutely nothing with it. So the *real* `password` property stays null and
explodes when it hits the database.

So here's the million-dollar question: how can we hash the `plainPassword` property?
Or, in simpler terms, how can we run code in API Platform *after* the data is
deserialized but *before* it's saved to the database? The answer is: *state
processors*. Let's dive into  this powerful concept next.
