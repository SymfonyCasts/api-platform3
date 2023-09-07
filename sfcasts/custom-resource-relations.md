# Relating Custom ApiResources

Inside `DailyQuest`, add a new property: `public array $treasures`. This will
hold an array of *dragon treasures* that you can *win* if you complete this quest:
treasures like a fancy magician's hat... a talking frog... the world's *second*
largest slinky... or *all four* corner pieces of brownie! Mmmmmm...

## Adding an array Relations Property

In PHP land, this is just like any other property. Over in our provider,
populate it: say `$quest->treasures = `... and then we'll set that to something.
Instead of a boring empty array, we need some `DragonTreasure` objects. Up at
the top, add `public function __construct()` to autowire a
`private DragonTreasureRepository $treasureRepository`. Below, grab some treasure:
`$treasures = $this->treasureRepository->findBy()` passing an empty array for the
criteria - so it'll return everything - *no* `orderBy`, and a limit of `10`. Yea,
we're just finding the first 1 treasures in the database. I'll paste in some boring
code that will grab a random *set* of these `DragonTreasure` objects. Put *that*
onto the `treasures` property.

Cool! And, even though we don't care right now, to make sure our test keeps passing,
at the top here, say `DragonTreasureFactory::createMany(5)`... because if there are
*zero* treasures, weird things will happen in our provider... and the dragons will
revolt.

Ok, does this new property show up in our API? Head to `/api/quest.jsonld` to see..
a familiar error:

> You must call `setIsOwnedByAuthenticatedUser()` before `isOwnedByAuthenticatedUser()`.

We know this: it comes from `DragonTreasure`... all the way at the bottom. Apparently,
the serializer is trying to access this field, but we never set it.. which makes
sense, because we're the provider and processor for `DragonTreasure` aren't called
when we're using a `DailyQuest` endpoint.

## Why The Relation is Embedded

But... hold on a second. This shouldn't even be a problem. Let me show you what I
mean. To temporarily silence this error, and understand what's going on, find that
property... there it is... and give it a default value of `false`. Spin over, refresh,
and... *whoa*! It *works*! Here's our daily quest... and *here* are the treasures.
*But*... this is not, *quite* what we expected. Each treasure is an *embedded object*.

Remember: when you have a relationship to an object that is an `ApiResource`, like
`DragonTreasure`, that object should only be *embedded* if the parent class and
child class share serialization groups. Like, if we had `normalizationContext`
with `groups` set to `quest:read` like this... where the `quest:read` group is above
`$treasures`, *and*, in `DragonTreasure`, we had at least one property that *also*
had `quest:read` on it.

But, if you do *not* have this situation - heck, we're not using groups at all -
then the serializer should render each `DragonTreasure` as an IRI string. This
should be an array of strings not embedded objects!

The *problem* is that the serializer looks at this `$treasures` property and doesn't
realize that it holds an array of `DragonTreasure` objects. It knows it's an array,
but before it starts serializing, it doesn't know *what* is inside. And so, instead
of sending them through the system that serializes `ApiResource` objects, it sends
them through the code that serializes *normal* objects... which results in it just
serializing all the properties.

This isn't a problem with entities, because the serializer is smart enough: it reads
the Doctrine relationship metadata to figure out that a property is a *collection*
of some *other* `#[ApiResource]` object. Long story short, this is a simple
to fix... it's just hard to understand at first. Above the property, add some
PHPDoc to help the serializer: `@var DragonTreasure[]`.

Try it now... bam! *Now* we get IRI strings! I won't bother, but we could now
undo the default value because this object won't be serialized...which is what gave
us this error in the first place.

So, other than the embedded object surprise, adding relations to our custom resource
is no biggie! Next: instead of embedding `DragonTreasure` objects directly, let's
see how we can invent a *new* class and new data structure to represent these
treasures.
