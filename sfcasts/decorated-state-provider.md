# Decorating the Core State Provider

To populate the non-persisted property on our entity, we'll leverage a custom
state provider. Create one with:

```terminal
php bin/console make:state-provider
```

Let's dub it `DragonTreasureStateProvider`.

Spin over and open this up in `src/State/`. Ok, it implements a `ProviderInterface`
which requires one method: `provide()`. Our job is to return the `DragonTreasure`
object for the current API request - which is a `Patch` request in our test.

[[[ code('a94cbd9265') ]]]

Before we think about doing that, `dd($operation)` so we can see if this is
executed. When we try the test... the answer is that it is *not* called. We
get the same error as before.

[[[ code('ffd675f987') ]]]

So, creating a state provider and implementing `ProviderInterface` is *not*
enough to make our class be used. And this is great! *We* get to control this
on an resource-by-resource basis... or even on an operation-by-operation basis.

In `DragonTreasure`, way up on top, inside the `ApiResource` attribute, add
`provider` then the service ID, which is the class in our case:
`DragonTreasureStateProvider::class`.

[[[ code('2aee6ff31a') ]]]

So now, whenever API Platform needs to "load" a dragon treasure, it will call
our provider. And our test is a perfect example. When we make a `PATCH` request,
the first thing API Platform will do is ask the state provider to load this
treasure. Then it will update it using the JSON.

Watch, when we run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

We hit the dump!

## Decorating the Provider

But... I *don't* want to do all the work of querying the database for the dragon
treasures... because there's already a core entity provider that does all that!
So let's use it!

Add a constructor... oh and I'll keep that `dd()` for now. Add a
private `ProviderInterface $itemProvider` argument.

[[[ code('8ee92e9fd2') ]]]

As a reminder: the `Get` one, `Patch`, `Put` and `Delete` operations all use
the `ItemProvider`, which knows to query for a *single* item. Since our test uses
`Patch`, we're going to focus on using *that* provider first.

If we run the test now, it fails. The error is:

> Cannot autowire service `DragonTreasureStateProvider`: argument `itemProvider`
> references `ProviderInterface`, but no such service exists.

Often in Symfony, if we type-hint an interface, Symfony will pass us what we need.
But in the case of `ProviderInterface`, there are *multiple* services that implement
this - including the core `ItemProvider` and `CollectionProvider`.

This means that we need to *tell* Symfony which we want. Do that with the handy-dandy
`#[Autowire]` attribute with `service` set to `ItemProvider::class` - make sure to
get the one from `ORM`.

[[[ code('f12b609e3b') ]]]

And yup! That *is* a valid service id. There is also a harder-to-remember service
id, but API Platform provides a service alias so that we can just use this. Lovely!

Ok, go test go! Yes! We hit the dump which means that the item provider *was*
injected. So now, we're dangerous. `$treasure` equals `$this->itemProvider->provide()`
passing the 3 args.

[[[ code('753f552fb8') ]]]

At this point, `$treasure` will be `null` or a valuable `DragonTreasure` object.
If it is *not* a `DragonTreasure` instance, return null.

But if we *do* have a treasure, we're in business! Call `setIsOwnedByAuthenticatedUser()`
and hardcode true for now. Then return `$treasure`.

[[[ code('db24c047cb') ]]]

Ok, go test go!

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

Shazam! We're green! So let's go set that value for real. This is easy enough:  add a
`private Security` argument... and make sure you first arg has a comma.

Then this is true if `$this->security->getUser()` equals `$treasure->getOwner()`.

[[[ code('36d726dc54') ]]]

And... then... the test still passes. Custom field accomplished! *And*, most importantly,
it *is* documented inside our API.

However, we *did* just break our `GetCollection` endpoint. Let's fix that next.
