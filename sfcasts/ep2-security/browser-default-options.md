# Customizing Browser Globally

Our test works... but the API is sending us back JSON, not JSON-LD. Why?

When we made the `GET` request earlier, we did *not* include
an `Accept` header to indicate which format we wanted back. But... JSON-LD is
our API's *default* format, so it sent that back.

However, when we make a `->post()` request with the `json` key, that adds a
`Content-Type` header set to `application/json` - which is fine - but it *also*
adds an `Accept` header set to `application/json`. Yup, we're telling the server
that we want plain JSON back, not JSON-LD.

I want to use JSON-LD everywhere. How can we do that? The second argument to
`->post()` can be an array *or* an object called `HttpOptions`. Say
`HttpOptions::json()`... and then pass the array directly. Let me... get my syntax
right.

So far, this is equivalent to what we had before. But now we can *change* some
options by saying `->withHeader()` passing `Accept` and `application/ld+json`.

We *could* have also done this with the *array* of options: it has a key called
`headers`. But the object is kind of nice.

Let's make sure this fixes things. Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

## Globally Sending the Header

And... we're back to JSON-LD! It's got the right fields and the `application/ld+json`
response `Content-Type` header.

So.... that's cool... but doing this *every* time we make a request to our API
in the tests is... mega lame. We need this to happen automatically.

A nice way to do that is to leverage a base test class. Inside of `tests/`, actually
inside of `tests/Functional/`, create a new PHP class called `ApiTestCase`. I'm going
to make this `abstract` and extend `KernelTestCase`. Inside, add the `HasBrowser`
trait.  But we're going to do something sneaky: we're going to import the `browser()`
method but *call* it `baseKernelBrowser`.

Why the heck are we doing that? Re-implement the `browser()` method... then
call `$this->baseKernelBrowser()` passing it `$options` and `$server`.  But *now*
call *another* method: `->setDefaultHttpOptions()`. Pass this
`HttpOptions::create()` then `->withHeader()`, `Accept`, `application/ld+json`.

Done! Back in our real test class, extend `ApiTestCase`: get the one that's
from *our* app. That's it! When we say `$this->browser()`, it now calls *our*
`browser()` method, which changes that default option. Celebrate by removing
`withHeader()`... and you could revert back to the array of options with a
`json` key if you want.

Let's try it.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

And... uh oh. That's a strange error:

> Cannot override final method `_resetBrowserClients()`

This... is because we're importing the trait from the parent class *and* our
class... which makes the trait go bananas. Remove the one inside our test class:
we don't need it anymore. I'll also do a little cleanup on my `use` statements.

And now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasure
```

Got it! We get back JSON-LD with zero extra work. Remove that `dump()`.

Next: let's write another test that uses our API token authentication.
