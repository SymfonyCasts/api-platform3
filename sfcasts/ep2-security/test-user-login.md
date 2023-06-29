# Testing Authentication

Let's create a test to post and create a new treasure. Say
`public function testPostToCreateTreasure()` that returns `void`. And start the
same way as before: `$this->browser()->post('/api/treasures')`:

[[[ code('7c51086c7f') ]]]

In this case we need to *send* data. The second argument to any of these
`post()` or `get()` methods is an array of options, which can include `headers`,
`query` parameters or other stuff. One key is `json`, which you can set to an array,
which will be JSON-encoded for you. Start by sending empty JSON... then
`->assertStatus(422)`. To see what the response looks like, add `->dump()`:

[[[ code('c655b922ff') ]]]

Awesome! Copy the test method name. I want to focus *just* on this one test. To
do that, run:

```terminal
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

And... oh! Current response status code is 401, but 422 expected.

## Dumped Failed Responses in Browser

When a test fails with browser, it automatically saves the last response
to a file... which is *awesome*. It's actually in the `var/` directory. In my
terminal, I can hold `Command` and click to open that in my browser. *That* is nice.
You'll see me do this a bunch of times.

Ok, so this returned a 401 status code. Of course: the endpoint requires authentication!
Our app has *two* ways to authenticate: via the login form and session or via
an API token. We're going to test both, starting with the login form.

## Logging in during the Test

To log in as a user... that user first needs to *exist* in the database.
Remember: at the start of each test, our database is empty. It's then *our*
job to populate it with whatever we need.

Create a user with `UserFactory::createOne(['password' => 'pass'])` so that we
know what the password will be. Then, before we make the POST request to create
a treasure, `->post()` to `/login` and send `json` with `email` set to
`$user->getEmail()` - to use whatever random email address Faker chose -
then `password` set to `pass`. To make sure that worked, `->assertStatus(204)`:

[[[ code('1427f28a47') ]]]

That's the status code we're returning after successful authentication.

Let's give this a try! Move over and run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

It passes! We're getting the 422 status code and see the validation messages!

## Shortcut to Logging in: actingAs()

So... logging in is... just that easy! And I *would* recommend having a test that
specifically POSTs to your login endpoint like we just did, to make sure its
working correctly.

However, in all of my *other* tests... when I simply need to be authenticated to
do the *real* work, there's a faster way to log in. Instead of making the POST
request, say `->actingAs($user)`:

[[[ code('6df44ba8eb') ]]]

This is a sneaky way of taking the `User` object and pushing it directly into
Symfony's security system without making any requests. It's easier, and faster.
And now, we don't care what the password is at all, so we can simplify that.

Let's check it:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Still good!

## Testing Successful Treasure Creation

Let's do another `POST` down here. Keep chaining and add `->post()`. Actually...
I'm lazy. Copy the existing `->post()`... and use that. But this time, send
real data: I'll quickly type in some... these can be anything. The last key
we need is `owner`. Right now, we *are* required to send the `owner` when we
create a treasure. Soon, we'll make that optional: if we don't send it,
it will default to whoever is authenticated. But for now, set it to `/api/users/`
then `$user->getId()`. Finish with `assertStatus(201)`:

[[[ code('5cbe0b0eb7') ]]]

Because 201 is what the API returns when an object is created.

Alright, go test, go:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Still passing! We're on a roll! Add a `->dump()` to help us debug then a
sanity check: `->assertJsonMatches()` that `name` is `A shiny thing`:

[[[ code('823be418fd') ]]]

When we try that:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

## Sending the Accept: application/ld+json Header

No surprise: all green. But look at the dumped response: it's *not* JSON-LD!
We're getting back standard JSON. You can see it in the `Content-Type`
header: `'application/json'`, *not* `application/ld+json`, which is what I
was expecting.

Let's find out what's going on next and fix it globally by customizing how Browser
works across our entire test suite.
