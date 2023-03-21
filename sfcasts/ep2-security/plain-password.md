# User Test + Plain Password

We have a pretty nice `DragonTreasureResourceTest`, so let's bootstrap one for
the User class.

## Bootstrapping the User Test

Create a new PHP class called, how about, `UserResourceTest`. Make it extend our
custom `ApiTestCase`, then we just need to `use ResetDatabase`. We don't need
`HasBrowser` because that's already done in the base class.

Start with `public function testPostToCreateUser()`. Make a `->post()` request to
`/api/users`, pass in some `json` - `email` and `password` - then `assertStatus(201)`.

And now that we've created this new user, let's immediately see if we can use it
to log in. Make another `->post()` request to `/login`, *also* pass some `json`,
copy the `email` and `password` from above... `assertSuccessful()`.

Ok. Let's give this a go: `symfony php bin/phpunit` and run the entire
`UserResourceTest`:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... ok! A 422 status code, but 201 expected. Let's see: this means something went
wrong creating the user. Let's pop open the last response. Ah! My bad: I forgot
to pass the required `username` field: we're failing validation!

Pass `username`... set to anything. Try that again:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

*That's* what I wanted:

> expected successful status code, but got 401.

So the failure is down here. We *were* able to create the user... but when we trie
to log in, it failed. If you were with us for episode one, you might remember why!
We never setup our API to *hash* the password.

Check it out: inside `User`, we *did* make `password` part of our API. The user
sends the plain-text password they want... then we're saving that directly into
the database. That's a *huge* security problem... and it makes it impossible to
log in as this user because Symfony expects `password` to hold the *hashed* password.

## Setting up the plainPassword Field

So our goal is clear: allow the user to send a *plain* password, but then hash
it before it's stored in the database. To do this, instead of temporarily storing
the plain-text password on the `password` property, let's create a totally *new*
property for this: `private ?string $plainPassword = null`.

This will *not* be stored in the database: it's just a temporary spot to hold the
plain password before we hash it and set that on the *real* `password` property.

Down at the bottom, I'll go to Code -> Generate, or `Command + N` on a Mac, and
generate a getter and setter for this. Let's clean this up a bit: accept only
a string, and the PHPDoc is redundant.

Next, scroll all the way to the top and find `password`. *Remove* this from our
API entirely. And, instead, expose `plainPassword`... but use `SerializedName` so
that it's called `password`.

So we're obviously not done yet... and if you run the tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

Yea... things are worse than before: a 500 error because of a not null violation.
We're sending `password`, that's stored onto `plainPassword`... then we're not doing
anything with that, so the *real* `password` property stays null and explodes
when it hits the database.

So the question is: how can we hash the `plainPassword` property? Or, more
generally, how can we run code *after* the data is deserialized but *before*
it's saved into the database? The answer is: state processors. Let's dive into
this powerful concept next.
