# State Providers, Processors & a Custom Field 

API Platform 3 rolled out snazzy new concepts called State Providers and State
Processors. We chatted about them in the last tutorial and we're going to dive even
*deeper* in this tutorial.

## Providers & Processors Basics

Nestled within the "Upgrade Guide" of API Platform's docs lives one of my favorite sections
on this very topic. Each API resource class - whether it's an entity
or a normal class - will have a State Provider. Its job is to *load* the data,
like from the database... or wherever. Each API resource class will *also* have
a State *Processor* whose jobs is to *save* the data, like on a POST or PATCH request.
It's also responsible for *deleting*.

The big bonus is that if your API resource is an entity, you *automatically* get a
set of State Providers and State Processors. For example, the `GetCollection` operation
uses a core `CollectionProvider`, which queries the database for you. And
there's a similar `ItemProvider` to fetch *one* item from the database.

Entities also gets a complimentary `PersistProcessor`, which, no surprise,
persists your data to the database.

In Episode 2, we decorated the `PersistProcessor` for the `User` entity. This
let us hash the plain password up here... before calling the core
`PersistProcessor` to handle the saving.

[[[ code('7c2bfb64af') ]]]

## Good & Better Ways to Add a Custom Field

We're talking about this because we can use a *similar* trick with the state
*provider* to add a custom field: a field that you want in your API, but that
doesn't live in the database.

In the last episode, we learned that one way to add a custom field is by extending
the normalizer. We did this in `AddOwnerGroupsNormalizer`. Well, this does a
*few* things, but importantly for us: if the object is a `DragonTreasure` - so
if a `DragonTreasure` is being turned into JSON - *and* the currently authenticated
user is the owner of that treasure, then add a totally custom `isMine` field.

[[[ code('100447ac31') ]]]

We can see this in our tests:
`tests/Functional/DragonTreasureResourceTest.php`
Search for `isMine`. Yep: `testOwnerCanSeeIsPublishedAndIsMineFields`. The important
part is the bottom: when the treasure is serialized, `isMine` should be in the
response.

[[[ code('ecd7a98abf') ]]]

This works great... except for one hiccup: in the documentation... there is *no*
mention of the `isMine` field! It *will* be returned, but it's not documented.

If this matters to you, there are two better ways to handle this: add a
non-persisted field to your entity - that's what we'll do in a moment - or create
a totally custom API resource class. *That* will be our big topic later.

## Adding the Non-Persisted Field

Step 1: remove the code in the normalizer... and just return. Copy the test method
name... to make sure this fails:

[[[ code('fdcf1cec0c') ]]]

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

And... yay failure! Expected `null` to be the same as `true` from line 215...
because no more `isMine` field!

Step 2: add this field as a real property on our class: how about
`private bool $isOwnedByAuthenticatedUser`. Notice this is a non-persisted property:
it only exists to help our API. Doing this isn't super common, but *is*
allowed. Skip down to the bottom to add a getter and setter.

[[[ code('582f4535f5') ]]]

Oh, and since the property doesn't have a default value, if the property hasn't
been initialized, let's yell so we know.

[[[ code('1adaa2ee20') ]]]

Last but not least, we need to expose this property to our API. Do that by putting
it into the group called `treasure:read`... and then use `SerializedName` to call
it `isMine` in the API.

[[[ code('81fc26d0f8') ]]]

If we go run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

We're greeted with a delicious 500 error! Thanks to the `zenstruck/browser`
library, it saved that failed response to a file... which we can pop open in our browser.
And... yup!

> You must call setIsOwnedByAuthenticatedUser()

So it's *trying* to expose the field to our API... but nothing is *setting* that
property. How *will* we set it? With a positive attitude! And... mostly
a custom state provider. That's next.
