# Decorating the CollectionProvider

Let's boldly do something that scares most us developers: run
the *entire* test suite:

```terminal
symfony php bin/phpunit
```

These were obediently passing when I started the tutorial... but they've decided
to rebel! Let's pop open the failed response. Hmm:

> More than one result was found for query, although one row or none was expected.

If you view the page source, this is coming from Doctrine... and eventually the
core `ItemProvider` that we're calling. Back on the docs, the `GetCollection`
operation - which is the operation used in this test - has a *different*
provider: `CollectionProvider`.

Unfortunately, when I set `provider` inside the `#[ApiResource]` attribute...
that set the provider for *every* operation. It *is* possible to set the `provider`
for a specific operation... like this. But... I *like* having a single
provider for my entire API resource - it's simpler.

To make that happen, we just need to realize that this provider will be called
both when fetching a *single* item and when fetching a *collection* of
items. For this test, our provider is being called to fetch a collection...
then we're calling the item provider... and weird stuff happens.

`dd()` the `$operation` again... then copy the failing test name... and run
just that one:

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Excellent! A `GetCollection` object. We can use *that* to figure out which
provider we need!

Let's get the core `CollectionProvider` injected. Copy the first argument,
duplicate it, and set it to use the `CollectionProvider` service from ORM.
Name it `$collectionProvider`.

Below, check to see if `$operation` is an instance of `CollectionOperationInterface`.
Ok, really, only *one* operation - `GetCollection` - uses the collection provider...
but in case a custom operation were added, anything that needs a collection
will implement this interface. In this situation, return `$this->collectionProvider->provide()`
and pass in the args. And... don't forget the method name!

Alrighty! Spin over or run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

And... it still explodes. Something about expected null to be the same as 5. 
Check the response. Ah! It's our error again! For the item operation,
we *are* setting that property. Now, we need to do the same thing here: loop
over each treasure and set that.

## The Paginator Object

But first, what *does* the collection provider return - an array of treasures?
Copy the entire call, `dd()` it... and run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

Let's see... it's a `Paginator` *object*! That's important: *that* is what powers
the pagination for our collection endpoints. Ok, it's not actually *that* important
right now - we can loop over this object to get each `DragonTreasure` - but we'll
come back to this later when we create a custom resource.

Delete the `dd()` and, instead of the return, say `$paginator` equals. I'll
help my editor by saying that this is an `iterable` of `DragonTreasure`. Now,
`foreach` `$paginator` as `$treasure`... and then I'll steal the code from
below... and paste.

Now that we've modified each item, `return $paginator`.

Let's try it again!

```terminal-silent
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

It fails *again*... but at the very end: `DragonTreasureResourceTest` line 37. Let's
go check that out. So all the way up here, we create some treasures, make a `->get()`
request to the collection endpoint, verify some things, and then, below, we
grab the first item and check to make sure it has the right fields. Apparently
the `isMine` property *is* there... but wasn't expected?

That's my bad. On a previous adventure, when we added the `isMine` property, we
*only* added it when it was `true`. If a `DragonTreasure` did *not* belong to
me, the field wasn't there at all... and it probably should have been. So let's
update the test. And now... it's green!

Re-run *everything*

```terminal-silent
symfony php bin/phpunit
```

## POST: No State Provider

Uhhh. down to one failure: `testPostToCreateTreasure` - with a 500 error. Pop
that open in our browser. Bah! It's our:

> You must call `setIsOwnedByAuthenticatedUser()`.

But how is that possible? No matter what, we *are* setting that value inside our
state provider! However... the `POST` operation is unique: it's the only operation
that does *not* use a provider. Ok, `Delete` doesn't *show* a provider, but it uses
the `ItemProvider` to load the one item it's about to delete.

For `Post`, the JSON is deserialized *directly* into a `TreasureEntity`.. then
saved. The state provider is never needed or used.... which means when it serializes
to JSON, that property is *still* not set.

The fix is in the state processor for `DragonTreasure`: right before or after saving,
we need to run this same logic. Copy this. We *do* have a state processor already
for `DragonTreasure`. It's meant to set the owner if it's not set... but let's
hijack it for this. Right after the save, paste that. Oh, but the way we created
this in the previous episode means that it's called for *every* ApiResource. So
we need the same if statement from up here: if `$data` is an `instanceof`
`DragonTreasure`, then set that property. I'll... update a couple of variables.

So, the object saves, we set the property... and *then* it's serialized to JSON.
Try those tests again:

```terminal-silent
symfony php bin/phpunit
```

All green! Woo! So we already know that we can run code before or after an item saves
by having a custom state processor. But what if we need to run code only when
something *specific* changes? Like when a `DragonTreasure` changes from unpublished
to published. We'll dive into that next, starting with making our state processor
a bit simpler.
