# Testing Token Authentication

What about a test like this... but where we log in with an API key? Let's do that!
Create a new method: public function `testPostToCreateTreasureWithApiKey()`:

[[[ code('cc0da55af8') ]]]

This will start pretty much the same as before. I'll copy the top of the previous
test, remove the `actingAs()`... and add a `dump()` near the bottom:

[[[ code('6c31b71407') ]]]

So, like before, we're sending invalid data and expect a 422 status code.

Copy that method name, then spin over and run *just* this test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

And... no surprise: we get a 401 status code because we're *not* authenticated.

Let's send an `Authorization` header, but an invalid one to start. Pass a
`headers` key set to an array with `Authorization` and then word `Bearer` and
then... `foo`.

This should still fail:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

And... it does! But with a different error message: `invalid_token`. Nice!

## Using a Real Token

To pass a *real* token, we need to put a real token into the database. Do that
with `$token = ApiTokenFactory::createOne()`:

[[[ code('c86b43cd3d') ]]]

Do we need to control any fields on this? We actually *do*. Open up `DragonTreasure`.
If we scroll up, the `Post` operation requires `ROLE_TREASURE_CREATE`:

[[[ code('7e7ffa2753') ]]]

When we authenticate via the login form, thanks to `role_hierarchy`, we always
have that. But when using an API key, to get that role, the token needs the
corresponding scope.

To make sure we have it, back in the test, set the `scopes` property to
`ApiToken::SCOPE_TREASURE_CREATE`:

[[[ code('5ca50ca12d') ]]]

Now pass this to the header: `$token->getToken()`. Oh... and let me fix
`scopes`: that should be an array:

[[[ code('32ef7b9af9') ]]]

I think we're ready! Run that test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithApiKey
```

And... got it! We see the beautiful 422 validation errors!

## Testing a Token with a Bad Scope

Let's test to make sure we *don't* have access if our token is *missing* this
scope. Copy the entire test method... then paste below. Call it
`testPostToCreateTreasureDeniedWithoutScope()`.

This time, set `scopes` to something else, like `SCOPE_TREASURE_EDIT`. Below, we
now expect a 403 status code:

[[[ code('43280da30d') ]]]

This time, let's run *all* the tests:

```terminal
symfony php bin/phpunit
```

And... all green! A 422 then a 403. Go remove the dumps from both those spots.

By the way, if you use API tokens a lot in your tests, passing the `Authorization`
header can get annoying. Browser has a way where we can create a *custom*
Browser object with custom methods. For example, you could add an `authWithToken()`
method, pass an array of scopes, and then it would create that token and set
it into the header

```php
$this->browser()
    ->authWithToken([ApiToken::SCOPE_TREASURE_CREATE])
    // ...
;
```

This totally does *not* work right now, but check out Browser's docs to learn how.

Next: in API Platform 3.1, the behavior of the `PUT` operation is changing. Let's
talk about how, and what we need to do in our code to prepare for it.
