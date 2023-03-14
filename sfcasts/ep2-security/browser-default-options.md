# Customizing Browser Globally

Our test works... but the API is sending us back JSON, not JSON-LD. Why?

When we made the `GET` request earlier, we did *not* include
an `Accept` header. But... the API was smart enough to send back JSON-LD because
that's the *default* format.

However, when we make a `POST` request with the `json` key, that adds a
`Content-Type` header set to `application/json` - which is fine - but it *also*
adds an `Accept` header set to `application/json`. Yea, it's telling the server
that we want plain JSON back, not JSON-LD.

I want to use JSON-LD everywhere. How? The second argument to `->post()` can
be an array *or* an object called `HttpOptions`. Say `HttpOptions::json()`... and
then pass the array directly. Let me... get my syntax right.

So far, this is equivalent to what we had before. But now we can *change* some
options by saying `->withHeader()` passing `Accept` and `application/ld+json`.

We *could* have also done this with the *array* of options: it has a key called
`headers`. But this object is kind of nice.

Let's make sure this fixes things. Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

## Globally Sending the Header

And... we're back to JSON-LD! It's got the right fields and the `application/ld+json`
response `Content-Type` header. So that's cool... but doing this *every* time we
make a request to our API in the tests is... mega lame. I want this to happen
automatically.

A nice way to do this is to leverage a base test class. Inside of `tests/`, actually
inside of `test/Functional`, create a new PHP class called `ApiTestCase`. I'm going
to make this `abstract` extend `KernelTestCase`. Inside, add the `HasBrowser` trait.
But we're going to do something a little tricky: we're going to import the `browser()`
method but *call* it `baseKernelBrowser`.

Why the heck are we doing that? Because now I we can re-implement the `browser()`
method... and then call `$this->baseKernelBrowser()` passing it `$options` and
`$server`.

But *now* call another method: `->setDefaultHttpOptions()`. Inside, pass
`HttpOptions::create()` then `->withHeader()`, `Accept`, `application/ld+json`.

Done! Back in our real test class, extend `ApiTestCase`: get the one that's
from our app. That's it! When we say `$this->browser()`, it's now calling *our*
`browser()` method, which changes that default option. Celebrate by removing
the `withHeader()` call... and you could revert this back to an array of options
with a `json` key if you want.

Let's try it.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

And... uh oh. That's a strange error:

> Cannot override method final method `_resetBrowserClients()`

This... is just because we're now importing that trait from the parent class *and*
our class. Remove the one inside our test class: we don't need that anymore. I'll
also do a little cleanup on my `use` statements.

And now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Got it! We get back JSON-LD with no extra work. Remove that `dump()`.

Next: let's write another test that uses our API token authentication.
