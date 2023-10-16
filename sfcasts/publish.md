# Triggering a "Publish"



This is our *notification* test. Over here... in `testPublishTreasure`, let's check
that out. Earlier, we were testing to see if a notification is created in the database
when the status changes from `'isPublished' => false` to `'isPublished' => true`.
We did that previously via a custom state processor. We could *also* do that in the
mapper. When we're using our `DragonTreasureApiToEntityMapper`, we could check to
see if the entity *was* `'isPublished' => false` and is now *changing* to
`'isPublished' => true`, and if it *is*, create a notification right there. *But*
this doesn't feel like the right place to do that. Data mappers should really just
be all about mapping data.

*So* let's try a different solution: Creating a *custom state processor*. Over at you
terminal, say:

```terminal
./bin/console make:state-processor
```

And then we'll call that:

```terminal
DragonTreasureStateProcessor
```

There it is! And now we're going to decorate this just like our normal
`EntityClassDtoStateProcessor`. We'll add a `__construct()` method with `private
EntityClassDtoStateProcessor $innerProcessor`. And down here, we'll just return that
with `return $this->innerProcessor->process()` and pass it the arguments it needs:
`$data`, `$operation`, `$uriVariables`, and `$context`. Ah, and you can see that this
is highlighted in red here. We don't *have to* do this, but this isn't really a
`void` method, so we can remove that.

Now that we have this new processor (it's going to use our original one), we can just
hook this up inside of here. So on `DragonTreasureApi`, instead of using the *core*
processor, we're going to use the `DragonTreasureStateProcessor`.

At this point, we have changed *nothing*, and if we rerun the test, everything still
works except for that last failure. *So* let's add our notification code! Earlier,
the way we figured out if we're changing from `'isPublished' => false` to
`'isPublished => true`, is by using the previous data that's inside of the context.
So right here, let's `dd($context['previous_data']`. Now let's head over and run
*just* that test:

```terminal
--filter=testPublishTreasure
```

Cool! We can see that our previous data is the `DragonTreasureApi` with `isPublished:
false`. This is the *original* one that we had inside of our test when we started.
Let's also dump `$data` so our result is even more interesting.

Okay, the original one has `isPublished: false`, and the *new* one has the JSON on it
and `isPublished: true`. And just like before, that's what we're going to focus on to
send the notification. We wrote some of this code before, so let's go borrow that...
*paste*, and that will add a couple of `use` statements. This isn't *super*
interesting. We just have the `$previousData`, we're showing that it `isPublished`,
and then we're creating a `Notification` down here. The only thing that's *kind of*
interesting is that the `Notification` is related to a `DragonTreasure` `$entity`. So
we're actually querying for the `$entity` using the `repository` and grabbing the
`id` off of the API.

Now we need to inject a couple of things here. The first is `private
EntityManagerInterface $entityManager` so we can save, and then `private
DragonTreasureRepository $repository`. There we go! That makes a little more sense
now. So we're grabbing the `id` off of the `DragonTreasureApi`, *querying* for the
`$entity`, and then we relay that on the `Notification` entity and save everything.
If we try our test now... it *passes*! And check this out! All of our
`DragonTreasure` tests pass. Everything is back where it should be. *Amazing*!

Next: Let's make it possible to *write* the `$dragonTreasures` property on *user*.
This involves a trick that's going to help us better understand how API Platform
loads data.
