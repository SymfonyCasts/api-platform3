# State Providers, Processors & a Custom Field 

API Platform 3 introduced this *awesome* concept of State Providers and State
Processors. We talked about them in the last tutorial and we're going to dive even
*deeper* in this tutorial.

## Providers & Processors Basics

On the "Upgrade Guide" of API Platform's docs lives one of my favorite sections
called: Providers and Processors. Each API resource class - whether it's an entity
or a normal class - will have a State Provider, whose job is to *load* the data,
like from the database or wherever. Each API resource class will *also* have State
*Processor* whose jobs is to *save* the data, like on a POST or PATCH request.
It's also responsible for *deleting*.

The key is that if your API resource is an entity, you automatically get a set of
State Providers and State Processors. For example, the `GetCollection` operation
automatically uses a core `CollectionProvider`, which queries the database. And
there's a similar `ItemProvider` to fetch *one* item from the database.

Our entity also gets a free `PersistProcessor`, which, no surprise, persists your
data to the database. Lovely!

In Episode 2, we decorated the `PersistProcessor` for the `User` entity so we
could hash the password. Here we hash the plain password... and then we call the
core `PersistProcessor` so it can handle the saving.

## Good & Better Ways to Add a Custom Field

We're talking about this because we can use a *similar* trick with the state
*provider* to add custom fields: fields that you want in your API, but that don't
live in your entity.

In the last episode, we learned that one way to add a custom field is by extending
the normalizer. We added `AddOwnerGroupsNormalizer`. This does a few things...
but focus on this part: if the object is a `DragonTreasure` - so if a
`DragonTreasure`
is being turned into JSON - *and* the currently authenticated user is the owner of
that `DragonTreasure`, then add a totally custom `isMine` field.

We can see this, actually, in our tests:
`tests/Functional/DragonTreasureResourceTest.php`
Search for `isMine`. Yep: `testOwnerCanSeeIsPublishedAndIsMineFields`. The important
part is the bottom: when the treasure is serialized, `isMine` should be in the
response.

This works great... except that over in the documentation... there is *no*
mention of an `isMine` field. It *will* be returned, but it's not documented.

If this matters to you, there are two better ways to handle this: adding a
non-persisted field to your entity - that's what we'll do in a moment - or create
a totally custom API resource class. *That* will be our big topic later.

## Adding the Non-Persisted Field

Step 1: remove the code on the normalizer... and just return. Copy the method
name... to make sure this fails:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

And... yay failure! Expected null to be the same as true from line 215... because
no more `isMine` field!

Ok step 1: add this field as a real property on our class: how about
`private bool $isOwnedByAuthenticatedUser`. Notice this is a non-persisted property:
it only exists to help our API. Doing this isn't super common, but is totally
allowed. Skip down to the bottom to add a getter and setter.

Oh and since the property doesn't have a default value, if the property hasn't
been initialized, let's yell so we know.

Finally, we need to expose this property to our API. Do that by putting it into
the group called `treasure:read`... and then use `SerializedName` so that it's
called `isMine` in the API.

If we go run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

We're greeted with a delicious 500 error. Because we're using the `zenstruck/browser`
library, it saved the failed response... which we can pop open in our browser.
And... yup!

> You must call setIsOwnedByAuthenticatedUser

So it's *trying* to expose the field to our API... but nothing is *setting* that
property. How *will* we set it? With a custom state provider!
