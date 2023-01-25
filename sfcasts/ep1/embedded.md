# Embedded Relations

So when two resources are related in our API, they show up as an IRI string, or
collection of strings. But you might wonder:

> Hey, could you instead actually include the *data* right here for `DragonTreasure`
> instead of the IRI so I don't need to make a second, third, fourth or fifth request
> to get that data?

Absolutely that can be done! And, again, you can also do something really cool with
Vulcain... but let's learn how to embed data.

## Embedding Vs IRI via Normalization Groups

When the user object is being serialized, it uses the normalization group to
determine which fields to include. In this case, we have one group called
`user:read`. That's why `email`, `username` and `dragonTreasures` are all returned.

To turn the `dragonTreasures` property into *embedded* data, we need to go into
`DragonTreasure` and add this *same* `user:read` group to whatever fields should
be embedded. Watch: above `name`, add `user:read`. Then... go down and also add
this for `value`.

Yup, as soon as we have even *one* property inside of `DragonTreasure` that's
in the `user:read` normalization group, the *way* the `dragonTreasures` field
looks will totally change.

Watch: when we execute that... awesome! Instead of an array of IRI strings, it's
an array of *objects*, with `name` and `value`... and of course the normal `@id`
and `@type` fields.

So: when you have a relation field, it will either be represented as an IRI string
*or* an object... and this depends entirely on your normalization groups.

## Embedding the Other Direction

Let's try this same thing in the other direction. We have a `treasure` whose id
is 2. Head up to the GET a single treasure endpoint... try it... and enter 2
for the id.

No surprise,  we see `owner` as an IRI string. Could we turn that into an embedded
object instead? Of course! We know that `DragonTreasure` uses the `treasure:read`
normalization group. So, go into `User` and add that to the `username` property:
`treasure:read`.

With *just* that change... when we try it... yes! The `owner` field just got
transformed into an object with a `username` field.

## Embedded for One Endpoint, IRI for Another

Ok, let's also fetch a collection of `treasures`: just request all of them.
Again, no surprise. Thanks to the change we just made, *every* single treasure's
`owner` property is now an object.

That gives me a wild, hare-brained idea. What if having all the `owner` information
when I fetch a *single* `DragonTreasure` is cool... but maybe it feels like overkill
to have that returned from the collection endpoint. Could we embed the `owner`
when we're fetching a *single* `treasure`... but then use the IRI string when
fetching a collection?

The answer is... no! I'm kidding - of course! We can do whatever crazy things
we want! Of course, the more weird things you add like this to your API, the
trickier lie gets... so choose your adventures wisely!

Doing this is a two-step process. First in `DragonTreasure`, find the `Get`
operation. This is the operation for fetching a *single* treasure. One of the
options that you can pass *into* an operation is the `normalizationContext`...
which will override the default. Set `normalizationContext`, then `groups` set
to the normal `treasure:read`. Then add a *second* group that's specific to this
operation: `treasure:item:get`.

You can call this whatever you want... but I like this convention: resource name
followed by `item` or `collection` then the HTTP method, like `get` or `post`.

And yes, I *did* just forget the `groups` key: I'll fix that in a minute.

Anyways, assuming I coded this correctly, this means that when this operation is
used, the serializer will include all fields that in *both* of these groups.

*Now* we can leverage that. Copy the new group name. Then, over in `User`, above
the `username` field, instead of `treasure:red`, paste that new group.

Let's check it out! Try the GET collection endpoint again. Yes! We're back to `owner`
being an IRI string. And if we try the GET *one* endpoint.. oh, the owner is...
also an IRI here too? That's my mistake. Back on `normalization_context` I forgot
to say `groups`. I was basically setting two meaningless options into
`normalization_context`.

Let's try that again. This time... got it!

When you get fancy like this, it *does* get a but harder to keep track of what
serialization `groups` are being used and when. You can use the Profiler to help
with that. For example, this is our most recent request for the single treasure.

If we open the profiler for that request... and go down to the Serializer section,
you can see the data that's being serialized... but more importantly the normalization
context... including `groups` set to the two we expect.

This is also cool because you can see *other* context options that are set by
API Platform... to control certain internal behavior.

Next: let's get crazy with our relationships by using a `DragonTreasure` endpoint
to change the `username` field of that treasure's owner. Woh.
