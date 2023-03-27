# Normalizer Decoration & "Normalizer Aware"

Our mission is clear: set up our normalizer to decorate Symfony's *core* normalizer
service so that we can add the `owner:read` group when necessary and *then* call
the decorated normalizer.

## Setting up for Decoration

And we know decoration! Add `public function __construct()` with
`private NormalizerInterface $normalizer`.

Below in `normalize()`, add a `dump()` then return `$this->normalizer->normalize()`
passing `$object` `$format`, and `$context`. For `supportsNormalization()`, do the
same thing: call `supportsNormalization()` on the decorated class and pass the args.

To complete decoration, head to the top of the class. I'll remove a few
old `use` statements...  then say `#[AsDecorator]` passing `serializer`, which I
mentioned  is the service id for the top-level, main normalizer.

Ok! We haven't made any changes yet... so we should still see the one failing
test. Try it:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Woh! An explosion! Wow.

> `ValidationExceptionListener::__construct()` Argument #1 (`$serializer`) must be
> of type `SerializerInterface`, `AddOwnerGroupsNormalizer` given.

Okay? When we add `#[AsDecorator('serializer')]`, it means that our service
*replaces* the service known as `serializer`. So, everyone that's depending on
the `serializer` service will now be passed *us*... and then the original
`serializer` is passed to *our* constructor.

So, what's the problem? Decoration has worked several times before. The problem is
that the `serializer` service in Symfony is... kind of big. It implements
`NormalizerInterface`, but also `DenormalizerInterface`, `EncoderInterface`,
`DecoderInterface` and `SerializerInterface`! But our object only implements *one*
of these . And so, when our class is passed to something that expects an object
with one of those *other* 4 interfaces, it explodes.

If we truly wanted to decorate the `serializer` service, we would need to implement
all *five* of those interfaces... which is just a ugly and too much. And that's
fine!

## Decorating a Lower-Level Normalizer

Instead of decorating the *top* level `normalizer`, let's decorate one *specific*
normalizer: the one that's responsible for normalizing `ApiResource` objects into
`JSON-LD`. This is another spot where you can rely on the documentation to give you
the exact service ID you need. It's `api_platform.jsonld.normalizer.item`.

Try the test again: `testOwnerCanSeeIsPublishedField`

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Yes! We see our dump! And... a 400 error? Let me pop open the response so we can
see it. Strange:

> The injected serializer must be an instance of `NormalizerInterface`.

And it's coming from deep inside of API Platform's serializer code. So...
decorating normalizers is *not* a very friendly process. It's well-documented, but
weird. When you decorate this specific normalizer, you also need to implement
`SerializerAwareInterface`. And that's going to require you to have a `setSerializer()`
method. Oh, let me import that `use` statement: I don't know why that didn't come
automatically. There we go.

Inside, say, if `$this->normalizer` is an `instanceof SerializerAwareInterface`,
then call `$this->normalizer->setSerializer($serializer)`.

I don't even want to get into the details of this: it just happens that the normalizer
we're decorating implements another interface... so we need to *also* implement
it.

Let's try this again.

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Finally, we have the dump *and* it's failing the assertion we expect... since we
haven't added the group yet. Let's do that!

## Adding the Dynamic Group

Remember the goal: if we own this `DragonTreasure`, we want to add the `owner:read`
group. On the constructor, autowire the `Security` service as a property... then
down here, if `$object` is an `instanceof DragonTreasure` - because this method
will be called for *all* of our API resource classes - *and* `$this->security->getUser()`
equals `$object->getOwner()`, then call `$context['groups'][]` to add
`owner:read`.

Phew! Try that test one more time:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

We got it! We can now return different fields on an object-by-object basis.

## Also Decorating the Denormalizer

If you want to *also* add `owner:write` during *denormalization*, you would need
to implement a second interface. I'm not going to do the whole thing... but you
would implement `DenormalizerInterface`, add the two methods needed, call the
decorated service... and change the argument to be a union type of
`NormalizerInterface` *and* `DenormalizerInterface`.

Finally, the service that you're decorating for denormalization is different: it's
`api_platform.serializer.normalizer.item`. However, if you want to decorate
*both* the normalizer and denormalizer in the same class, you'd need to remove
`#[AsDecorator]` and move the decoration config to `services.yaml`... because a
single  service can't decorate two things at once. API Platform covers that in their
docs.

Ok, I'm going to undo all of that... and just stick with adding `owner:read`.
Next: now that we have a custom normalizer, we can easily do wacky things like
adding a *totally* custom field to our API that doesn't exist in our class.
