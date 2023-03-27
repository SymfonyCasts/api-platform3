# Custom Normalizer

Copy the test method - `testOwnerCanSeeIsPublishedField`. We just added some magic
so that *admin* users can see the `isPublished` property. This method tests for
our next mission: that *owners* or a `DragonTreasure` can *also* see this.

Run it with:

```
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

And... it fails; expected `null` to be the same as `false`, because the field isn't
showing up at all.

To fix this, over in `DragonTreasure`, I'm going to add a third special group
called `owner:read`.

Can you see where we're going with this? If we are the *owner* of a `DragonTreasure`,
we will add this group, and then the field will get included. However, this is
tricky. As we talked about in the last video, normalization groups start *static*:
they live up here in our config. The context builder allows us to make these groups
dynamic *per request*. So, if we're an admin user, we can add an extra `admin:read`
group, which will be used when serializing *every* object for this entire request.

But in this situation, we need to make the group dynamic per *object*. Imagine
if we're returning 10 `DragonTreasure`s. The user may only own *one* of them. So
only that *one* `DragonTreasure` should have this extra group.

## The Job of Normalizers

To handle *this* level of control, we need a custom normalizer. Normalizers
are core to Symfony's serializer and they're responsible for turning a piece of
data - like an `ApiResource` object or a `DateTime` object that lives on a property -
into a scalar value or an array. By creating a custom normalizer, you can do pretty
much *any* weird thing you want to with your data.

Actually, find your terminal and run:

```terminal
php  bin/console debug:container --tag=serializer.normalizer
```

I love this: this shows us *every* single normalizer in our app! You can see stuff
tat's responsible for normalizing UUIDs. This is responsible for normalizing any
of our `ApiResource` objects to `JSON-LD`, here's one for a `DateTime`.... there's
a *ton* of interesting stuff.

Our goal is to create our *own* normalizer, decorate an existing *core* normalizer,
but then add the dynamic group before that core normalizer is called.

## Creating the Normalizer Class

So let's get to work! Over in `src/` - it doesn't really matter how we organize
things - I'm going to create a new directory called `Normalizer`. Let me collapse
a few things so it's easier to look at. Inside that, add a new class called, how
about, `AddOwnerGroupsNormalizer`. All normalizers must implement
`NormalizerInterface`... then go to Code -> Generate or Command + N on a Mac and
select "Implement Methods" to add the two we need.

Here's how this works: as soon as we implement `NormalizerInterface`, anytime *any*
piece of data is being normalized, it will call our `supportsNormalization()` method.
there, we can decide whether or not we know how to normalize that thing. If we return
`true`, the serializer will then call `normalize()`,  pass us that data, and then
we return the normalized version.

And actually, to avoid some deprecation errors, let me pop open the parent class.
The return type is this crazy array thingy. Copy that... and add it as the retur
type. You don't *have* to do this - everything would work without it - but you'd
get a deprecation warning in your tests.

Down for `supportsNormalization()`, in the Symfony 7, there will be an `array $context`
argument... and the method will return a `bool`.

## Which Service do We Decorate?

Before we fill this in or set up decoration, we need to think about *which* core
service we're going to decorate. Here's my idea: if we replace the main core
`normalizer` service with *this* class, we could add the group then call the decorated
normalizer... so that everything works like it usually does, except that it uses
the extra group.

Back at the terminal, run

```terminal
bin/console debug:container normalizer
```

We get back a *bunch* of results. That makes sense: there's a *main* `normalizer`,
but then the `normalizer` itself has lots of *other* normalizers inside of it to
handle different types of data. So... where is the top level normalizer? It's actually
not even in this list: it called `serializer`. Though, as we'll see next, even
*that* isn't quite right.
